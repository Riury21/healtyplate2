<?php
use App\Models\JurnalUmum;
use App\Models\Pendapatan;
use Illuminate\Support\Carbon;

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

    <div class="w-full border rounded overflow-x-auto mt-6">
        <div class="bg-yellow-300 text-center font-bold py-2">Jurnal Umum & Pendapatan</div>
        <table class="min-w-full text-sm border">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-2 border text-left">Nama Transaksi</th>
                    <th class="px-4 py-2 border text-right">Debit Jurnal</th>
                    <th class="px-4 py-2 border text-right">Kredit Jurnal</th>
                    <th class="px-4 py-2 border text-right">Selisih (Debit)</th>
                    <th class="px-4 py-2 border text-right">Kredit Pendapatan</th>
                </tr>
            </thead>
            <tbody>
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
                    <tr class="hover:bg-purple-50">
                        <td class="px-4 py-1 border">{{ $nama }}</td>
                        <td class="px-4 py-1 border text-right text-green-700">
                            {{ $debit ? 'Rp ' . number_format($debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-1 border text-right text-red-700">
                            {{ $kredit ? 'Rp ' . number_format($kredit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-1 border text-right text-blue-700">
                            {{ $selisih ? 'Rp ' . number_format($selisih, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-1 border text-right text-red-700">
                            {{ $kreditPendapatan ? 'Rp ' . number_format($kreditPendapatan, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach

                <tr class="bg-yellow-200 font-bold">
                    <td class="px-4 py-1 border">TOTAL</td>
                    <td class="px-4 py-1 border text-right text-green-800">
                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-1 border text-right text-red-800">
                        Rp {{ number_format($totalKredit, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-1 border text-right text-blue-800">
                        Rp {{ number_format($totalSelisih, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-1 border text-right text-red-800">
                        Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-filament::page>
