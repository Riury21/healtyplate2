<?php

namespace App\Filament\Resources\SaldoHutangUmumResource\Pages;

use App\Filament\Resources\SaldoHutangUmumResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaldoHutangUmum extends EditRecord
{
    protected static string $resource = SaldoHutangUmumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
