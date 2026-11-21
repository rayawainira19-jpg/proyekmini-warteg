@extends('layouts.app')
@section('title', 'Kasbon')

@section('content')
    <h1>Catatan Kasbon</h1>
    <p class="sub">Kasbon pelanggan yang tercatat dari transaksi dengan metode kasbon.</p>

    <p class="total">Total piutang kasbon: <strong>Rp{{ number_format($piutang, 0, ',', '.') }}</strong></p>

    @if ($daftar->isEmpty())
        <div class="kotak"><h2>Belum ada kasbon</h2><p>Kasbon muncul di sini saat transaksi dibayar dengan metode kasbon.</p></div>
    @else
        <table>
            <thead><tr><th>Nomor</th><th>Waktu</th><th>Pelanggan</th><th class="num">Total</th><th>Status</th>@if (auth()->user()->adalahPemilik())<th>Aksi</th>@endif</tr></thead>
            <tbody>
                @foreach ($daftar as $t)
                    <tr class="{{ $t->status_bayar === 'lunas' ? 'mati' : '' }}">
                        <td><a href="{{ route('transaksi.show', $t->id) }}">{{ $t->nomor }}</a></td>
                        <td>{{ $t->waktu->format('d-m-Y H:i') }}</td>
                        <td>{{ $t->nama_pelanggan }}</td>
                        <td class="num">Rp{{ number_format($t->total, 0, ',', '.') }}</td>
                        <td>{{ $t->status_bayar === 'lunas' ? 'Lunas' : 'Belum lunas' }}</td>
                        @if (auth()->user()->adalahPemilik())
                            <td class="aksi">
                                @if ($t->status_bayar === 'belum_lunas')
                                    <form method="POST" action="{{ route('kasbon.lunas', $t->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="kecil">Tandai lunas</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
