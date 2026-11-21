<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdukRequest;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdukController extends Controller
{
    private const TIDAK_ADA = 'Produk tidak ditemukan atau sudah dihapus.';
    private const DI_KERANJANG = 'Produk sedang berada di keranjang transaksi berjalan. Selesaikan atau batalkan transaksi tersebut terlebih dahulu.';

    /** Halaman 1: katalog produk + pencarian + empty state */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $pesan = null;

        $query = Produk::with('kategori')->orderBy('kode');

        if ($q !== '') {
            if (mb_strlen($q) < 2) {
                $pesan = 'Kata kunci minimal 2 karakter.'; // US-01
            } else {
                $query->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")
                      ->orWhere('kode', 'like', "%{$q}%")
                      ->orWhereHas('kategori', fn ($k) => $k->where('nama', 'like', "%{$q}%"));
                });
            }
        }

        try {
            $produk = $query->paginate(10)->withQueryString();
        } catch (QueryException $e) {
            return view('produk.index', [
                'produk' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'q'      => $q,
                'pesan'  => 'Gagal memuat data, silakan coba lagi.',
            ]);
        }

        return view('produk.index', compact('produk', 'q', 'pesan'));
    }

    /** Halaman 2: tambah produk */
    public function create(): View
    {
        return view('produk.create', [
            'produk'   => new Produk(['aktif' => true]),
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(ProdukRequest $request): RedirectResponse
    {
        try {
            $p = Produk::create($request->validated());
        } catch (QueryException $e) {
            return back()->withInput()->with('gagal', 'Gagal menyimpan produk. Periksa koneksi atau coba lagi.');
        }

        return redirect()->route('produk.berhasil', $p->id)
            ->with('sukses', "Produk {$p->nama} berhasil disimpan.");
    }

    /** Umpan balik setelah produk berhasil disimpan (wireframe 10) */
    public function berhasil(int $id): View|RedirectResponse
    {
        $produk = Produk::with('kategori')->find($id);

        return $produk ? view('produk.berhasil', compact('produk')) : $this->tidakAda();
    }

    /** Halaman 3: ubah produk */
    public function edit(int $id): View|RedirectResponse
    {
        $produk = Produk::find($id);
        if (! $produk) {
            return $this->tidakAda();
        }

        return view('produk.edit', [
            'produk'   => $produk,
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function update(ProdukRequest $request, int $id): RedirectResponse
    {
        $produk = Produk::find($id);
        if (! $produk) {
            return $this->tidakAda();
        }

        try {
            $produk->update($request->validated());
        } catch (QueryException $e) {
            return back()->withInput()->with('gagal', 'Gagal menyimpan produk. Periksa koneksi atau coba lagi.');
        }

        return redirect()->route('produk.berhasil', $produk->id)
            ->with('sukses', "Perubahan produk {$produk->nama} berhasil disimpan.");
    }

    /** Halaman 4: konfirmasi nonaktifkan / aktifkan kembali */
    public function konfirmasiNonaktif(int $id): View|RedirectResponse
    {
        $produk = Produk::with('kategori')->find($id);
        if (! $produk) {
            return $this->tidakAda();
        }
        if ($produk->aktif && $this->dalamKeranjang($id)) {
            return redirect()->route('produk.index')->with('galat', self::DI_KERANJANG);
        }

        return view('produk.nonaktif', compact('produk'));
    }

    public function nonaktif(int $id): RedirectResponse
    {
        $produk = Produk::find($id);
        if (! $produk) {
            return $this->tidakAda();
        }

        if ($produk->aktif && $this->dalamKeranjang($id)) {
            return redirect()->route('produk.index')->with('galat', self::DI_KERANJANG);
        }

        $produk->update(['aktif' => ! $produk->aktif]);

        return redirect()->route('produk.index')->with(
            'sukses',
            $produk->aktif ? "Produk {$produk->nama} diaktifkan kembali." : "Produk {$produk->nama} dinonaktifkan."
        );
    }

    /** Halaman 5: konfirmasi hapus, atau penolakan bila punya riwayat transaksi */
    public function konfirmasiHapus(int $id): View|RedirectResponse
    {
        $produk = Produk::with('kategori')->find($id);
        if (! $produk) {
            return $this->tidakAda();
        }

        if ($this->dalamKeranjang($id)) {
            return redirect()->route('produk.index')->with('galat', self::DI_KERANJANG);
        }

        $punyaRiwayat = $produk->detailTransaksi()->exists();

        return view('produk.hapus', compact('produk', 'punyaRiwayat'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $produk = Produk::find($id);
        if (! $produk) {
            return $this->tidakAda();
        }

        if ($this->dalamKeranjang($id)) {
            return redirect()->route('produk.index')->with('galat', self::DI_KERANJANG);
        }

        if ($produk->detailTransaksi()->exists()) {
            return redirect()->route('produk.hapus', $id); // tampilkan halaman penolakan
        }

        $produk->delete();

        return redirect()->route('produk.index')->with('sukses', "Produk {$produk->nama} dihapus.");
    }

    /** US-08: produk pada keranjang transaksi berjalan tidak boleh dinonaktifkan/dihapus. */
    private function dalamKeranjang(int $id): bool
    {
        return Keranjang::where('produk_id', $id)->exists();
    }

    private function tidakAda(): RedirectResponse
    {
        return redirect()->route('produk.index')->with('galat', self::TIDAK_ADA);
    }
}
