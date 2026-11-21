# Proyek Mini Warteg Ende

Sistem kasir dan manajemen produk untuk Warung Tegal Ende, dibangun dengan **Laravel 13** dan **PHP 8.3+**.
Mengacu pada PRD/FSD v1.0, 10 user story (US-01 sampai US-10), persona dan matriks hak akses, wireframe Balsamiq 1–10, dan ERD
(`kategori`, `produk`, `transaksi`, `detail_transaksi`, ditambah tabel pendukung `keranjang`).

## Peran dan hak akses (F-01)

Masuk lewat `/masuk`. Ada dua peran:

| Fitur | Kasir | Pemilik |
|---|---|---|
| Melihat dan mencari produk | Ya | Ya |
| Tambah, ubah, nonaktifkan, hapus produk | Tidak | Ya |
| Transaksi (keranjang, bayar tunai/QRIS/transfer/kasbon, bukti) | Ya | Ya |
| Melihat catatan kasbon | Ya | Ya |
| Menandai kasbon lunas | Tidak | Ya |
| Laporan penjualan harian | Tidak | Ya |

Kasir yang membuka halaman Pemilik lewat URL dialihkan ke halaman Kasir dengan pesan "Akses ditolak: Hak akses tidak memadai" (US-09).

Akun contoh dari seeder (ganti kata sandinya sebelum dipakai sungguhan):

| Peran | Email | Kata sandi |
|---|---|---|
| Pemilik | `pemilik@warteg.test` | `pemilik123` |
| Kasir | `kasir@warteg.test` | `kasir123` |

## Halaman aplikasi

| Halaman | Route | Peran |
|---|---|---|
| Masuk / Keluar | `/masuk`, `POST /keluar` | Semua |
| Katalog produk (cari dengan `?q=`) | `GET /produk` | Kasir, Pemilik |
| Tambah produk | `GET /produk/tambah` | Pemilik |
| Produk berhasil disimpan | `GET /produk/{id}/berhasil` | Pemilik |
| Ubah produk | `GET /produk/{id}/ubah` | Pemilik |
| Nonaktifkan / aktifkan produk | `GET /produk/{id}/nonaktif` | Pemilik |
| Hapus produk | `GET /produk/{id}/hapus` | Pemilik |
| Transaksi kasir (produk, keranjang, pembayaran) | `GET /transaksi/baru` | Kasir, Pemilik |
| Bukti transaksi (dengan tombol Cetak Ulang) | `GET /transaksi/{id}` | Kasir, Pemilik |
| Catatan kasbon | `GET /kasbon` | Kasir, Pemilik (pelunasan: Pemilik) |
| Laporan penjualan harian | `GET /laporan?dari=&sampai=` | Pemilik |

Alamat `/` mengarahkan Pemilik ke Katalog dan Kasir ke Transaksi.

## Fitur utama

- **Transaksi (F-03):** produk aktif dimasukkan ke keranjang (tidak boleh melebihi stok, pesan "Stok tidak mencukupi."), total dihitung otomatis, batas maksimum Rp100.000.000. Pembayaran tunai menghitung kembalian dan menolak uang kurang atau input bukan angka positif. Stok berkurang otomatis dan harga satuan disalin saat transaksi (BR-05).
- **Kasbon (F-04):** metode kasbon mewajibkan nama pelanggan dan berstatus `belum_lunas`. Pemilik menandainya lunas dari halaman Kasbon.
- **Laporan (F-05, US-10):** rekap per tanggal atau rentang tanggal. Pemasukan hanya menghitung transaksi lunas, kasbon belum lunas tampil sebagai piutang. Rentang terbalik dan tanggal masa depan ditolak, dan tanpa transaksi tampil Rp0 dengan catatan "Tidak ada transaksi pada tanggal ini."
- **Produk (F-02):** kode dan nama produk harus unik. Produk yang ada di keranjang berjalan, atau yang sudah punya riwayat transaksi, tidak dapat dihapus. Produk di keranjang juga tidak dapat dinonaktifkan.

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

### Database

**SQLite (bawaan)**

```bash
touch database/database.sqlite
```

Pastikan `.env` berisi `DB_CONNECTION=sqlite`. Jika tabel `sessions` belum dimigrasi, ubah `SESSION_DRIVER=file`.

**MySQL**: buat database kosong, lalu isi `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env`.

Jalankan migrasi dan seeder (membuat 4 kategori, 12 produk contoh, dan 2 akun):

```bash
php artisan migrate:fresh --seed
```

## Menjalankan

```bash
php artisan serve
```

Buka <http://127.0.0.1:8000> lalu masuk dengan salah satu akun di atas.

Zona waktu aplikasi diatur ke `Asia/Makassar` (WITA) di `config/app.php`, sehingga laporan harian mengikuti tanggal setempat.

## Pengujian

Uji otomatis (peran, validasi, transaksi, kasbon, laporan):

```bash
php artisan test
```

Tabel uji QA manual ada pada berkas Excel yang diserahkan terpisah (`Tabel-Uji-QA-Warteg-Ende.xlsx`).

## Dokumen pendukung

- `PERBAIKAN-H8.md` — daftar revisi terhadap spesifikasi PPT Hari ke-8 dan FSD v1.0.
- `CATATAN-DEMO-HARI-8.md` — skenario QA, tabel error, dan kunci exit ticket.
- `INSTALL.txt` — panduan paket file Hari ke-8.

## Catatan rancangan

- Kolom `detail_transaksi.qty` mengikuti ERD. Kolom `subtotal` dan `transaksi.nomor` adalah tambahan di luar ERD.
- Tabel `keranjang` ditambahkan agar produk yang sedang berada di keranjang dapat dicegah dinonaktifkan atau dihapus (batasan US-08).
- Stok dan harga bernilai bulat >= 0 sesuai FSD, sehingga stok 0 diperbolehkan untuk menandai produk "Habis".
