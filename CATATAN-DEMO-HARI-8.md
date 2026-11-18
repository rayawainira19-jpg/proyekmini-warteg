# Catatan demo: database dan katalog Laravel Hari 8

Catatan pendamping [slide materi Hari 8](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit?usp=sharing). Gunakan urutan berikut untuk menunjukkan **kode yang sudah ada di proyek** sambil menjelaskan hasilnya. Nomor baris di bawah memudahkan navigasi; di VS Code tekan `Ctrl+P`, ketik nama berkas diikuti `:nomor_baris`, lalu tekan Enter.

## Peta cepat saat presentasi

| Slide | Apa yang ditunjukkan | Buka di proyek |
| --- | --- | --- |
| [5–6](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p5) | Pemeriksaan dan koneksi database | [.env.example](.env.example), baris 23; [config/database.php](config/database.php), baris 49 |
| [7–15](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p7) | Empat tabel, foreign key, dan migration | [migration kategori](database/migrations/2026_11_18_000001_create_kategori_table.php), baris 7; [migration produk](database/migrations/2026_11_18_000002_create_produk_table.php), baris 7 |
| [16–17](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p16) | Model dan query Eloquent | [Produk.php](app/Models/Produk.php), baris 5; terminal untuk contoh query |
| [18–19](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p18) | Seeder kategori dan produk | [KatalogSeeder.php](database/seeders/KatalogSeeder.php), baris 8 |
| [20–22](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p20) | Relasi dan eager loading | [Kategori.php](app/Models/Kategori.php), baris 8; [Produk.php](app/Models/Produk.php), baris 9; [KatalogController.php](app/Http/Controllers/KatalogController.php), baris 18 |
| [23–25](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p23) | Route → controller → Blade | [web.php](routes/web.php), baris 7; [KatalogController.php](app/Http/Controllers/KatalogController.php), baris 11; [index.blade.php](resources/views/katalog/index.blade.php), baris 34 |
| [27–30](https://docs.google.com/presentation/d/1vBXAfxh3fZwoVKlrkMomu94uBzNuhdVC32pLl3VTJsc/edit#slide=id.p27) | QA, masalah umum, dan exit ticket | [KatalogTest.php](tests/Feature/KatalogTest.php), baris 16; bagian QA di bawah |

## Persiapan demo

Buka terminal **di dalam VS Code dev container**, lalu jalankan:

```sh
cd /workspace/Praktik_Hari_8/kasir-h8
php -v
php -m | grep pdo_mysql
php artisan --version
php artisan route:list --path=katalog --no-interaction
php artisan migrate:status --no-interaction
php artisan db:show --counts --no-interaction
```

Pada pemeriksaan 17 November 2026, container menggunakan PHP 8.4.27, proyek menggunakan Laravel 13.42.0, koneksi mengarah ke MariaDB `kasir_h8`, dan keempat migration kasir sudah berstatus `Ran`. Database saat itu berisi 3 kategori dan 6 produk. Slide merujuk Laravel 12; contoh di catatan ini mengikuti kode Laravel 13 yang terpasang. Status dan isi database dapat berubah, jadi periksa lagi sebelum demo.

Untuk menampilkan halaman, jalankan di terminal terpisah:

```sh
cd /workspace/Praktik_Hari_8/kasir-h8
php artisan serve --host=0.0.0.0 --port=8000 --no-interaction
```

Buka port **8000** melalui tab **Ports** di VS Code, kemudian buka path `/katalog`. Hentikan server dengan `Ctrl+C`. Jika port 8000 sudah dipakai, pilih port lain dan buka port yang sama di VS Code.

Jika migration belum berjalan, pastikan database tujuan memang database latihan, lalu jalankan `php artisan migrate --no-interaction`. Jika data contoh belum tersedia, jalankan `php artisan db:seed --class=KatalogSeeder --no-interaction`. Seeder akan memperbarui data enam SKU contoh yang sudah ada; gunakan hanya pada database latihan yang boleh diubah. Jangan gunakan `migrate:fresh` pada database yang datanya ingin disimpan karena perintah itu menghapus tabel.

## 1. Koneksi dan alur data — slide 4–7

**Tunjukkan:** [.env.example](.env.example) baris 23–28 dan [config/database.php](config/database.php) bagian `mysql`. Gunakan `.env.example` saat berbagi layar agar password `.env` tidak terlihat.

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=kasir_h8
```

**Katakan:** “Laravel membaca konfigurasi koneksi dari environment. Di dev container, `db` adalah nama service MariaDB. `127.0.0.1` seperti pada slide menunjuk ke container aplikasi sendiri, sehingga bukan host database untuk susunan container ini.” Kredensial sebenarnya diatur di `.env`; setelah mengubahnya, jalankan `php artisan config:clear --no-interaction`.

Gambarkan perjalanan permintaan:

```text
Browser /katalog → route → KatalogController → model Produk → database
                                              ↓
                                    Blade ← data produk + kategori
```

Hari ke-7 memakai array contoh; di proyek ini controller mengambil data melalui model. Migration membentuk tabel, seeder mengisi data contoh, dan model membantu PHP mengakses data serta relasinya.

## 2. ERD dan migration — slide 8–15

**Tunjukkan berurutan:**

1. [Migration kategori](database/migrations/2026_11_18_000001_create_kategori_table.php), baris 7–13: `id` sebagai primary key, `nama` unik, dan `timestamps()`.
2. [Migration produk](database/migrations/2026_11_18_000002_create_produk_table.php), baris 7–14: `kategori_id` mengarah ke `kategori.id`, SKU unik, dan stok awal `0`.
3. [Migration transaksi](database/migrations/2026_11_18_000003_create_transaksi_table.php), baris 7–12, dan [migration detail transaksi](database/migrations/2026_11_18_000004_create_detail_transaksi_table.php), baris 7–14.

Sorot potongan yang benar-benar ada pada migration produk:

```php
$table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
$table->string('sku')->unique();
$table->unsignedInteger('harga');
$table->unsignedInteger('stok')->default(0);
```

**Katakan:** “Satu kategori dapat memiliki banyak produk. `kategori_id` berada di tabel produk dan harus menunjuk kategori yang ada. `restrictOnDelete()` mencegah kategori yang masih dirujuk produk dihapus.” Method `up()` membuat tabel; `down()` berisi cara membatalkannya. Tabel induk dibuat lebih dahulu; urutan nama/timestamp migration memastikan `kategori` sebelum `produk` dan keduanya tersedia sebelum `detail_transaksi`.

Pada detail transaksi, `harga_satuan` menyimpan harga saat penjualan; `subtotal` dan `total` memakai `unsignedBigInteger`. Tabel transaksi dan detail sudah disiapkan, tetapi halaman katalog belum membuat transaksi.

**Demo terminal:**

```sh
php artisan migrate:status --no-interaction
php artisan db:table produk --no-interaction
```

Di hasil `db:table`, tunjukkan foreign key `kategori_id` yang mereferensikan `kategori.id`, serta indeks unik `sku`.

## 3. Seeder — slide 18–19

**Tunjukkan:** [KatalogSeeder.php](database/seeders/KatalogSeeder.php), baris 8–22. Kategori dibuat terlebih dahulu dengan `firstOrCreate`; setiap produk di dalam perulangan dicari berdasarkan SKU dengan `updateOrCreate`.

```php
$minuman = Kategori::firstOrCreate(['nama' => 'Minuman']);

// Salah satu baris data di dalam array produk:
['KOP-001', 'Kopi Ende', 15000, 12, $minuman->id],

// Di dalam perulangan:
Produk::updateOrCreate(['sku' => $sku], [
    'nama' => $nama, 'harga' => $harga, 'stok' => $stok,
    'kategori_id' => $kategoriId,
]);
```

**Katakan:** “Jika SKU sudah ada, data produk contoh diperbarui; jika belum, produk dibuat. Karena itu menjalankan seeder berulang tidak menambah duplikat SKU.” [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) baris 11 juga memanggil `KatalogSeeder`.

Untuk demonstrasi pengulangan, jalankan perintah seed berikut dua kali **hanya pada database latihan**:

```sh
php artisan db:seed --class=KatalogSeeder --no-interaction
php artisan db:seed --class=KatalogSeeder --no-interaction
php artisan tinker --no-interaction --execute 'dump(App\Models\Kategori::count(), App\Models\Produk::count());'
```

Pada database latihan yang hanya berisi data contoh, hasilnya tetap `3` dan `6`. Jika sudah ada data lain, jumlah total dapat lebih besar. Menjalankan seeder lagi mengembalikan nama, harga, stok, dan kategori untuk keenam SKU contoh ke nilai dalam seeder.

## 4. Model, query, dan relasi — slide 16–22

**Tunjukkan:** [Produk.php](app/Models/Produk.php), baris 5–10. `$table` menentukan nama tabel, `$fillable` menentukan kolom yang boleh diisi melalui mass assignment, dan `$casts` membaca `harga` serta `stok` sebagai integer.

Bandingkan kedua arah relasi:

```php
// Kategori.php: satu kategori memiliki banyak produk.
return $this->hasMany(Produk::class, 'kategori_id');

// Produk.php: satu produk dimiliki satu kategori.
return $this->belongsTo(Kategori::class, 'kategori_id');
```

**Demo query:** contoh berikut dijalankan dari terminal proyek, satu per satu. Ini contoh eksplorasi sesuai slide; query katalog yang dipakai aplikasi berada di controller.

```sh
php artisan tinker --no-interaction --execute 'dump(App\Models\Produk::orderBy("nama")->get(["nama", "stok"]));'
php artisan tinker --no-interaction --execute 'dump(App\Models\Produk::where("stok", ">", 0)->get(["nama", "stok"]));'
php artisan tinker --no-interaction --execute 'dump(App\Models\Produk::where("sku", "KOP-001")->first()?->kategori?->nama);'
php artisan tinker --no-interaction --execute 'dump(App\Models\Kategori::where("nama", "Minuman")->first()?->produk()->where("stok", ">", 0)->count());'
```

Jelaskan bahwa `get()` menghasilkan collection dan `first()` menghasilkan satu model atau `null`; `find(id)` mencari berdasarkan primary key. Pada data seeder yang belum diubah, dua perintah terakhir menghasilkan `Minuman` dan `1`. Properti `$produk->kategori` mengambil kategori terkait, sedangkan method `$kategori->produk()` dapat dilanjutkan dengan kondisi query.

Di [KatalogController.php](app/Http/Controllers/KatalogController.php) baris 18, tunjukkan `Produk::with('kategori')`. Ini memuat kategori untuk kumpulan produk di awal sehingga akses kategori setiap baris di Blade tidak memicu query terpisah per produk.

## 5. Route, controller, dan Blade — slide 23–25

1. **Route:** [routes/web.php](routes/web.php) baris 7 menghubungkan `GET /katalog` ke `KatalogController@index`. Route `/` mengalihkan ke katalog.
2. **Controller:** [KatalogController.php](app/Http/Controllers/KatalogController.php) baris 11–26 memvalidasi pencarian, memuat relasi, mengurutkan produk, dan mengirim `$produk` serta `$pencarian` ke view. Ini pengembangan dari contoh controller yang lebih ringkas pada slide.
3. **View:** [resources/views/katalog/index.blade.php](resources/views/katalog/index.blade.php) baris 34–58 mengulang produk dengan `@forelse`, mengakses nama kategori melalui objek, dan menampilkan pesan jika hasil kosong.

Tunjukkan potongan berikut di view:

```blade
@forelse ($produk as $item)
    <td><span class="product-name">{{ $item->nama }}</span><span class="sku">{{ $item->sku }}</span></td>
    <td><span class="category">{{ $item->kategori->nama }}</span></td>
    <td class="price">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
    {{-- ... kolom stok ... --}}
@empty
    {{-- ... pesan Belum ada produk. ... --}}
@endforelse
```

Potongan view di atas disingkat untuk presentasi; buka berkas asli untuk struktur tabel lengkap. **Katakan:** “`->` mengakses properti model dan relasinya, `{{ }}` melakukan escaping HTML, dan `@empty` menangani collection kosong.”

**Demo di browser:** buka `/katalog`, tunjukkan **Kopi Ende → Minuman → Rp 15.000 → 12 tersedia** dan **Teh Kotak → Habis**. Cari `Kopi`, lalu cari kata yang tidak ada untuk memunculkan **Belum ada produk.** Gunakan tombol **Reset** untuk kembali. Hasil ini mengasumsikan data contoh belum diubah.

## 6. QA dan pertanyaan penutup — slide 27–30

| Skenario | Cara menunjukkan hasil |
| --- | --- |
| Seeder dapat diulang | Jalankan dua kali dan hitung data seperti pada bagian 3; nilai `3`/`6` berlaku untuk database yang hanya berisi data contoh. |
| Stok nol | Tunjukkan Teh Kotak berlabel **Habis** di katalog. |
| Relasi | Tunjukkan Kopi Ende berkategori Minuman dan query relasi pada bagian 4. |
| Hasil kosong | Cari kata yang tidak ada; view menampilkan **Belum ada produk.** |
| Foreign key tidak valid | Tunjukkan definisi foreign key di `db:table produk` dan pengujian di [KatalogTest.php](tests/Feature/KatalogTest.php), baris 90. |

Jalankan pengujian yang sudah ada dari terminal dev container:

```sh
vendor/bin/phpunit tests/Feature/KatalogTest.php
```

Pengujian menggunakan SQLite **in-memory**, sehingga tidak mengubah MariaDB latihan. Pengujian membuktikan perilaku aplikasi pada SQLite; `migrate:status`, `db:table`, dan demo katalog memeriksa koneksi serta struktur MariaDB yang sedang dipakai.

**Jawaban singkat exit ticket:**

- **Migration, model, seeder?** Migration membentuk/mengubah struktur tabel; model mewakili data dan relasi; seeder mengisi data contoh.
- **Mengapa kategori lebih dahulu?** Produk memiliki foreign key yang merujuk tabel kategori.
- **Di mana `kategori_id`?** Di tabel `produk`; nilainya merujuk `kategori.id`.
- **Manfaat `with('kategori')`?** Memuat kategori bersama kumpulan produk agar tidak memuatnya berulang untuk setiap baris.
- **Bekal CRUD hari ke-9?** Struktur tabel, model, relasi, dan katalog sudah ada; formulir serta operasi CRUD produk belum dibuat di sini.

Jika koneksi gagal, periksa service MariaDB, `DB_HOST=db`, nama database dan kredensial; `could not find driver` mengarah ke ekstensi `pdo_mysql`, sedangkan tabel tidak ditemukan perlu diperiksa lewat `migrate:status` dan nama tabel pada model.
