# Perbaikan Proyek Kasir H8 — Catatan Revisi

Dokumen ini menjelaskan perbedaan antara proyek `kasir-h8-main` asli dengan
spesifikasi pada **PPT Hari ke-8** dan **FSD v1.0 Sistem Kasir Warung Tegal
Ende**, beserta perbaikan yang sudah diterapkan.

## Ringkasan temuan dan perbaikan

| # | Masalah | Sumber spec yang dilanggar | Perbaikan |
|---|---|---|---|
| 1 | `DatabaseSeeder` menulis data sendiri (Camilan, Es Teh Manis stok 25) alih-alih memanggil `KatalogSeeder` | PPT slide 18/27 (data contoh Teh Kotak stok 0); catatan demo baris 526 | `DatabaseSeeder` diubah menjadi `$this->call([KatalogSeeder::class])` |
| 2 | Kolom `kategori.nama` tidak `unique()` | PPT slide 12; catatan demo baris 483 | Ditambah `->unique()` |
| 3 | `produk.kategori_id` pakai `cascadeOnDelete()` | PPT slide 13 (`restrictOnDelete`) | Diganti `restrictOnDelete()` |
| 4 | `produk.harga` bertipe `integer` (boleh negatif) | PPT slide 13 (`unsignedInteger`); FSD BR-02 | Diganti `unsignedInteger` |
| 5 | `produk.stok` bertipe `integer` tanpa default | PPT slide 13 (`unsignedInteger` default 0) | Diganti `unsignedInteger('stok')->default(0)` |
| 6 | Kolom `produk.aktif` tidak ada | FSD BR-03; kamus data produk | Ditambah via migration baru `000005` |
| 7 | Kolom pembayaran transaksi tidak ada (`nama_pelanggan`, `metode_bayar`, `status_bayar`, `bayar`, `kembalian`) | FSD kamus data transaksi; BR-04 | Ditambah via migration baru `000005` |
| 8 | Model `Transaksi` & `DetailTransaksi` belum dibuat | FSD ERD (4 entitas) | Dibuat dua model beserta relasi |
| 9 | File sampah `kasir_hari6` (SQLite bekas) & `unique()` (kosong) di root | Kebersihan repo | Dihapus |
| 10 | `storage/framework/views/*.php` (compiled view) ikut di-zip | `.gitignore` bawaan Laravel | Dihapus |

## File yang diubah

- `database/migrations/2026_11_18_000001_create_kategori_table.php` — `nama` jadi unique.
- `database/migrations/2026_11_18_000002_create_produk_table.php` — `restrictOnDelete`, `unsignedInteger('harga')`, `unsignedInteger('stok')->default(0)`.
- `database/seeders/DatabaseSeeder.php` — memanggil `KatalogSeeder`.
- `app/Models/Produk.php` — `aktif` masuk `$fillable` & `$casts`; relasi `detailTransaksi()`.

## File yang ditambah

- `database/migrations/2026_11_18_000005_tambah_kolom_fsd_untuk_hari9.php` — kolom FSD untuk persiapan Hari ke-9 (mengikuti aturan slide 14: perubahan baru = migration baru).
- `app/Models/Transaksi.php`
- `app/Models/DetailTransaksi.php`

## Catatan naming PPT vs FSD (dipertahankan sesuai PPT untuk H8)

- PPT memakai `sku`; FSD memakai `kode` (maks 20). Tetap `sku` sesuai PPT.
- PPT memakai `jumlah` + `subtotal` pada `detail_transaksi`; FSD memakai `qty` tanpa `subtotal`. Tetap `jumlah`/`subtotal` sesuai PPT.

## Dampak pada pengujian

`tests/Feature/KatalogTest.php` memakai `KatalogSeeder` langsung (`$this->seed(KatalogSeeder::class)`), jadi pengujian tidak terpengaruh oleh perubahan `DatabaseSeeder`. Data seeder (Kopi Ende, Teh Kotak stok 0, dst.) tetap sama sehingga semua asserst (urutan, "Rp 15.000", "12 tersedia", "Habis", "Menampilkan 6 produk") tetap valid.

## Cara menerapkan di komputer kelompok

Karena struktur tabel berubah (kolom `nama` jadi unique, tipe `harga`/`stok` berubah, kolom baru ditambah), jalankan ulang migration dari bersih **pada database latihan**:

```sh
php artisan migrate:fresh --seed --no-interaction
```

> `migrate:fresh` menghapus semua tabel lalu membuat ulang. Hanya gunakan pada database latihan yang boleh dikosongkan. Jika database sudah berisi data penting, jangan pakai perintah ini.

Lalu verifikasi:

```sh
php artisan migrate:status --no-interaction
php artisan db:seed --class=KatalogSeeder --no-interaction   # boleh dijalankan dua kali, hasil tetap 3/6
php artisan tinker --execute 'dump(App\Models\Kategori::count(), App\Models\Produk::count());'  // 3, 6
vendor/bin/phpunit tests/Feature/KatalogTest.php
php artisan serve
```

Buka `http://localhost:8000/katalog` — harus tampil **Kopi Ende → Minuman → Rp 15.000 → 12 tersedia** dan **Teh Kotak → Habis**.
