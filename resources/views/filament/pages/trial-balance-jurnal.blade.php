<?php
use App\Models\JurnalUmum;
use App\Models\Pendapatan;
use Illuminate\Support\Carbon;
use App\Models\Pembelian;
use App\Models\SaldoHutangUmum;
use Illuminate\Support\Facades\DB;

$bulan = request('bulan', now()->format('Y-m'));
$bulanIni = Carbon::parse($bulan);

// --- Variabel dasar
$saldoBulanLalu = 20858229; // default awal
$bulanSebelumnya = $bulanIni->copy()->subMonth();

// Ambil arus kas bersih bulan sebelumnya jika tersedia
$arusKasBulanLalu = cache()->remember("arus_kas_{$bulanSebelumnya->format('Y_m')}", 60, function () use ($bulanSebelumnya) {
    return null; // nanti isi logika ambil nilai real dari DB jika ada
});

// Jika ada data dari bulan lalu, pakai itu
if ($arusKasBulanLalu !== null) {
    $saldoBulanLalu = $arusKasBulanLalu;
}

// Total pembelian bahan
$totalPembelian = Pembelian::whereMonth('tanggal', $bulanIni->month)
    ->whereYear('tanggal', $bulanIni->year)
    ->where('jenis_transaksi', 'debit')
    ->sum('jumlah');

// Beban-beban dari dua tabel
$namaBeban = [
    'Beban Ongkir', 'Beban Perlengkapan', 'Beban Konsumsi', 'Beban Sampah',
    'Beban Entertain', 'Beban Gaji', 'Beban Listrik', 'Beban Wifi'
];

$totalBebanJurnal = JurnalUmum::whereMonth('tanggal', $bulanIni->month)
    ->whereYear('tanggal', $bulanIni->year)
    ->whereIn('transaksi', $namaBeban)
    ->sum('jumlah');

$totalBebanSaldo = SaldoHutangUmum::whereMonth('tanggal', $bulanIni->month)
    ->whereYear('tanggal', $bulanIni->year)
    ->whereIn('transaksi', $namaBeban)
    ->sum('jumlah');

// Beban tetap: Beban Owner Rp10.000.000
$bebanTetapOwner = 10_000_000;

// Gabungan semua
$totalBeban = $totalBebanJurnal + $totalBebanSaldo + $bebanTetapOwner;

$bulan = request('bulan', now()->format('Y-m'));
$bulanIni = Carbon::parse($bulan);

// Daftar urutan tetap
$urutanTransaksi = [
    'Piutang QL', 'Piutang UAD', 'Piutang PKU Kotagede', 'Piutang Sedayu', 'Piutang AVO',
    'Piutang Klinik Indira', 'Piutang Siloam', 'BCA 484',
    'Pph 23 atas jasa', 'Potongan',
    ...collect(range(1, 12))->flatMap(function ($m) {
        setlocale(LC_TIME, 'id_ID.UTF-8');
        $bulanName = \Carbon\Carbon::create()->locale('id')->month($m)->translatedFormat('F');
        return [
            'Piutang QL ' . $bulanName,
            'Piutang UAD ' . $bulanName,
            'Piutang PKU Kotagede ' . $bulanName,
            'Piutang Sedayu ' . $bulanName,
            'Piutang AVO ' . $bulanName,
            'Piutang Klinik Indira ' . $bulanName,
            'Piutang Siloam ' . $bulanName,
        ];
    })->toArray(),
    'Beban Entertain', 'Beban Gaji', 'Beban Listrik', 'Beban Wifi', 'Beban Alat',
    'BCA Pak Sigit',
    'Pendapatan April', 'Pendapatan Mei',
    'Pendapatan QL', 'Pendapatan UAD', 'Pendapatan PKU Kotagede',
    'Pendapatan Sedayu', 'Pendapatan AVO', 'Pendapatan Klinik Indira',
    'Pendapatan Siloam', 'Pendapatan Daily',
];

