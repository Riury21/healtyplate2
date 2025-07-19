<?php

namespace App\Filament\Resources\PendapatanResource\Pages;

use App\Models\Menu;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PendapatanResource;

class EditPendapatan extends EditRecord
{
    protected static string $resource = PendapatanResource::class;

    protected function afterSave(): void
{
    $record = $this->record;

    $items = $record->items;
    $total = 0;
    $jumlahTotal = 0;
    $sesiUnik = [];

    foreach ($items as $item) {
        $harga = $item->harga_satuan ?? 0; // 💡 Gunakan harga yang disimpan user
        $item->total = $harga * $item->jumlah;
        $item->save();

        $total += $item->total;
        $jumlahTotal += $item->jumlah;

        if (!empty($item->sesi)) {
            $sesiUnik[strtolower($item->sesi)] = true;
        }
    }

    $jumlahSesiUnik = count($sesiUnik);

    // Ongkir
    $ongkir_per_sesi = $record->pakai_ongkir ? ($record->ongkir ?? 0) : 0;
    $total_ongkir = $record->pakai_ongkir ? ($ongkir_per_sesi * $jumlahSesiUnik) : 0;
    $ongkir_fix = $record->pakai_ongkir ? $ongkir_per_sesi : 0;

    // Diskon (jika sistem diskon massal aktif — opsional)
    $diskon = ($record->pakai_diskon && $jumlahTotal > 9) ? 0.1 : 0;
    $total_diskon = ($total + $total_ongkir) * $diskon;

    $total_pendapatan = $total + $total_ongkir - $total_diskon;

    $record->update([
        'ongkir' => $ongkir_fix,
        'total_ongkir' => $total_ongkir,
        'total_diskon' => $total_diskon,
        'total_pendapatan' => $total_pendapatan,
    ]);
}


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
