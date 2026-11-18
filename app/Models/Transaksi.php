<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    public $timestamps = false;
    protected $fillable = ['waktu', 'nama_pelanggan', 'metode_bayar', 'status_bayar', 'total', 'bayar', 'kembalian'];
    protected $casts = ['waktu' => 'datetime'];

    public function detail(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