// Relasi khusus (untuk rumus debit)
$pemetaans = [
    'Piutang QL' => 'Pendapatan QL',
    'Piutang UAD' => 'Pendapatan UAD',
    'Piutang PKU Kotagede' => 'Pendapatan PKU Kotagede',
    'Piutang Sedayu' => 'Pendapatan Sedayu',
    'Piutang AVO' => 'Pendapatan AVO',
    'Piutang Klinik Indira' => 'Pendapatan Klinik Indira',
    'Piutang Siloam' => 'Pendapatan Siloam',
    'BCA 484' => 'Pendapatan Daily',
];

// Ambil data
$jurnalData = JurnalUmum::whereMonth('tanggal', $bulanIni->month)
    ->whereYear('tanggal', $bulanIni->year)
    ->get()
    ->groupBy('transaksi')
    ->map(fn($items, $nama) => [
        'transaksi' => $nama,
        'debit' => $items->where('jenis_transaksi', 'debit')->sum('jumlah'),
        'kredit' => $items->where('jenis_transaksi', 'kredit')->sum('jumlah'),
    ]);

$pendapatanData = Pendapatan::whereMonth('tanggal', $bulanIni->month)
    ->whereYear('tanggal', $bulanIni->year)
    ->get()
    ->groupBy('nama')
    ->map(fn($items, $nama) => [
        'transaksi' => $nama,
        'kredit' => $items->sum('total_pendapatan'),
    ]);

// Gabungan dan filter
$sortedData = collect($urutanTransaksi)->map(function ($nama) use ($jurnalData, $pendapatanData) {
    $debit = $jurnalData[$nama]['debit'] ?? 0;
    $kredit = $jurnalData[$nama]['kredit'] ?? 0;

    $isManualPendapatan = $nama === 'BCA Pak Sigit' || preg_match('/^Pendapatan\s(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)$/u', $nama);

    return [
        'transaksi' => $nama,
        'debit' => $debit,
        'kredit' => $kredit,
        'kredit_pendapatan' => $isManualPendapatan
            ? max($kredit - $debit, 0)
            : ($pendapatanData[$nama]['kredit'] ?? 0),
    ];
})->filter(function ($item) {
    $nama = $item['transaksi'];
    $isPiutangBulanan = str_contains($nama, 'Piutang') && preg_match('/Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember/', $nama);
    if ($isPiutangBulanan) {
        return $item['debit'] > 0 || $item['kredit'] > 0 || $item['kredit_pendapatan'] > 0;
    }
    return true;
})->values();

// ✅ Gunakan nilai debit selisih dari baris BCA 484
$bcaRow = $sortedData->firstWhere('transaksi', 'BCA 484');
$kreditPendapatanBca = $pendapatanData['Pendapatan Daily']['kredit'] ?? 0;
$totalPenerimaan = $kreditPendapatanBca + ($bcaRow['debit'] ?? 0) - ($bcaRow['kredit'] ?? 0);

// Hitung Arus Kas Bersih
$arusKasBersih = $saldoBulanLalu + $totalPenerimaan - $totalPembelian - $totalBeban;

// Simpan untuk bulan depan
cache()->put("arus_kas_{$bulanIni->format('Y_m')}", $arusKasBersih, 3600);
?>


