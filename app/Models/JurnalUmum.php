<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{
    protected $fillable = [
        'tanggal',
        'transaksi',
        'jenis_transaksi',
        'jumlah',
        'keterangan',
    ];
}

