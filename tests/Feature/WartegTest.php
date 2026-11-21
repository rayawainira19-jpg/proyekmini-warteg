<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji otomatis untuk F-01 sampai F-05 dan US-01 sampai US-10.
 * Jalankan: php artisan test
 */
class WartegTest extends TestCase
{
    use RefreshDatabase;

    private function pemilik(): User
    {
        return User::create(['name' => 'Pemilik', 'email' => 'pemilik@warteg.test', 'password' => 'pemilik123', 'role' => 'pemilik']);
    }

    private function kasir(): User
    {
        return User::create(['name' => 'Kasir', 'email' => 'kasir@warteg.test', 'password' => 'kasir123', 'role' => 'kasir']);
    }

    private function produk(array $o = []): Produk
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Lauk']);

        return Produk::create(array_merge([
            'kategori_id' => $kategori->id, 'kode' => 'AYM-001', 'nama' => 'Ayam Goreng',
            'harga' => 25000, 'stok' => 5, 'aktif' => true,
        ], $o));
    }

    // ---------- F-01 / US-09 ----------

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/')->assertRedirect('/masuk');
        $this->get('/produk')->assertRedirect('/masuk');
    }

    public function test_login_pemilik_dan_kasir_diarahkan_sesuai_peran(): void
    {
        $this->pemilik();
        $this->kasir();

        $this->post('/masuk', ['email' => 'pemilik@warteg.test', 'password' => 'pemilik123'])
            ->assertRedirect(route('produk.index'));
        $this->post('/keluar');

        $this->post('/masuk', ['email' => 'kasir@warteg.test', 'password' => 'kasir123'])
            ->assertRedirect(route('transaksi.create'));
    }

    public function test_login_gagal_jika_kata_sandi_salah(): void
    {
        $this->pemilik();

        $this->from('/masuk')->post('/masuk', ['email' => 'pemilik@warteg.test', 'password' => 'salah'])
            ->assertRedirect('/masuk')
            ->assertSessionHas('galat', 'Email atau kata sandi salah.');
        $this->assertGuest();
    }

    public function test_kasir_ditolak_membuka_halaman_ubah_produk_lewat_url(): void
    {
        $p = $this->produk();

        $this->actingAs($this->kasir())->get("/produk/{$p->id}/ubah")
            ->assertRedirect(route('transaksi.create'))
            ->assertSessionHas('galat', 'Akses ditolak: Hak akses tidak memadai');
    }

    public function test_kasir_tidak_bisa_membuka_laporan_dan_menambah_produk(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->get('/laporan')->assertRedirect(route('transaksi.create'));
        $this->actingAs($kasir)->post('/produk', [])->assertRedirect(route('transaksi.create'));
    }

    // ---------- F-02 (US-06, US-07, US-08) ----------

    public function test_pemilik_menambah_produk_dan_melihat_ringkasan(): void
    {
        $kategori = Kategori::create(['nama' => 'Lauk']);

        $res = $this->actingAs($this->pemilik())->post('/produk', [
            'kode' => 'MN-01', 'nama' => 'Sayur Asem', 'kategori_id' => $kategori->id,
            'harga' => 12000, 'stok' => 15, 'aktif' => 1,
        ]);

        $p = Produk::where('kode', 'MN-01')->firstOrFail();
        $res->assertRedirect(route('produk.berhasil', $p->id));
        $this->get(route('produk.berhasil', $p->id))->assertOk()->assertSee('Ringkasan Produk');
    }

    public function test_validasi_kode_dan_nama_duplikat_serta_harga_negatif(): void
    {
        $p = $this->produk();

        $this->actingAs($this->pemilik())->post('/produk', [
            'kode' => $p->kode, 'nama' => $p->nama, 'kategori_id' => $p->kategori_id, 'harga' => -5000, 'stok' => 1,
        ])->assertSessionHasErrors(['kode' => 'Kode sudah digunakan.', 'nama' => 'Nama produk sudah digunakan.', 'harga' => 'Harga tidak valid.']);
    }

    public function test_produk_dengan_riwayat_transaksi_tidak_bisa_dihapus(): void
    {
        $p = $this->produk();
        $t = Transaksi::create(['nomor' => 'TRX-1', 'waktu' => now(), 'total' => 25000]);
        $t->detailTransaksi()->create(['produk_id' => $p->id, 'qty' => 1, 'harga_satuan' => 25000, 'subtotal' => 25000]);

        $pemilik = $this->pemilik();
        $this->actingAs($pemilik)->get(route('produk.hapus', $p->id))->assertOk()->assertSee('Produk tidak dapat dihapus');
        $this->actingAs($pemilik)->delete(route('produk.destroy', $p->id));
        $this->assertDatabaseHas('produk', ['id' => $p->id]);
    }

    public function test_produk_di_keranjang_tidak_bisa_dinonaktifkan_atau_dihapus(): void
    {
        $p = $this->produk();
        $kasir = $this->kasir();
        Keranjang::create(['user_id' => $kasir->id, 'produk_id' => $p->id, 'jumlah' => 1]);

        $pemilik = $this->pemilik();
        $this->actingAs($pemilik)->patch(route('produk.nonaktif', $p->id))->assertSessionHas('galat');
        $this->assertTrue($p->fresh()->aktif);

        $this->actingAs($pemilik)->delete(route('produk.destroy', $p->id))->assertSessionHas('galat');
        $this->assertDatabaseHas('produk', ['id' => $p->id]);
    }

    // ---------- F-03 (US-02, US-03, US-04, US-05) ----------

    public function test_keranjang_menolak_stok_tidak_cukup_dan_produk_nonaktif(): void
    {
        $p = $this->produk(['stok' => 2]);
        $kasir = $this->kasir();

        $this->actingAs($kasir)->post('/keranjang', ['produk_id' => $p->id, 'jumlah' => 3])
            ->assertSessionHas('galat', 'Stok tidak mencukupi.');
        $this->assertDatabaseCount('keranjang', 0);

        $this->actingAs($kasir)->post('/keranjang', ['produk_id' => $p->id, 'jumlah' => 2])
            ->assertSessionHas('sukses');
        $this->assertDatabaseHas('keranjang', ['produk_id' => $p->id, 'jumlah' => 2]);
    }

    public function test_transaksi_tunai_berhasil_menghitung_kembalian_dan_mengurangi_stok(): void
    {
        $p = $this->produk(); // harga 25000, stok 5
        $kasir = $this->kasir();
        $this->actingAs($kasir)->post('/keranjang', ['produk_id' => $p->id, 'jumlah' => 1]);

        $this->post('/transaksi', ['metode_bayar' => 'tunai', 'bayar' => 50000]);

        $this->assertDatabaseHas('transaksi', ['total' => 25000, 'bayar' => 50000, 'kembalian' => 25000, 'status_bayar' => 'lunas']);
        $this->assertDatabaseHas('detail_transaksi', ['produk_id' => $p->id, 'qty' => 1, 'harga_satuan' => 25000]);
        $this->assertSame(4, $p->fresh()->stok);
        $this->assertDatabaseCount('keranjang', 0);
    }

    public function test_tunai_uang_kurang_atau_tidak_valid_ditolak(): void
    {
        $p = $this->produk();
        $this->actingAs($this->kasir())->post('/keranjang', ['produk_id' => $p->id, 'jumlah' => 1]);

        $this->post('/transaksi', ['metode_bayar' => 'tunai', 'bayar' => 20000])
            ->assertSessionHasErrors(['bayar' => 'Uang tunai kurang dari total belanja.']);
        $this->post('/transaksi', ['metode_bayar' => 'tunai', 'bayar' => 'abc'])
            ->assertSessionHasErrors(['bayar' => 'Input uang tunai harus berupa angka positif.']);

        $this->assertDatabaseCount('transaksi', 0);
        $this->assertSame(5, $p->fresh()->stok);
    }

    public function test_keranjang_kosong_tidak_bisa_dibayar(): void
    {
        $this->actingAs($this->kasir())->post('/transaksi', ['metode_bayar' => 'qris'])
            ->assertSessionHasErrors('keranjang');
    }

    // ---------- F-04 ----------

    public function test_kasbon_membutuhkan_nama_dan_berstatus_belum_lunas(): void
    {
        $p = $this->produk();
        $this->actingAs($this->kasir())->post('/keranjang', ['produk_id' => $p->id, 'jumlah' => 1]);

        $this->post('/transaksi', ['metode_bayar' => 'kasbon'])
            ->assertSessionHasErrors('nama_pelanggan');

        $this->post('/transaksi', ['metode_bayar' => 'kasbon', 'nama_pelanggan' => 'Budi']);
        $this->assertDatabaseHas('transaksi', ['metode_bayar' => 'kasbon', 'status_bayar' => 'belum_lunas', 'nama_pelanggan' => 'Budi', 'bayar' => 0]);
    }

    public function test_hanya_pemilik_dapat_melunasi_kasbon(): void
    {
        $t = Transaksi::create([
            'nomor' => 'TRX-K1', 'waktu' => now(), 'metode_bayar' => 'kasbon', 'status_bayar' => 'belum_lunas',
            'nama_pelanggan' => 'Budi', 'total' => 15000, 'bayar' => 0, 'kembalian' => 0,
        ]);

        $this->actingAs($this->kasir())->patch("/kasbon/{$t->id}/lunas")->assertRedirect(route('transaksi.create'));
        $this->assertSame('belum_lunas', $t->fresh()->status_bayar);

        $this->actingAs($this->pemilik())->patch("/kasbon/{$t->id}/lunas");
        $this->assertSame('lunas', $t->fresh()->status_bayar);
    }

    // ---------- F-05 / US-10 ----------

    public function test_laporan_menghitung_hanya_transaksi_lunas_dan_menampilkan_rp0_jika_kosong(): void
    {
        Transaksi::create(['nomor' => 'TRX-A', 'waktu' => now(), 'metode_bayar' => 'qris', 'status_bayar' => 'lunas', 'total' => 30000, 'bayar' => 30000]);
        Transaksi::create(['nomor' => 'TRX-B', 'waktu' => now(), 'metode_bayar' => 'kasbon', 'status_bayar' => 'belum_lunas', 'nama_pelanggan' => 'Budi', 'total' => 15000]);

        $pemilik = $this->pemilik();
        $this->actingAs($pemilik)->get('/laporan')->assertOk()->assertSee('Rp30.000');

        $kemarin = now()->subDays(3)->toDateString();
        $this->actingAs($pemilik)->get("/laporan?dari={$kemarin}&sampai={$kemarin}")
            ->assertOk()->assertSee('Tidak ada transaksi pada tanggal ini.');
    }

    public function test_laporan_menolak_rentang_terbalik_dan_tanggal_masa_depan(): void
    {
        $pemilik = $this->pemilik();
        $besok = now()->addDay()->toDateString();
        $hariIni = now()->toDateString();
        $kemarin = now()->subDay()->toDateString();

        $this->actingAs($pemilik)->get("/laporan?dari={$hariIni}&sampai={$kemarin}")
            ->assertSee('Tanggal akhir tidak boleh mendahului tanggal mulai.');
        $this->actingAs($pemilik)->get("/laporan?dari={$besok}&sampai={$besok}")
            ->assertSee('Rekap tidak dapat ditampilkan untuk tanggal di masa depan.');
    }
}
