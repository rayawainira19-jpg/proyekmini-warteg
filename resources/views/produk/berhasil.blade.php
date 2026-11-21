@extends('layouts.app')
@section('title', 'Produk Berhasil Disimpan')

@section('content')
    <h1>Produk Berhasil Disimpan</h1>
    <p class="sub">Umpan balik setelah data produk berhasil tersimpan.</p>

    <h2>Ringkasan Produk</h2>
    @include('produk._ringkas')

    <div class="tombol">
        <a class="btn" href="{{ route('produk.index') }}">Kembali ke katalog</a>
        <a class="btn putih" href="{{ route('produk.create') }}">+ Tambah Produk</a>
    </div>
@endsection
