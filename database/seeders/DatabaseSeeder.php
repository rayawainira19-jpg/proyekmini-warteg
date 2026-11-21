<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder bawaan Laravel.
 *
 * Hanya memanggil KatalogSeeder agar seluruh data contoh katalog
 * (3 kategori + 6 produk, termasuk Teh Kotak stok 0) konsisten
 * dengan yang dipakai KatalogSeeder dan pengujian KatalogTest.
 *
 * Dengan demikian `php artisan db:seed` (tanpa --class) menghasilkan
 * data yang sama persis dengan `php artisan db:seed --class=KatalogSeeder`,
 * sehingga skenario QA "Stok nol -> Habis" tetap terpenuhi.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KatalogSeeder::class,
            UserSeeder::class,
        ]);
    }
}
