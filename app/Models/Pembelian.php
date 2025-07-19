<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tanggal',
        'jenis_pembelian',
        'transaksi',
        'jenis_transaksi',
        'kuantiti',
        'jumlah',
        'keterangan',
        'total_debit_hari',
    ];
}
