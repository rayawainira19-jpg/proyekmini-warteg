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
            $table->foreignId('kategori_id')->constrained('kategori');
            $table->string('kode', 20)->unique();      // BR-01
            $table->string('nama', 100);
            $table->unsignedInteger('harga');          // BR-02
            $table->unsignedInteger('stok');
            $table->boolean('aktif')->default(true);   // BR-03
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
