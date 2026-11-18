<div class="grid">
    <div class="f">
        <label for="kode">Kode Produk</label>
        <input id="kode" name="kode" value="{{ old('kode', $produk->kode) }}" placeholder="Contoh: KOP-001">
        @error('kode')<small class="e">{{ $message }}</small>@enderror
    </div>
    <div class="f">
        <label for="nama">Nama Produk</label>
        <input id="nama" name="nama" value="{{ old('nama', $produk->nama) }}" placeholder="Masukkan nama produk">
        @error('nama')<small class="e">{{ $message }}</small>@enderror
    </div>
    <div class="f">
        <label for="kategori_id">Kategori</label>
        <select id="kategori_id" name="kategori_id">
            <option value="">Pilih kategori</option>
            @foreach ($kategori as $k)
                <option value="{{ $k->id }}" @selected((string) old('kategori_id', $produk->kategori_id) === (string) $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        @error('kategori_id')<small class="e">{{ $message }}</small>@enderror
    </div>
    <div class="f">
        <label for="harga">Harga (Rp)</label>
        <input id="harga" name="harga" inputmode="numeric" value="{{ old('harga', $produk->harga) }}" placeholder="Contoh: 15000">
        @error('harga')<small class="e">{{ $message }}</small>@enderror
    </div>
    <div class="f">
        <label for="stok">Stok (unit)</label>
        <input id="stok" name="stok" inputmode="numeric" value="{{ old('stok', $produk->stok) }}" placeholder="Contoh: 12">
        @error('stok')<small class="e">{{ $message }}</small>@enderror
    </div>
    <div class="f cek">
        <label><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $produk->aktif))> Produk Aktif</label>
    </div>
</div>
