<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendapatanDetail extends Model
{
    protected $fillable = [
        'pendapatan_id',
        'menu_id',
        'sesi',
        'jumlah',
        'diskon',
        'harga_satuan',
        'total',
        'keterangan',
    ];

    public function pendapatan()
    {
        return $this->belongsTo(Pendapatan::class);
    }

    public function menu()
    {
        return $this->belongsTo(\App\Models\Menu::class);
    }
}
