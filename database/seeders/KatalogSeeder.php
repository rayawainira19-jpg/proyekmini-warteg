<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Produk;
class KatalogSeeder extends Seeder {
    public function run(): void {
        $minuman = Kategori::firstOrCreate(['nama' => 'Minuman']);
        $makanan = Kategori::firstOrCreate(['nama' => 'Makanan']);
        $alat = Kategori::firstOrCreate(['nama' => 'Alat Tulis']);
        foreach ([
            ['KOP-001', 'Kopi Ende', 15000, 12, $minuman->id],
            ['TEH-001', 'Teh Kotak', 5000, 0, $minuman->id],
            ['ROT-001', 'Roti', 8000, 7, $makanan->id],
            ['NAS-001', 'Nasi Bungkus', 18000, 5, $makanan->id],
            ['PEN-001', 'Pulpen', 3000, 20, $alat->id],
            ['BUK-001', 'Buku Tulis', 6000, 15, $alat->id],
        ] as [$sku, $nama, $harga, $stok, $kategoriId]) {
            Produk::updateOrCreate(['sku' => $sku], [
                'nama' => $nama, 'harga' => $harga, 'stok' => $stok,
                'kategori_id' => $kategoriId,
            ]);
        }
    }
}
