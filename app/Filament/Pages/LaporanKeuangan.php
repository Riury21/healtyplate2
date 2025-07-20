<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Pendapatan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Laporankeuangan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan-keuangan';

    public $bulan;
    public $pendapatans;

    public function mount()
    {
        $this->bulan = request('bulan') ?? now()->format('Y-m');
        $this->loadData();
    }

    public function loadData()
    {
        $tanggal = Carbon::parse($this->bulan);
        $bulanIni = Carbon::parse($this->bulan);

        // Pendapatan dengan jumlah pax (jumlah menu yang diorder)
        $this->pendapatans = DB::table('pendapatans')
            ->whereMonth('tanggal', $tanggal->month)
            ->whereYear('tanggal', $tanggal->year)
            ->leftJoin('pendapatan_details', 'pendapatans.id', '=', 'pendapatan_details.pendapatan_id')
            ->select('pendapatans.nama',
                DB::raw('SUM(pendapatans.total_pendapatan) as total'),
                DB::raw('SUM(pendapatan_details.jumlah) as pax')
            )
            ->groupBy('pendapatans.nama')
            ->get();

        $totalPendapatan = $this->pendapatans->sum('total');

        // Data pembelian per jenis_transaksi
        $this->pembelianPerJenis = \App\Models\Pembelian::whereMonth('tanggal', $tanggal->month)
            ->whereYear('tanggal', $tanggal->year)
            ->select('jenis_pembelian', \DB::raw('SUM(jumlah) as total'))
            ->groupBy('jenis_pembelian')
            ->get();

        $totalPembelian = $this->pembelianPerJenis->sum('total');

        // Daftar beban tetap
        $bebanTetap = collect([
            ['transaksi' => 'Beban Gaji Owner', 'total' => 10000000],
            ['transaksi' => 'Beban Sewa dibayar dimuka', 'total' => 2750000],
            ['transaksi' => 'Beban Penyusutan Alat', 'total' => 860317],
            ['transaksi' => 'Beban Penyusutan Alat Lama', 'total' => 406250],
        ]);

        // Ambil beban dari jurnal_umums
        $jurnal = DB::table('jurnal_umums')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->where('transaksi', 'like', '%Beban%')
            ->select('transaksi', DB::raw('SUM(jumlah) as total'))
            ->groupBy('transaksi')
            ->get();

        // Ambil beban dari saldo_hutang_umums
        $hutang = DB::table('saldo_hutang_umums')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->where('transaksi', 'like', '%Beban%')
            ->select('transaksi', DB::raw('SUM(jumlah) as total'))
            ->groupBy('transaksi')
            ->get();

        // Gabungkan hasil dari dua tabel dan jumlahkan per transaksi
        $gabung = $jurnal->concat($hutang)
            ->groupBy('transaksi')
            ->map(function ($item, $key) {
                return [
                    'transaksi' => $key,
                    'total' => $item->sum('total')
                ];
            })
            ->values();

        // Gabungkan dengan beban tetap
        $final = $gabung->concat($bebanTetap)->groupBy('transaksi')->map(function ($item, $key) {
            return [
                'transaksi' => $key,
                'total' => collect($item)->sum('total')
            ];
        })->values();

        // Hitung total semua
        $this->totalBeban = $final->sum('total') ?: 1; // Hindari pembagi 0

        // Hitung persen per item
        $this->bebanGabungan = $final->map(function ($row) {
            return (object) [
                'transaksi' => $row['transaksi'],
                'total' => $row['total'],
                'persen' => round(($row['total'] / $this->totalBeban) * 100, 2)
            ];
        });

        // Pajak UMKM 0,5%
        $pengurangPajak = 50000000 / 12;
        $this->pajakUmkm = max(0, 0.005 * ($totalPendapatan - $pengurangPajak));

        // Laba awal
        $this->labaAwal = $totalPendapatan - $totalPembelian - $this->totalBeban - $this->pajakUmkm;

        // Pengembangan 25%
        $this->pengembangan = 0.25 * $this->labaAwal;

        // Charity 5%
        $this->charity = 0.05 * $this->labaAwal;

        // Laba akhir
        $this->labaAkhir = $this->labaAwal - $this->pengembangan - $this->charity;
    }

    protected function getViewData(): array
    {
        return [
            'bulan' => $this->bulan,
            'pendapatans' => $this->pendapatans,
            'totalSeluruh' => $this->pendapatans->sum('total'),
            'totalPax' => $this->pendapatans->sum('pax'),
            'pembelianPerJenis' => $this->pembelianPerJenis,
            'totalPembelian' => $this->pembelianPerJenis->sum('total'),
            'bebanGabungan' => $this->bebanGabungan,
            'totalBeban' => $this->bebanGabungan->sum('total'),
            'pajakUmkm' => $this->pajakUmkm,
            'labaAwal' => $this->labaAwal,
            'pengembangan' => $this->pengembangan,
            'charity' => $this->charity,
            'labaAkhir' => $this->labaAkhir,
        ];
    }
}
