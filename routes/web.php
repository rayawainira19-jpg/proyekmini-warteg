<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect(AuthController::beranda(auth()->user()))
        : redirect()->route('login');
});

// F-01: login (belum masuk) dan logout
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'form'])->name('login');
    Route::post('/masuk', [AuthController::class, 'masuk'])->middleware('throttle:10,1')->name('login.proses');
});
Route::post('/keluar', [AuthController::class, 'keluar'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {

    // Kasir dan Pemilik: katalog (baca), transaksi, keranjang, kasbon (baca)
    Route::middleware('role:kasir,pemilik')->group(function () {
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');   // 1. Katalog

        Route::get('/transaksi/baru', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->whereNumber('id')->name('transaksi.show');

        Route::post('/keranjang', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
        Route::delete('/keranjang/{id}', [KeranjangController::class, 'hapus'])->whereNumber('id')->name('keranjang.hapus');
        Route::delete('/keranjang', [KeranjangController::class, 'kosongkan'])->name('keranjang.kosongkan');

        Route::get('/kasbon', [KasbonController::class, 'index'])->name('kasbon.index');
    });

    // Hanya Pemilik: kelola produk, pelunasan kasbon, laporan
    Route::middleware('role:pemilik')->group(function () {
        Route::controller(ProdukController::class)->prefix('produk')->name('produk.')->group(function () {
            Route::get('/tambah', 'create')->name('create');                                // 2. Tambah
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/ubah', 'edit')->whereNumber('id')->name('edit');              // 3. Ubah
            Route::put('/{id}', 'update')->whereNumber('id')->name('update');
            Route::get('/{id}/berhasil', 'berhasil')->whereNumber('id')->name('berhasil');
            Route::get('/{id}/nonaktif', 'konfirmasiNonaktif')->whereNumber('id')->name('nonaktif.konfirmasi'); // 4. Nonaktifkan
            Route::patch('/{id}/nonaktif', 'nonaktif')->whereNumber('id')->name('nonaktif');
            Route::get('/{id}/hapus', 'konfirmasiHapus')->whereNumber('id')->name('hapus');  // 5. Hapus
            Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
        });

        Route::patch('/kasbon/{id}/lunas', [KasbonController::class, 'lunas'])->whereNumber('id')->name('kasbon.lunas');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });
});
