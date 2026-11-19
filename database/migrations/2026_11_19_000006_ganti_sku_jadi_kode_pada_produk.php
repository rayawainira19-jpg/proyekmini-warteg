<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan nama kolom produk dengan FSD dan seluruh tampilan (view,
 * ProdukController, ProdukRequest) yang memakai "kode", bukan "sku".
 *
 * Dibuat sebagai migration BARU, bukan mengedit migration H8
 * (aturan PPT slide 14).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->renameColumn('sku', 'kode');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->renameColumn('kode', 'sku');
        });
    }
};
