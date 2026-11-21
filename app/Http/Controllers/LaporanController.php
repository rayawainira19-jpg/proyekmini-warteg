<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * F-05 / US-10: rekap penjualan berdasarkan tanggal (hanya Pemilik).
 *  - Pemasukan = transaksi LUNAS pada rentang tanggal; kasbon belum lunas ditampilkan sebagai piutang.
 *  - Tanggal akhir tidak boleh mendahului tanggal mulai; tanggal masa depan ditolak.
 *  - Tanpa transaksi: rekap Rp0 dengan catatan, bukan layar kosong.
 */
class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $hariIni = Carbon::today();
        $dari = $hariIni->copy();
        $sampai = $hariIni->copy();
        $galat = null;

        $inDari = trim((string) $request->query('dari', ''));
        $inSampai = trim((string) $request->query('sampai', ''));
        if ($inDari !== '' && $inSampai === '') {
            $inSampai = $inDari;
        }

        if ($inDari !== '') {
            if (! Carbon::hasFormat($inDari, 'Y-m-d') || ! Carbon::hasFormat($inSampai, 'Y-m-d')) {
                $galat = 'Format tanggal tidak valid.';
            } else {
                $d = Carbon::createFromFormat('Y-m-d', $inDari)->startOfDay();
                $s = Carbon::createFromFormat('Y-m-d', $inSampai)->startOfDay();

                if ($s->lt($d)) {
                    $galat = 'Tanggal akhir tidak boleh mendahului tanggal mulai.';
                } elseif ($d->gt($hariIni) || $s->gt($hariIni)) {
                    $galat = 'Rekap tidak dapat ditampilkan untuk tanggal di masa depan.';
                } else {
                    $dari = $d;
                    $sampai = $s;
                }
            }
        }

        $transaksi = Transaksi::with('detailTransaksi')
            ->whereDate('waktu', '>=', $dari->toDateString())
            ->whereDate('waktu', '<=', $sampai->toDateString())
            ->orderBy('waktu')
            ->get();

        return view('laporan.index', [
            'dari'       => $dari,
            'sampai'     => $sampai,
            'transaksi'  => $transaksi,
            'pemasukan'  => $transaksi->where('status_bayar', 'lunas')->sum('total'),
            'piutang'    => $transaksi->where('status_bayar', 'belum_lunas')->sum('total'),
            'jumlahItem' => $transaksi->sum(fn ($t) => $t->detailTransaksi->sum('qty')),
            'galat'      => $galat,
        ]);
    }
}
