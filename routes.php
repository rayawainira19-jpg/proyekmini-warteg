<?php

use App\Http\Controllers\KatalogController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/katalog');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
