<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'sku',
        'nama',
        'harga',
        'stok',
        'aktif',
    ];

    protected $casts = [
        'harga' => 'integer',
        'stok'  => 'integer',
        'aktif' => 'boolean',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Satu produk dapat muncul pada banyak baris detail transaksi.
     * Relasi disiapkan untuk POS Hari ke-9.
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'produk_id');
    }
}
