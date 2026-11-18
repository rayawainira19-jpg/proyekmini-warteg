<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $minuman = Kategori::firstOrCreate(['nama' => 'Minuman']);
        $lauk    = Kategori::firstOrCreate(['nama' => 'Lauk Utama']);
        $sayur   = Kategori::firstOrCreate(['nama' => 'Sayur']);

        $kopi = Produk::updateOrCreate(['kode' => 'KOPI-001'], ['kategori_id' => $minuman->id, 'nama' => 'Kopi Ende',   'harga' => 15000, 'stok' => 12, 'aktif' => true]);
        Produk::updateOrCreate(['kode' => 'TEH-002'],  ['kategori_id' => $minuman->id, 'nama' => 'Teh Kotak',   'harga' => 5000,  'stok' => 0,  'aktif' => false]);
        Produk::updateOrCreate(['kode' => 'AIR-003'],  ['kategori_id' => $minuman->id, 'nama' => 'Air Mineral', 'harga' => 4000,  'stok' => 20, 'aktif' => true]);
        Produk::updateOrCreate(['kode' => 'AYM-004'],  ['kategori_id' => $lauk->id,    'nama' => 'Ayam Goreng', 'harga' => 12000, 'stok' => 15, 'aktif' => true]);
        Produk::updateOrCreate(['kode' => 'SYR-005'],  ['kategori_id' => $sayur->id,   'nama' => 'Sayur Asem',  'harga' => 5000,  'stok' => 8,  'aktif' => true]);

        // Satu transaksi contoh agar KOPI-001 "memiliki riwayat transaksi" (tidak boleh dihapus)
        if (Transaksi::count() === 0) {
            $t = Transaksi::create([
                'waktu' => now(), 'metode_bayar' => 'tunai', 'status_bayar' => 'lunas',
                'total' => 15000, 'bayar' => 20000, 'kembalian' => 5000,
            ]);
            $t->detail()->create(['produk_id' => $kopi->id, 'qty' => 1, 'harga_satuan' => 15000]);
        }
    }
}
