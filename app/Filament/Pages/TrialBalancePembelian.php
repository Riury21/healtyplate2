<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TrialBalancePembelian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static string $view = 'filament.pages.trial-balance';
    protected static ?string $navigationLabel = 'Trial Balance Pembelian & Hutang';
    protected static ?string $navigationGroup = 'Rekap Data';
    protected static ?string $title = 'Trial Balance Pembelian & Saldo Hutang Umum';
    protected static ?int $navigationSort = 3;
}
