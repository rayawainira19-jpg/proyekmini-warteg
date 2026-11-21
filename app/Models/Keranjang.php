<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Isi keranjang transaksi berjalan milik satu pengguna (US-02). */
class Keranjang extends Model
{
    protected $table = 'keranjang';

    protected $fillable = ['user_id', 'produk_id', 'jumlah'];

    protected $casts = ['jumlah' => 'integer'];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
