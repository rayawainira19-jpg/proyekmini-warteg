<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->dateTime('waktu');
            $table->string('nama_pelanggan')->nullable();
            $table->enum('metode_bayar', ['tunai', 'qris', 'transfer', 'kasbon']);
            $table->enum('status_bayar', ['lunas', 'belum_lunas']);
            $table->unsignedInteger('total');
            $table->unsignedInteger('bayar');
            $table->unsignedInteger('kembalian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
