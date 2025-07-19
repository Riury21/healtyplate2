<?php

namespace App\Filament\Pages;

use App\Models\Pendapatan;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class RekapPendapatanHarian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.rekap-pendapatan-harian';
    protected static ?string $navigationLabel = 'Total Orderan';
    protected static ?string $navigationGroup = 'Rekap Data';
    protected static ?string $title = 'Rekap Orderan';

    public $rekap = [];

    public function mount(): void
    {
        // Ambil semua data pendapatan lalu dikelompokkan per tanggal & customer
        $data = Pendapatan::with(['items', 'items.menu'])
            ->get()
            ->groupBy('tanggal')
            ->map(function ($pendapatans, $tanggal) {
                $baris = [
                    'tanggal' => $tanggal,
                    'instansi' => [],
                    'total_pendapatan' => 0,
                    'total_jumlah' => 0,
                    'total_sesi' => 0,
                ];

                foreach ($pendapatans as $pendapatan) {
                    $nama = $pendapatan->nama;

                    $jumlah = $pendapatan->items->sum('jumlah');
                    $sesi = $pendapatan->items->pluck('sesi')->unique()->count();

                    $baris['instansi'][$nama] = [
                        'pendapatan' => $pendapatan->total_pendapatan ?? 0,
                        'jumlah' => $jumlah,
                    ];

                    $baris['total_pendapatan'] += $pendapatan->total_pendapatan ?? 0;
                    $baris['total_jumlah'] += $jumlah;
                    $baris['total_sesi'] += $sesi;
                }

                return $baris;
            });

        $this->rekap = $data->sortKeys()->values()->toArray();
    }
}
