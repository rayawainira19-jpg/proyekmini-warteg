# Proyek Mini Warteg Ende

Aplikasi katalog dan manajemen produk untuk Warung Tegal Ende (**Laravel 13**, **PHP 8.3+**).
Dirancang dari wireframe Balsamiq 1–10, user story US-01/06/07/08, dan ERD
(`kategori`, `produk`, `transaksi`, `detail_transaksi`).

## Halaman aplikasi

Aplikasi terdiri dari 5 halaman di bawah `/produk`. Alamat `/` otomatis diarahkan ke halaman Katalog.

### 1. Katalog Produk — `GET /produk`
Daftar produk dalam tabel: **Kode, Nama produk, Kategori, Harga, Stok, Status, Aksi** (10 produk per halaman).
- **Pencarian** (`?q=`) berdasarkan nama, kode, atau kategori. Kata kunci minimal 2 karakter.
- Pencarian tanpa hasil menampilkan "Produk tidak ditemukan. Periksa kembali kata kunci pencarian."
- Katalog kosong menampilkan "Belum ada data produk" dengan tombol **+ Tambah produk**.
- Produk nonaktif ditampilkan redup dengan status "Nonaktif".
- Tiap baris punya aksi **Edit | Nonaktifkan/Aktifkan | Hapus**.

### 2. Tambah Produk — `GET /produk/tambah`
Formulir produk baru: Kode Produk, Nama Produk, Kategori, Harga (Rp), Stok (unit), dan kotak centang Produk Aktif.
- Tombol **Simpan Produk** menyimpan data, lalu kembali ke Katalog dengan pesan sukses.
- Validasi: kode tidak boleh kembar, harga tidak boleh negatif, stok harus angka bulat.
- Jika penyimpanan gagal, data yang sudah diisi tetap ada dan muncul pesan kesalahan.

### 3. Ubah Produk — `GET /produk/{id}/ubah`
Formulir yang sama dengan halaman Tambah, sudah terisi data produk. Tombol **Simpan Perubahan** memperbarui data.
- ID yang tidak ada → kembali ke Katalog dengan pesan "Produk tidak ditemukan atau sudah dihapus."

### 4. Nonaktifkan / Aktifkan Produk — `GET /produk/{id}/nonaktif`
Halaman konfirmasi dengan ringkasan produk. Produk nonaktif tidak tersedia untuk penjualan, tetapi datanya tetap tersimpan dan bisa diaktifkan lagi.
Teks dan tombol menyesuaikan status: **Ya, Nonaktifkan** atau **Ya, Aktifkan**.

### 5. Hapus Produk — `GET /produk/{id}/hapus`
Halaman konfirmasi dengan dua kemungkinan tampilan:
- **Produk tanpa riwayat transaksi** → konfirmasi, lalu tombol **Ya, Hapus**.
- **Produk dengan riwayat transaksi** → penghapusan ditolak, ditawarkan **Nonaktifkan** sebagai gantinya.

## Menjalankan

Persyaratan: PHP 8.3+, Composer, Node.js + npm, dan SQLite (bawaan) atau MySQL.

```bash
git clone https://github.com/rayawainira19-jpg/proyekmini-warteg.git
cd proyekmini-warteg

composer install
npm install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite      # SQLite (bawaan, DB_CONNECTION=sqlite)
php artisan migrate:fresh --seed
php artisan serve
```

Buka <http://127.0.0.1:8000/produk>.

- Jika tabel `sessions` belum dimigrasi, ubah `SESSION_DRIVER=file` di `.env`.
- Untuk MySQL, buat database kosong lalu isi `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env`.
- Seeder mengisi 4 kategori (Minuman, Makanan, Snack, Lauk) dan 12 produk contoh. Beberapa produk bernilai stok 0.

## Skenario uji per halaman (FSD)

| Halaman | Skenario | Hasil yang diharapkan |
|---|---|---|
| Katalog | `/produk?q=kopi` | Menampilkan produk yang cocok |
| Katalog | `/produk?q=xyz` | "Produk tidak ditemukan..." |
| Katalog | `/produk?q=k` | "Kata kunci minimal 2 karakter." |
| Tambah | **TC-01** data valid (mis. kode MN-01, Ayam Goreng, harga 12000, stok 15) | Tersimpan sekali dan tampil di Katalog |
| Tambah | **TC-02** kode duplikat | "Kode sudah digunakan." |
| Tambah | **TC-03** harga `-5000`, juga `15.000` atau `2,5` pada stok | "Harga tidak valid." |
| Ubah | `/produk/999/ubah` | Kembali ke Katalog: "Produk tidak ditemukan atau sudah dihapus." |
| Nonaktifkan | Nonaktifkan lalu aktifkan kembali satu produk | Status di Katalog berubah mengikuti |
| Hapus | Produk dengan riwayat transaksi | Ditolak, ditawarkan nonaktif |
| Hapus | Produk tanpa riwayat transaksi | Konfirmasi, lalu terhapus |

## Dokumen pendukung

- `PERBAIKAN-H8.md` — daftar revisi terhadap spesifikasi PPT Hari ke-8 dan FSD v1.0.
- `CATATAN-DEMO-HARI-8.md` — skenario QA, tabel error, dan kunci exit ticket.
- `INSTALL.txt` — panduan paket file Hari ke-8.
