<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete sesuai PPT slide 13: kategori yang masih
            // dirujuk produk tidak boleh dihapus (mencegah data produk yatim).
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->string('sku')->unique();
            $table->string('nama');
            // unsignedInteger sesuai PPT slide 13 & FSD BR-02: tidak boleh negatif.
            $table->unsignedInteger('harga');
            // stok default 0 sesuai PPT slide 13 ("Stok awal bernilai nol").
            $table->unsignedInteger('stok')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
