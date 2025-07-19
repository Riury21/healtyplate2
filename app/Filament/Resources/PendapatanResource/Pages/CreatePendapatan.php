<?php

namespace App\Filament\Resources\PendapatanResource\Pages;

use App\Models\Pendapatan;
use App\Models\Menu;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendapatanResource;

class CreatePendapatan extends CreateRecord
{
    protected static string $resource = PendapatanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $total = 0;
    $totalDiskon = 0;
    $sesiUnik = [];

    if (!empty($data['items']) && is_array($data['items'])) {
        foreach ($data['items'] as &$item) {
            $harga = (int) $item['harga_satuan'];
            $jumlah = (int) $item['jumlah'];
            $diskonPersen = (int) ($item['diskon'] ?? 0);

            $totalSebelumDiskon = $harga * $jumlah;
            $diskonNominal = ($diskonPersen / 100) * $totalSebelumDiskon;
            $totalItem = $totalSebelumDiskon - $diskonNominal;

            $item['total'] = $totalItem;

            $total += $totalItem;
            $totalDiskon += $diskonNominal;

            if (!empty($item['sesi'])) {
                $sesiUnik[strtolower($item['sesi'])] = true;
            }
        }

        $jumlahSesiUnik = count($sesiUnik);
        $ongkirPerSesi = $data['pakai_ongkir'] ? ((int) $data['ongkir']) : 0;
        $totalOngkir = $ongkirPerSesi * $jumlahSesiUnik;

        $data['total_ongkir'] = $totalOngkir;
        $data['total_diskon'] = $totalDiskon;
        $data['total_pendapatan'] = $total + $totalOngkir;
    }

    return $data;
}


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
