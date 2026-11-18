<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model DetailTransaksi.
 *
 * Memiliki dua foreign key: transaksi_id dan produk_id.
 * harga_satuan menyimpan salinan harga master produk saat transaksi
 * berlangsung (FSD BR-05: Riwayat Harga Transaksi), sehingga perubahan
 * harga di masa depan tidak mengubah nota lama.
 */
class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jumlah'       => 'integer',
        'harga_satuan' => 'integer',
        'subtotal'     => 'integer',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
