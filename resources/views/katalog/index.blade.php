@extends('layouts.app')

@section('title', 'Katalog Produk | Kasir Toko')

@section('content')
    <p class="eyebrow">Katalog toko sekolah</p>
    <h1>Daftar produk</h1>
    <p class="intro">Lihat pilihan produk, kategori, dan ketersediaan stok toko dalam satu tempat.</p>

    <section class="panel" aria-labelledby="judul-katalog">
        <div class="panel-header">
            <div>
                <h2 class="panel-title" id="judul-katalog">Semua produk</h2>
                <p class="panel-subtitle">Data katalog dari database</p>
            </div>
            <form class="search" action="{{ route('katalog.index') }}" method="get" role="search">
                <label for="pencarian" class="sr-only" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0">Cari nama produk</label>
                <input id="pencarian" type="search" name="q" value="{{ $pencarian }}" placeholder="Cari nama produk…" maxlength="100">
                <button type="submit">Cari</button>
                @if ($pencarian !== '')
                    <a class="reset" href="{{ route('katalog.index') }}">Reset</a>
                @endif
            </form>
        </div>
        @error('q')
            <p class="error">Pencarian harus berupa teks dengan maksimal 100 karakter.</p>
        @enderror
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th scope="col">Produk</th><th scope="col">Kategori</th><th scope="col">Harga</th><th scope="col">Stok</th></tr>
                </thead>
                <tbody>
                    @forelse ($produk as $item)
                        <tr>
                            <td><span class="product-name">{{ $item->nama }}</span><span class="sku">{{ $item->sku }}</span></td>
                            <td><span class="category">{{ $item->kategori->nama }}</span></td>
                            <td class="price">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td>
                                @if ($item->stok > 0)
                                    <span class="stock">{{ $item->stok }} tersedia</span>
                                @else
                                    <span class="stock empty">Habis</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty-state" colspan="4">
                                <strong>Belum ada produk.</strong>
                                @if ($pencarian !== '')
                                    Tidak ada hasil untuk “{{ $pencarian }}”. Coba kata kunci lain.
                                @else
                                    Produk yang ditambahkan ke database akan muncul di sini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <span>Menampilkan {{ $produk->count() }} produk{{ $pencarian !== '' ? ' untuk pencarian ini' : '' }}</span>
            <span>Harga dalam Rupiah</span>
        </div>
    </section>
    <p class="footer-note">Ketersediaan produk mengikuti data yang tersimpan di database.</p>
@endsection
