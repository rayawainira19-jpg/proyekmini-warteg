<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1. detail_transaksi.jumlah -> qty, menyamakan dengan ERD dan kamus data FSD.
 * 2. Tabel pendukung keranjang (US-02): isi keranjang tiap pengguna disimpan di
 *    database agar produk yang sedang berada di keranjang berjalan dapat dicegah
 *    untuk dinonaktifkan/dihapus (batasan US-08).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->renameColumn('jumlah', 'qty');
        });

        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->timestamps();
            $table->unique(['user_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->renameColumn('qty', 'jumlah');
        });
    }
};
