@extends('layouts.app')
@section('title', $produk->aktif ? 'Nonaktifkan Produk' : 'Aktifkan Produk')

@section('content')
    <h1>{{ $produk->aktif ? 'Nonaktifkan Produk' : 'Aktifkan Produk' }}</h1>
    <p class="sub">Produk dinonaktifkan agar tidak tersedia untuk penjualan.</p>

    @include('produk._ringkas')

    <div class="kotak kiri">
        @if ($produk->aktif)
            <h2>Nonaktifkan produk?</h2>
            <p>Produk tidak akan tersedia untuk dipilih pada penjualan. Data produk tetap tersimpan dan dapat diaktifkan kembali.</p>
        @else
            <h2>Aktifkan kembali produk?</h2>
            <p>Produk akan tersedia lagi untuk dipilih pada penjualan.</p>
        @endif
        <form method="POST" action="{{ route('produk.nonaktif', $produk->id) }}" class="tombol">
            @csrf
            @method('PATCH')
            <a class="btn putih" href="{{ route('produk.index') }}">Batal</a>
            <button type="submit">{{ $produk->aktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}</button>
        </form>
    </div>
@endsection
