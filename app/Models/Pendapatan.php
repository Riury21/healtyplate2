<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pendapatan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama',
        'tanggal',
        'pakai_diskon',
        'pakai_ongkir',
        'ongkir',
        'total_ongkir',
        'total_diskon',
        'total_pendapatan',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(PendapatanDetail::class);
    }
    
    public function items()
    {
        return $this->hasMany(PendapatanDetail::class);
    }

}