<x-filament::page>
    <div class="mb-4">
        <form method="GET" class="flex items-center gap-2">
            <label for="bulan" class="text-sm font-semibold">Pilih Bulan:</label>
            <input
                type="month"
                id="bulan"
                name="bulan"
                value="{{ request('bulan', now()->format('Y-m')) }}"
                class="border border-gray-300 rounded px-3 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800"
            >
            <div class="mb-0 p-0 border rounded bg-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white px-4 py-1 rounded text-sm hover:opacity-90 transition"
                    >
                        Tampilkan
                    </button>
                </form>
            </div>
        </form>
    </div>

    {{-- 📗 Laporan Arus Kas --}}
    <div class="mt-6 border rounded-md shadow-sm overflow-x-auto bg-white dark:bg-gray-900 w-full max-w-md mx-auto">
        <div class="bg-green-200 dark:bg-gray-800 text-center font-semibold py-2 text-sm text-green-900 dark:text-white rounded-t-md">
            📗 Laporan Arus Kas
        </div>
        <table class="w-full text-xs border border-gray-300 dark:border-gray-700 border-collapse table-fixed">
            <thead class="bg-gray-700 text-white">
                <tr>
                    <th class="px-2 py-1 border border-gray-300 dark:border-gray-600 text-left">Keterangan</th>
                    <th class="px-2 py-1 border border-gray-300 dark:border-gray-600 text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900">
                <tr>
                    <td class="px-2 py-1 border text-left">Saldo Kas Bulan Lalu</td>
                    <td class="px-2 py-1 border text-right">Rp {{ number_format($saldoBulanLalu, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="px-2 py-1 border text-left">+ Penerimaan Pembayaran (BCA 484)</td>
                    <td class="px-2 py-1 border text-right text-green-700">
                        Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td class="px-2 py-1 border text-left">- Pembelian Bahan</td>
                    <td class="px-2 py-1 border text-right text-red-700">
                        Rp {{ number_format($totalPembelian, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td class="px-2 py-1 border text-left">- Beban-beban</td>
                    <td class="px-2 py-1 border text-right text-red-700">
                        Rp {{ number_format($totalBeban, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="bg-yellow-100 font-semibold">
                    <td class="px-2 py-1 border text-left">Arus Kas Bersih</td>
                    <td class="px-2 py-1 border text-right">
                        Rp {{ number_format($arusKasBersih, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    {{-- 📒 Jurnal Umum & Pendapatan --}}
    <div class="w-full border rounded-xl shadow-md overflow-x-auto bg-white dark:bg-gray-900 mt-6">
        <div class="bg-gradient-to-r from-yellow-300 to-yellow-200 dark:from-gray-700 dark:to-gray-800 text-center font-bold py-3 text-lg text-black dark:text-white rounded-t-xl">
            📒 Jurnal Umum & Pendapatan
        </div>
        <table class="w-full text-sm border border-gray-300 dark:border-gray-700 table-fixed border-collapse">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left">Nama Transaksi</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Debit Jurnal</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Kredit Jurnal</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Selisih</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Kredit Pendapatan</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900">
                @php
                    $totalDebit = 0;
                    $totalKredit = 0;
                    $totalPendapatan = 0;
                    $totalSelisih = 0;
                @endphp

                @foreach ($sortedData as $item)
                    @php
                        $nama = $item['transaksi'];
                        $debit = $item['debit'];
                        $kredit = $item['kredit'];
                        $kreditPendapatan = $item['kredit_pendapatan'];

                        $totalDebit += $debit;
                        $totalKredit += $kredit;
                        $totalPendapatan += $kreditPendapatan;

                        if (array_key_exists($nama, $pemetaans)) {
                            $namaPendapatan = $pemetaans[$nama];
                            $kreditTerkait = $pendapatanData[$namaPendapatan]['kredit'] ?? 0;
                            $selisih = $kreditTerkait + $debit - $kredit;
                        } elseif (
                            preg_match('/^Pendapatan\s(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)$/u', $nama)
                            || $nama === 'BCA Pak Sigit'
                        ) {
                            $selisih = 0;
                        } else {
                            $selisih = $debit - $kredit;
                        }

                        $totalSelisih += $selisih;
                    @endphp

                    <tr class="hover:bg-yellow-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-left">
                            {{ $nama }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-green-700">
                            {{ $debit ? 'Rp ' . number_format($debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-red-700">
                            {{ $kredit ? 'Rp ' . number_format($kredit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-blue-700">
                            {{ $selisih ? 'Rp ' . number_format($selisih, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-red-700">
                            {{ $kreditPendapatan ? 'Rp ' . number_format($kreditPendapatan, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach

                <tr class="bg-yellow-200 font-bold dark:bg-yellow-600/20">
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-left">TOTAL</td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-green-800">
                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-red-800">
                        Rp {{ number_format($totalKredit, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-blue-800">
                        Rp {{ number_format($totalSelisih, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-red-800">
                        Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</x-filament::page>
