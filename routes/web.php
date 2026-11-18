<?php

use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/produk');

Route::controller(ProdukController::class)->prefix('produk')->name('produk.')->group(function () {
    Route::get('/', 'index')->name('index');                                        // 1. Katalog
    Route::get('/tambah', 'create')->name('create');                                // 2. Tambah
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/ubah', 'edit')->whereNumber('id')->name('edit');              // 3. Ubah
    Route::put('/{id}', 'update')->whereNumber('id')->name('update');
    Route::get('/{id}/nonaktif', 'konfirmasiNonaktif')->whereNumber('id')->name('nonaktif.konfirmasi'); // 4. Nonaktifkan
    Route::patch('/{id}/nonaktif', 'nonaktif')->whereNumber('id')->name('nonaktif');
    Route::get('/{id}/hapus', 'konfirmasiHapus')->whereNumber('id')->name('hapus');  // 5. Hapus
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
});
