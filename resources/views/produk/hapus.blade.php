@extends('layouts.app')
@section('title', 'Hapus Produk')

@section('content')
    @if ($punyaRiwayat)
        <h1>Produk Memiliki Riwayat Transaksi</h1>
        <p class="sub">Produk yang memiliki riwayat transaksi tidak dihapus.</p>
        @include('produk._ringkas')

        <div class="kotak kiri">
            <h2>Produk tidak dapat dihapus</h2>
            <p>Produk ini sudah memiliki riwayat transaksi. Untuk menjaga riwayat, ubah status produk menjadi nonaktif.</p>
            <div class="tombol">
                <a class="btn" href="{{ route('produk.nonaktif.konfirmasi', $produk->id) }}">Nonaktifkan</a>
                <a class="btn putih" href="{{ route('produk.index') }}">Mengerti</a>
            </div>
        </div>
    @else
        <h1>Hapus Produk</h1>
        <p class="sub">Produk tanpa riwayat transaksi boleh dihapus.</p>
        @include('produk._ringkas')

        <div class="kotak kiri">
            <h2>Hapus produk?</h2>
            <p>Produk tanpa riwayat transaksi boleh dihapus. Apakah Anda ingin melanjutkan?</p>
            <form method="POST" action="{{ route('produk.destroy', $produk->id) }}" class="tombol">
                @csrf
                @method('DELETE')
                <a class="btn putih" href="{{ route('produk.index') }}">Batal</a>
                <button type="submit" class="bahaya">Ya, Hapus</button>
            </form>
        </div>
    @endif
@endsection
