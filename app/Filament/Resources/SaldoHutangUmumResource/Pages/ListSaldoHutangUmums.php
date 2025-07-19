<?php

namespace App\Filament\Resources\SaldoHutangUmumResource\Pages;

use App\Filament\Resources\SaldoHutangUmumResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSaldoHutangUmums extends ListRecords
{
    protected static string $resource = SaldoHutangUmumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
