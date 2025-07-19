<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class KontrolPembelian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.kontrol-pembelian'; // arahkan ke file blade yang kamu buat

    protected static ?string $navigationGroup = 'Rekap Data'; // agar masuk ke menu Rekap
    protected static ?string $title = 'Kontrol Pembelian';
}
