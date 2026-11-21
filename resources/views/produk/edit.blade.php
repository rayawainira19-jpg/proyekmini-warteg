@extends('layouts.app')
@section('title', 'Ubah Produk')

@section('content')
    <h1>Ubah Produk</h1>
    <p class="sub">Perbarui informasi produk yang sudah terdaftar.</p>

    <form method="POST" action="{{ route('produk.update', $produk->id) }}">
        @csrf
        @method('PUT')
        @include('produk._form')
        @include('produk._gagal')
        <div class="tombol">
            <button type="submit">{{ session('gagal') ? 'Coba Lagi' : 'Simpan Perubahan' }}</button>
            <a class="btn putih" href="{{ route('produk.index') }}">Batal</a>
        </div>
    </form>
@endsection
