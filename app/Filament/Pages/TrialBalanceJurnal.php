<?php

// app/Filament/Pages/TrialBalanceJurnal.php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TrialBalanceJurnal extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.trial-balance-jurnal';

    protected static ?string $title = 'Trial Balance Jurnal Umum';
    protected static ?string $navigationGroup = 'Rekap Data';
}
