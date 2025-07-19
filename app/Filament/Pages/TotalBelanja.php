<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Pembelian;
use Illuminate\Support\Carbon;

class TotalBelanja extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Total Belanja';
    protected static ?string $navigationGroup = 'Rekap Data';
    protected static string $view = 'filament.pages.total-belanja';

    public $rekap = [];
    public $totalPerJenis = [];
    public $grandTotal = 0;

    public $bulan;
    public $tahun;

public function mount(): void
{
    $this->bulan = request('bulan')
        ? \Carbon\Carbon::parse(request('bulan'))->month
        : now()->month;

    $this->tahun = request('bulan')
        ? \Carbon\Carbon::parse(request('bulan'))->year
        : now()->year;

    $jenisList = [
        'harian' => 'Pembelian Harian',
        'bahan utama' => 'Pembelian Bahan Utama',
        'sembako' => 'Pembelian Sembako',
        'buah' => 'Pembelian Buah',
        'plastik' => 'Pembelian Plastik',
        'gas' => 'Pembelian Gas',
        'vip' => 'Pembelian VIP',
        'stiker' => 'Pembelian Stiker',
    ];

    // ✅ Ambil hanya transaksi dengan jenis_transaksi = 'debit'
    $pembelian = Pembelian::whereMonth('tanggal', $this->bulan)
        ->whereYear('tanggal', $this->tahun)
        ->where('jenis_transaksi', 'debit') // ⬅️ filter pasti debit
        ->get();

    // ✅ Group hanya data debit berdasarkan tanggal
    $grouped = $pembelian->groupBy('tanggal');

    $rekap = [];
    $totalJenis = array_fill_keys(array_keys($jenisList), 0);
    $grand = 0;

    foreach ($grouped as $tanggal => $items) {
        $baris = ['tanggal' => $tanggal];
        $totalHarian = 0;

        foreach ($jenisList as $key => $label) {
            // Data sudah hanya debit, jadi ini aman
            $sum = $items->where('jenis_pembelian', $key)->sum('jumlah');
            $baris[$key] = $sum;
            $totalJenis[$key] += $sum;
            $totalHarian += $sum;
        }

        $baris['total'] = $totalHarian;
        $grand += $totalHarian;
        $rekap[] = $baris;
    }

    $this->rekap = $rekap;
    $this->totalPerJenis = $totalJenis;
    $this->grandTotal = $grand;
}


}
