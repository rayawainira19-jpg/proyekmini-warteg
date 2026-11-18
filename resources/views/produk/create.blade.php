@extends('layouts.app')
@section('title', 'Tambah Produk')

@section('content')
    <h1>Tambah Produk</h1>
    <p class="sub">Masukkan informasi produk baru ke dalam katalog.</p>

    <form method="POST" action="{{ route('produk.store') }}">
        @csrf
        @include('produk._form')
        <div class="tombol">
            <button type="submit">Simpan Produk</button>
            <a class="btn putih" href="{{ route('produk.index') }}">Batal</a>
        </div>
    </form>
@endsection
