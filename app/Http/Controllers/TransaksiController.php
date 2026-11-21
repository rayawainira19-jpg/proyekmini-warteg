<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * F-03 Transaksi Kasir (US-02 sampai US-05) dan F-04 Pencatatan Kasbon.
 *
 *  - Keranjang: qty bilangan bulat > 0 dan <= stok; menu nonaktif tidak dapat dipilih (BR-03).
 *  - Total dihitung otomatis; keranjang kosong = Rp0; batas maksimum Rp100.000.000 (US-03).
 *  - Tunai: bayar >= total; kembalian = bayar - total (BR-04, US-04).
 *  - harga_satuan menyalin harga master saat transaksi (BR-05).
 *  - Kasbon: nama pelanggan wajib, status 'belum_lunas' (F-04).
 *  - Semua langkah penyimpanan berada dalam satu DB transaction.
 */
class TransaksiController extends Controller
{
    public const BATAS_TOTAL = 100_000_000;

    private const METODE = ['tunai', 'qris', 'transfer', 'kasbon'];

    public function create(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $pesan = null;

        $query = Produk::with('kategori')->where('aktif', true)->orderBy('nama');

        if ($q !== '') {
            if (mb_strlen($q) < 2) {
                $pesan = 'Kata kunci minimal 2 karakter.';
            } else {
                $query->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")->orWhere('kode', 'like', "%{$q}%");
                });
            }
        }

        try {
            $produk = $query->get();
        } catch (QueryException $e) {
            $produk = collect();
            $pesan = 'Gagal memuat data, silakan coba lagi.';
        }

        $keranjang = Keranjang::with('produk')->where('user_id', Auth::id())->orderBy('id')->get();
        $total = $keranjang->sum(fn ($k) => $k->produk->harga * $k->jumlah);

        return view('transaksi.create', compact('produk', 'keranjang', 'total', 'q', 'pesan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $metode = (string) $request->input('metode_bayar');
        if (! in_array($metode, self::METODE, true)) {
            throw ValidationException::withMessages(['metode_bayar' => 'Metode pembayaran tidak valid.']);
        }

        $nama = trim((string) $request->input('nama_pelanggan'));
        if ($metode === 'kasbon' && $nama === '') {
            throw ValidationException::withMessages(['nama_pelanggan' => 'Nama pelanggan wajib diisi untuk kasbon.']);
        }
        if (mb_strlen($nama) > 100) {
            throw ValidationException::withMessages(['nama_pelanggan' => 'Nama pelanggan maksimal 100 karakter.']);
        }

        $userId = Auth::id();

        $transaksi = DB::transaction(function () use ($userId, $metode, $nama, $request) {
            $baris = Keranjang::where('user_id', $userId)->lockForUpdate()->get();
            if ($baris->isEmpty()) {
                throw ValidationException::withMessages(['keranjang' => 'Keranjang masih kosong. Tambahkan produk terlebih dahulu.']);
            }

            $produk = Produk::whereIn('id', $baris->pluck('produk_id'))->lockForUpdate()->get()->keyBy('id');

            $total = 0;
            foreach ($baris as $b) {
                $p = $produk->get($b->produk_id);
                if (! $p || ! $p->aktif || $b->jumlah > $p->stok) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Stok tidak mencukupi' . ($p ? " ({$p->nama})" : '') . '.',
                    ]);
                }
                $total += $p->harga * $b->jumlah;
            }

            if ($total > self::BATAS_TOTAL) {
                throw ValidationException::withMessages(['keranjang' => 'Total belanja melebihi batas maksimum transaksi (Rp100.000.000).']);
            }

            $bayar = $total;
            $kembalian = 0;
            $status = 'lunas';

            if ($metode === 'tunai') {
                $uang = trim((string) $request->input('bayar'));
                if (! ctype_digit($uang) || strlen($uang) > 12 || (int) $uang < 1) {
                    throw ValidationException::withMessages(['bayar' => 'Input uang tunai harus berupa angka positif.']);
                }
                if ((int) $uang < $total) {
                    throw ValidationException::withMessages(['bayar' => 'Uang tunai kurang dari total belanja.']);
                }
                $bayar = (int) $uang;
                $kembalian = $bayar - $total;
            } elseif ($metode === 'kasbon') {
                $bayar = 0;
                $status = 'belum_lunas';
            }

            $transaksi = Transaksi::create([
                'nomor'          => 'TRX-' . now()->format('Ymd-His') . '-' . random_int(100, 999),
                'waktu'          => now(),
                'nama_pelanggan' => $nama !== '' ? $nama : null,
                'metode_bayar'   => $metode,
                'status_bayar'   => $status,
                'total'          => $total,
                'bayar'          => $bayar,
                'kembalian'      => $kembalian,
            ]);

            foreach ($baris as $b) {
                $p = $produk->get($b->produk_id);
                $transaksi->detailTransaksi()->create([
                    'produk_id'    => $p->id,
                    'qty'          => $b->jumlah,
                    'harga_satuan' => $p->harga, // BR-05
                    'subtotal'     => $p->harga * $b->jumlah,
                ]);
                $p->decrement('stok', $b->jumlah);
            }

            Keranjang::where('user_id', $userId)->delete();

            return $transaksi;
        });

        return redirect()->route('transaksi.show', $transaksi->id)
            ->with('sukses', 'Pembayaran berhasil. Transaksi telah disimpan.');
    }

    /** US-05: bukti transaksi, hanya ada setelah pembayaran berhasil. */
    public function show(int $id): View|RedirectResponse
    {
        $transaksi = Transaksi::with('detailTransaksi.produk')->find($id);

        if (! $transaksi) {
            return redirect()->route('transaksi.create')->with('galat', 'Transaksi tidak ditemukan.');
        }

        return view('transaksi.show', compact('transaksi'));
    }
}
