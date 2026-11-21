@extends('layouts.app')
@section('title', 'Transaksi Kasir')

@section('content')
    <h1>Transaksi Kasir</h1>
    <p class="sub">Pilih produk, periksa keranjang, lalu selesaikan pembayaran.</p>

    <div class="pos">
        {{-- Kiri: daftar produk (US-01, US-02) --}}
        <section>
            <h2>Produk</h2>
            <form method="GET" action="{{ route('transaksi.create') }}" class="cari">
                <label for="q" class="sr">Cari nama atau kode produk</label>
                <input id="q" name="q" value="{{ $q }}" placeholder="Cari nama atau kode produk...">
                <button type="submit" id="tombol-cari">Cari</button>
            </form>
            @if ($pesan)<p class="alert warn">{{ $pesan }}</p>@endif

            @if ($produk->isEmpty())
                <div class="kotak"><p>{{ $q !== '' && ! $pesan ? 'Produk tidak ditemukan. Periksa kembali kata kunci pencarian.' : 'Belum ada produk aktif.' }}</p></div>
            @else
                <table>
                    <thead><tr><th>Produk</th><th class="num">Harga</th><th>Stok</th><th>Tambah</th></tr></thead>
                    <tbody>
                        @foreach ($produk as $p)
                            <tr>
                                <td>{{ $p->nama }}<br><small class="sub">{{ $p->kode }} · {{ $p->kategori->nama }}</small></td>
                                <td class="num">Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
                                <td>@if ($p->stok > 0){{ $p->stok }}@else<span class="habis">Habis</span>@endif</td>
                                <td>
                                    <form method="POST" action="{{ route('keranjang.tambah') }}" class="tambah">
                                        @csrf
                                        <input type="hidden" name="produk_id" value="{{ $p->id }}">
                                        <input type="number" name="jumlah" value="1" min="1" class="qty" aria-label="Jumlah {{ $p->nama }}">
                                        <button type="submit">Tambah</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        {{-- Kanan: keranjang dan pembayaran (US-02, US-03, US-04) --}}
        <section>
            <h2>Keranjang</h2>
            @error('keranjang')<p class="alert err">{{ $message }}</p>@enderror

            @if ($keranjang->isEmpty())
                <div class="kotak"><p>Keranjang masih kosong.</p></div>
            @else
                <table>
                    <thead><tr><th>Produk</th><th>Qty</th><th class="num">Subtotal</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($keranjang as $k)
                            <tr>
                                <td>{{ $k->produk->nama }}<br><small class="sub">Rp{{ number_format($k->produk->harga, 0, ',', '.') }}</small></td>
                                <td>{{ $k->jumlah }}</td>
                                <td class="num">Rp{{ number_format($k->produk->harga * $k->jumlah, 0, ',', '.') }}</td>
                                <td class="aksi">
                                    <form method="POST" action="{{ route('keranjang.hapus', $k->id) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kecil">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <form method="POST" action="{{ route('keranjang.kosongkan') }}" class="tombol">
                    @csrf @method('DELETE')
                    <button type="submit" class="putih-btn">Batalkan transaksi</button>
                </form>
            @endif

            <p class="total">Total belanja: <strong id="total" data-total="{{ $total }}">Rp{{ number_format($total, 0, ',', '.') }}</strong></p>

            <form method="POST" action="{{ route('transaksi.store') }}" class="bayar">
                @csrf
                <div class="f">
                    <label for="metode_bayar">Metode pembayaran</label>
                    <select id="metode_bayar" name="metode_bayar">
                        @foreach (['tunai' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer', 'kasbon' => 'Kasbon'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('metode_bayar', 'tunai') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('metode_bayar')<small class="e">{{ $message }}</small>@enderror
                </div>

                <div class="f" id="kotak-bayar">
                    <label for="bayar">Uang tunai diterima (Rp)</label>
                    <input id="bayar" name="bayar" inputmode="numeric" value="{{ old('bayar') }}" placeholder="Contoh: 50000" @class(['invalid' => $errors->has('bayar')])>
                    @error('bayar')<small class="e">{{ $message }}</small>@enderror
                    <small class="sub">Kembalian: <strong id="kembalian">Rp0</strong></small>
                </div>

                <div class="f" id="kotak-nama">
                    <label for="nama_pelanggan">Nama pelanggan (wajib untuk kasbon)</label>
                    <input id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" @class(['invalid' => $errors->has('nama_pelanggan')])>
                    @error('nama_pelanggan')<small class="e">{{ $message }}</small>@enderror
                </div>

                <div class="tombol">
                    <button type="submit" @disabled($keranjang->isEmpty())>Selesaikan Pembayaran</button>
                </div>
            </form>
        </section>
    </div>

    <script>
        (function () {
            var rp = function (n) { return 'Rp' + String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
            var total = parseInt(document.getElementById('total').dataset.total, 10) || 0;
            var metode = document.getElementById('metode_bayar');
            var bayar = document.getElementById('bayar');
            var kembalian = document.getElementById('kembalian');
            var kotakBayar = document.getElementById('kotak-bayar');
            var kotakNama = document.getElementById('kotak-nama');
            var cari = document.getElementById('q');
            var tombolCari = document.getElementById('tombol-cari');

            function hitung() {
                var b = parseInt(bayar.value, 10) || 0;
                kembalian.textContent = rp(Math.max(b - total, 0));
                kotakBayar.hidden = metode.value !== 'tunai';
                kotakNama.hidden = metode.value !== 'kasbon';
            }
            // US-01: tombol cari nonaktif jika kata kunci kosong atau kurang dari 2 karakter.
            function cek() { tombolCari.disabled = cari.value.trim().length < 2; }

            metode.addEventListener('change', hitung);
            bayar.addEventListener('input', hitung);
            cari.addEventListener('input', cek);
            hitung();
            cek();
        })();
    </script>
@endsection
