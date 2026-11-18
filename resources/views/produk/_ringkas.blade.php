<table>
    <thead><tr><th>Kode</th><th>Nama produk</th><th>Kategori</th><th class="num">Harga</th><th>Stok</th><th>Status</th></tr></thead>
    <tbody>
        <tr>
            <td>{{ $produk->kode }}</td>
            <td>{{ $produk->nama }}</td>
            <td>{{ $produk->kategori->nama }}</td>
            <td class="num">Rp{{ number_format($produk->harga, 0, ',', '.') }}</td>
            <td>{{ $produk->stok }}</td>
            <td>{{ $produk->aktif ? 'Aktif' : 'Nonaktif' }}</td>
        </tr>
    </tbody>
</table>
