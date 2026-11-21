<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** F-04: daftar kasbon dan pelunasannya (hanya Pemilik). */
class KasbonController extends Controller
{
    public function index(): View
    {
        $daftar = Transaksi::where('metode_bayar', 'kasbon')
            ->orderBy('status_bayar')      // 'belum_lunas' tampil lebih dulu
            ->orderByDesc('waktu')
            ->get();

        $piutang = $daftar->where('status_bayar', 'belum_lunas')->sum('total');

        return view('kasbon.index', compact('daftar', 'piutang'));
    }

    public function lunas(int $id): RedirectResponse
    {
        $transaksi = Transaksi::where('metode_bayar', 'kasbon')->find($id);

        if (! $transaksi) {
            return redirect()->route('kasbon.index')->with('galat', 'Kasbon tidak ditemukan.');
        }

        if ($transaksi->status_bayar === 'lunas') {
            return redirect()->route('kasbon.index')->with('galat', 'Kasbon ini sudah lunas.');
        }

        $transaksi->update(['status_bayar' => 'lunas', 'bayar' => $transaksi->total]);

        return redirect()->route('kasbon.index')
            ->with('sukses', "Kasbon {$transaksi->nomor} ({$transaksi->nama_pelanggan}) dinyatakan lunas.");
    }
}
