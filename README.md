# Proyek Mini Warteg Ende

Aplikasi katalog dan manajemen produk untuk Warung Tegal Ende, dibangun dengan **Laravel 13** dan **PHP 8.3+**.
Mengikuti wireframe Balsamiq 1–10, user story US-01/06/07/08, dan ERD (`kategori`, `produk`, `transaksi`, `detail_transaksi`).

## Fitur

Lima halaman manajemen produk di bawah `/produk`:

| Halaman | Route |
|---|---|
| Katalog (cari dengan `?q=`) | `GET /produk` |
| Tambah produk | `GET /produk/tambah` |
| Ubah produk | `GET /produk/{id}/ubah` |
| Nonaktifkan / aktifkan produk | `GET /produk/{id}/nonaktif` |
| Hapus produk | `GET /produk/{id}/hapus` |

Alamat `/` otomatis diarahkan ke `/produk`.

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- SQLite (bawaan) atau MySQL

## Instalasi

```bash
git clone https://github.com/rayawainira19-jpg/proyekmini-warteg.git
cd proyekmini-warteg

composer install

cp .env.example .env
php artisan key:generate
```

## Database

**SQLite (bawaan)**

```bash
touch database/database.sqlite
```

Pastikan `.env` berisi `DB_CONNECTION=sqlite`. Jika tabel `sessions` belum dimigrasi, ubah `SESSION_DRIVER=file`.

**MySQL**

Buat database kosong, lalu isi di `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate:fresh --seed
```

Seeder mengisi 4 kategori (Minuman, Makanan, Snack, Lauk) dan 12 produk contoh. Beberapa produk sengaja bernilai stok 0 (mis. Teh Kotak dan Perkedel).

## Menjalankan

```bash
php artisan serve
```

Buka <http://127.0.0.1:8000/produk>. Untuk memeriksa daftar route: `php artisan route:list`.

## Skenario uji (FSD)

- **TC-01** Tambah produk valid → tersimpan satu kali dan tampil di daftar.
- **TC-02** Kode produk duplikat → muncul pesan "Kode sudah digunakan."
- **TC-03** Harga negatif (mis. `-5000`) → muncul pesan "Harga tidak valid." Format seperti `15.000` atau `2,5` pada stok juga ditolak.
- Hapus produk yang punya riwayat transaksi → ditolak, lalu ditawarkan untuk dinonaktifkan. (Seeder belum membuat data transaksi, jadi tambahkan transaksi contoh terlebih dahulu.)
- Hapus produk tanpa riwayat transaksi → muncul konfirmasi, lalu terhapus.
- Nonaktifkan lalu aktifkan kembali sebuah produk.
- Pencarian: `/produk?q=kopi`, `?q=xyz` (tidak ada hasil → "Produk tidak ditemukan"), `?q=k` (kurang dari 2 karakter → "Kata kunci minimal 2 karakter.").
- `/produk/999/ubah` → kembali ke katalog dengan pesan "Produk tidak ditemukan atau sudah dihapus."

## Dokumen pendukung

- `PERBAIKAN-H8.md` — daftar revisi terhadap spesifikasi PPT Hari ke-8 dan FSD v1.0.
- `CATATAN-DEMO-HARI-8.md` — skenario QA, tabel error, dan kunci exit ticket.
- `INSTALL.txt` — panduan paket file Hari ke-8.
