@extends('layouts.app')
@section('title', 'Bukti Transaksi')

@section('content')
    <h1>Bukti Transaksi</h1>
    <p class="sub">{{ $transaksi->nomor }} · {{ $transaksi->waktu->format('d-m-Y H:i') }}</p>

    <table>
        <thead><tr><th>Produk</th><th>Qty</th><th class="num">Harga</th><th class="num">Subtotal</th></tr></thead>
        <tbody>
            @foreach ($transaksi->detailTransaksi as $d)
                <tr>
                    <td>{{ $d->produk->nama }}</td>
                    <td>{{ $d->qty }}</td>
                    <td class="num">Rp{{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                    <td class="num">Rp{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="kotak kiri ringkas">
        <p>Total belanja: <strong>Rp{{ number_format($transaksi->total, 0, ',', '.') }}</strong></p>
        <p>Metode pembayaran: <strong>{{ ucfirst($transaksi->metode_bayar) }}</strong></p>
        @if ($transaksi->metode_bayar === 'tunai')
            <p>Uang tunai: Rp{{ number_format($transaksi->bayar, 0, ',', '.') }}</p>
            <p>Kembalian: <strong>Rp{{ number_format($transaksi->kembalian, 0, ',', '.') }}</strong></p>
        @endif
        @if ($transaksi->nama_pelanggan)<p>Pelanggan: {{ $transaksi->nama_pelanggan }}</p>@endif
        <p>Status: <strong>{{ $transaksi->status_bayar === 'lunas' ? 'Lunas' : 'Belum lunas' }}</strong></p>
    </div>

    <div class="tombol">
        <button type="button" onclick="window.print()">Cetak Ulang</button>
        <a class="btn putih" href="{{ route('transaksi.create') }}">Transaksi baru</a>
    </div>
@endsection
