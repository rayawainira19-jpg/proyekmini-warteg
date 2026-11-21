<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** US-02: menambah dan menghapus produk pada keranjang transaksi. */
class KeranjangController extends Controller
{
    public function tambah(Request $request): RedirectResponse
    {
        $id = trim((string) $request->input('produk_id'));
        $jumlah = trim((string) $request->input('jumlah', '1'));
        if ($jumlah === '') {
            $jumlah = '1';
        }

        if (! ctype_digit($id) || ! ctype_digit($jumlah) || strlen($jumlah) > 6 || (int) $jumlah < 1) {
            return back()->with('galat', 'Jumlah beli tidak valid.');
        }

        $produk = Produk::find((int) $id);
        if (! $produk || ! $produk->aktif) {
            return back()->with('galat', 'Produk tidak tersedia untuk transaksi.');
        }

        $item = Keranjang::firstOrNew(['user_id' => Auth::id(), 'produk_id' => $produk->id]);
        $baru = ($item->exists ? $item->jumlah : 0) + (int) $jumlah;

        if ($produk->stok < 1 || $baru > $produk->stok) {
            return back()->with('galat', 'Stok tidak mencukupi.');
        }

        $totalLain = Keranjang::with('produk')
            ->where('user_id', Auth::id())
            ->where('produk_id', '!=', $produk->id)
            ->get()
            ->sum(fn ($k) => $k->produk->harga * $k->jumlah);

        if ($totalLain + $produk->harga * $baru > TransaksiController::BATAS_TOTAL) {
            return back()->with('galat', 'Total belanja melebihi batas maksimum transaksi (Rp100.000.000).');
        }

        $item->jumlah = $baru;
        $item->save();

        return back()->with('sukses', "{$produk->nama} ditambahkan ke keranjang.");
    }

    public function hapus(int $id): RedirectResponse
    {
        Keranjang::where('user_id', Auth::id())->where('id', $id)->delete();

        return back()->with('sukses', 'Produk dihapus dari keranjang.');
    }

    /** Membatalkan transaksi berjalan: mengosongkan keranjang. */
    public function kosongkan(): RedirectResponse
    {
        Keranjang::where('user_id', Auth::id())->delete();

        return back()->with('sukses', 'Keranjang dikosongkan.');
    }
}
