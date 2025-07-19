<?php

namespace App\Filament\Resources\SaldoHutangUmumResource\Pages;

use App\Filament\Resources\SaldoHutangUmumResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSaldoHutangUmum extends CreateRecord
{
    protected static string $resource = SaldoHutangUmumResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
