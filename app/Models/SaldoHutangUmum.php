<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaldoHutangUmum extends Model
{
    use SoftDeletes;

    protected $table = 'saldo_hutang_umums';

    protected $fillable = [
        'tanggal',
        'transaksi',
        'jenis_transaksi',
        'jumlah',
        'keterangan',
    ];
}

