<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Produk;

class KatalogSeeder extends Seeder {
    public function run(): void {
        // Kategori untuk warung makan (wartegende).
        // "Alat Tulis" dihapus karena tidak relevan dengan warteg; diganti "Lauk".
        $minuman = Kategori::firstOrCreate(['nama' => 'Minuman']);
        $makanan = Kategori::firstOrCreate(['nama' => 'Makanan']);
        $snack   = Kategori::firstOrCreate(['nama' => 'Snack']);
        $lauk    = Kategori::firstOrCreate(['nama' => 'Lauk']);

        // Bersihkan kategori lama yang sudah tidak dipakai (idempoten).
        // Hapus juga produk yang masih terikat ke kategori tersebut supaya
        // tidak ada data yatim (foreign key restrictOnDelete akan menolak
        // kalau masih ada produk).
        $alatLama = Kategori::where('nama', 'Alat Tulis')->first();
        if ($alatLama) {
            Produk::where('kategori_id', $alatLama->id)->delete();
            $alatLama->delete();
        }

        // Format: [kode, nama, harga, stok, kategori_id]
        // Stok 0 dipakai untuk menguji label "Habis" di halaman katalog.
        foreach ([
            // Minuman
            ['KOP-001', 'Kopi Ende',          15000, 12, $minuman->id],
            ['TEH-001', 'Teh Kotak',           5000,  0, $minuman->id], // Habis
            ['JUS-001', 'Jus Jeruk',          12000,  8, $minuman->id],
            // Makanan
            ['NAS-001', 'Nasi Bungkus',       18000,  5, $makanan->id],
            ['MIE-001', 'Mie Goreng',         15000, 10, $makanan->id],
            // Lauk (lauk-pauk, biasanya jadi pilihan lauk nasi)
            ['AYM-001', 'Ayam Goreng',        12000,  9, $lauk->id],
            ['IKN-001', 'Ikan Bakar',         20000,  4, $lauk->id],
            ['TEL-001', 'Telur Dadar',         7000, 15, $lauk->id],
            ['TMP-001', 'Tempe Goreng',        5000, 20, $lauk->id],
            ['PDA-001', 'Perkedel',            4000,  0, $lauk->id],   // Habis
            // Snack
            ['PIS-001', 'Pisang Goreng',       8000,  6, $snack->id],
            ['KRP-001', 'Keripik Singkong',   10000,  0, $snack->id],  // Habis
        ] as [$kode, $nama, $harga, $stok, $kategoriId]) {
            Produk::updateOrCreate(['kode' => $kode], [
                'nama'        => $nama,
                'harga'       => $harga,
                'stok'        => $stok,
                'kategori_id' => $kategoriId,
            ]);
        }
    }
}
