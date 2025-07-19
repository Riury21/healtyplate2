<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $fillable = [
        'nama_barang',
        'jumlah',
        'nilai_barang',
        'total_nilai',
        'tahun_pembelian',
    ];
}
