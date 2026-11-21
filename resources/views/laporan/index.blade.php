@extends('layouts.app')
@section('title', 'Laporan Penjualan Harian')

@section('content')
    <h1>Laporan Penjualan Harian</h1>
    <p class="sub">Rekap penjualan berdasarkan tanggal. Pemasukan hanya menghitung transaksi yang sudah lunas.</p>

    <form method="GET" action="{{ route('laporan.index') }}" class="bar rentang">
        <div class="f">
            <label for="dari">Dari tanggal</label>
            <input id="dari" type="date" name="dari" value="{{ $dari->toDateString() }}" max="{{ now()->toDateString() }}">
        </div>
        <div class="f">
            <label for="sampai">Sampai tanggal</label>
            <input id="sampai" type="date" name="sampai" value="{{ $sampai->toDateString() }}" max="{{ now()->toDateString() }}">
        </div>
        <div class="f"><label>&nbsp;</label><button type="submit">Tampilkan</button></div>
    </form>

    @if ($galat)<p class="alert err">{{ $galat }}</p>@endif

    <p class="sub">Periode: {{ $dari->format('d-m-Y') }}{{ $dari->ne($sampai) ? ' s.d. ' . $sampai->format('d-m-Y') : '' }}</p>

    <div class="kartu">
        <div><span>Total pemasukan</span><strong>Rp{{ number_format($pemasukan, 0, ',', '.') }}</strong></div>
        <div><span>Jumlah transaksi</span><strong>{{ $transaksi->count() }}</strong></div>
        <div><span>Item terjual</span><strong>{{ $jumlahItem }}</strong></div>
        <div><span>Kasbon belum lunas</span><strong>Rp{{ number_format($piutang, 0, ',', '.') }}</strong></div>
    </div>

    @if ($transaksi->isEmpty())
        <div class="kotak"><p>Tidak ada transaksi pada tanggal ini.</p></div>
    @else
        <table>
            <thead><tr><th>Nomor</th><th>Waktu</th><th>Metode</th><th>Pelanggan</th><th class="num">Total</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($transaksi as $t)
                    <tr>
                        <td><a href="{{ route('transaksi.show', $t->id) }}">{{ $t->nomor }}</a></td>
                        <td>{{ $t->waktu->format('d-m-Y H:i') }}</td>
                        <td>{{ ucfirst($t->metode_bayar) }}</td>
                        <td>{{ $t->nama_pelanggan ?? '-' }}</td>
                        <td class="num">Rp{{ number_format($t->total, 0, ',', '.') }}</td>
                        <td>{{ $t->status_bayar === 'lunas' ? 'Lunas' : 'Belum lunas' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
