<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Transaksi.
 *
 * Satu transaksi = satu pembayaran.
 * Kolom pembayaran (nama_pelanggan, metode_bayar, status_bayar, bayar,
 * kembalian) ditambahkan lewat migration 000005 sesuai FSD v1.0.
 */
class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'nomor',
        'waktu',
        'nama_pelanggan',
        'metode_bayar',
        'status_bayar',
        'total',
        'bayar',
        'kembalian',
    ];

    protected $casts = [
        'waktu'          => 'datetime',
        'total'          => 'integer',
        'bayar'          => 'integer',
        'kembalian'      => 'integer',
    ];

    /**
     * Satu transaksi memiliki banyak baris detail.
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
