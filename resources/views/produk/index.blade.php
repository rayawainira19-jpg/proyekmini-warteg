@extends('layouts.app')
@section('title', 'Katalog Produk')

@section('content')
    <h1>Katalog Produk</h1>
    <p class="sub">Katalog daftar produk, harga, kategori, dan stok</p>

    <div class="bar">
        <form method="GET" action="{{ route('produk.index') }}" class="cari">
            <label for="q" class="sr">Cari nama, kode, atau kategori</label>
            <input id="q" name="q" value="{{ $q }}" placeholder="Cari nama atau kategori produk...">
            <button type="submit">Cari</button>
        </form>
        <a class="btn" href="{{ route('produk.create') }}">+ Tambah produk</a>
    </div>

    @if ($pesan)<p class="alert warn">{{ $pesan }}</p>@endif

    @if ($produk->isEmpty())
        <div class="kotak">
            @if ($q !== '' && ! $pesan)
                <p>Produk tidak ditemukan. Periksa kembali kata kunci pencarian.</p>
            @else
                <h2>Belum ada data produk</h2>
                <p>Produk yang ditambahkan akan tampil di halaman ini.</p>
                <a class="btn" href="{{ route('produk.create') }}">+ Tambah produk</a>
            @endif
        </div>
    @else
        <table>
            <thead>
                <tr><th>Kode</th><th>Nama produk</th><th>Kategori</th><th class="num">Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach ($produk as $p)
                    <tr class="{{ $p->aktif ? '' : 'mati' }}">
                        <td>{{ $p->kode }}</td>
                        <td>{{ $p->nama }}</td>
                        <td>{{ $p->kategori->nama }}</td>
                        <td class="num">Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
                        <td>@if ($p->stok > 0){{ $p->stok }}@else<span class="habis">Habis</span>@endif</td>
                        <td>{{ $p->aktif ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="aksi">
                            <a href="{{ route('produk.edit', $p->id) }}">Edit</a>
                            <a href="{{ route('produk.nonaktif.konfirmasi', $p->id) }}">{{ $p->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</a>
                            <a class="hapus" href="{{ route('produk.hapus', $p->id) }}">Hapus</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pager">
            @if ($produk->previousPageUrl())<a class="btn" href="{{ $produk->previousPageUrl() }}">&lt;</a>@endif
            <span>Halaman {{ $produk->currentPage() }} dari {{ $produk->lastPage() }}</span>
            @if ($produk->nextPageUrl())<a class="btn" href="{{ $produk->nextPageUrl() }}">&gt;</a>@endif
        </div>
    @endif
@endsection
