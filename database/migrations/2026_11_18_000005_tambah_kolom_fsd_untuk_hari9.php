<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration penyesuaian FSD untuk persiapan Hari ke-9.
 *
 * Dibuat sebagai migration BARU (bukan mengedit migration H8 yang sudah ada),
 * sesuai aturan PPT slide 14: "Jangan mengedit migration yang telah dibagikan
 * untuk perubahan berikutnya; buat migration baru."
 *
 * Perubahan mengacu pada FSD v1.0 "Perancangan Data":
 *  - produk.aktif        : Boolean default true  (FSD BR-03: menu nonaktif
 *                          tidak ditampilkan di antarmuka transaksi kasir).
 *  - transaksi.nama_pelanggan : String nullable  (untuk transaksi kasbon).
 *  - transaksi.metode_bayar   : enum tunai/qris/transfer/kasbon.
 *  - transaksi.status_bayar   : enum lunas/belum_lunas.
 *  - transaksi.bayar          : Integer (uang dibayar, tunai).
 *  - transaksi.kembalian      : Integer (bayar - total, BR-04).
 *
 * Catatan: kolom ini belum dipakai di katalog H8, tetapi disiapkan agar
 * struktur tabel sudah sesuai FSD sebelum CRUD & POS Hari ke-9 dibangun.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom aktif pada produk.
        Schema::table('produk', function (Blueprint $table) {
            $table->boolean('aktif')->default(true)->after('stok');
        });

        // 2. Tambah kolom pembayaran pada transaksi.
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('nama_pelanggan')->nullable()->after('nomor');
            $table->enum('metode_bayar', ['tunai', 'qris', 'transfer', 'kasbon'])
                ->default('tunai')
                ->after('waktu');
            $table->enum('status_bayar', ['lunas', 'belum_lunas'])
                ->default('lunas')
                ->after('metode_bayar');
            $table->unsignedInteger('bayar')->default(0)->after('total');
            $table->unsignedInteger('kembalian')->default(0)->after('bayar');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['kembalian', 'bayar', 'status_bayar', 'metode_bayar', 'nama_pelanggan']);
        });

        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('aktif');
        });
    }
};
