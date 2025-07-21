<x-filament::page>

    {{-- 🔍 Filter Bulan --}}
    <div class="mb-4">
        <form method="GET" class="flex items-center gap-3">
            <label for="bulan" class="text-sm font-semibold">Pilih Bulan:</label>
            <input
                type="month"
                id="bulan"
                name="bulan"
                value="{{ request('bulan', now()->format('Y-m')) }}"
                class="border border-gray-300 rounded px-3 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800"
            >
            <button
                type="submit"
                class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white px-4 py-1 rounded text-sm hover:opacity-90 transition"
            >
                Tampilkan
            </button>
        </form>
    </div>

    @php
        $bulan = request('bulan', now()->format('Y-m'));
        $bulanIni = \Carbon\Carbon::parse($bulan);
        $bulanLalu = $bulanIni->copy()->subMonth();
        $awalBulanIni = $bulanIni->copy()->startOfMonth();

        $saldoAwalFarras = 97791296;

        // Total sampai akhir bulan lalu
        $totalDebitSebelumnya = \App\Models\SaldoHutangUmum::where('transaksi', 'farras')
            ->where('jenis_transaksi', 'debit')
            ->where('tanggal', '<', $awalBulanIni)
            ->sum('jumlah');

        $totalKreditHutangSebelumnya = \App\Models\SaldoHutangUmum::where('transaksi', 'farras')
            ->where('jenis_transaksi', 'kredit')
            ->where('tanggal', '<', $awalBulanIni)
            ->sum('jumlah');

        $totalKreditPembelianSebelumnya = \App\Models\Pembelian::where('transaksi', 'farras')
            ->where('jenis_transaksi', 'kredit')
            ->where('tanggal', '<', $awalBulanIni)
            ->sum('jumlah');

        $saldoFarrasLalu = $saldoAwalFarras + $totalDebitSebelumnya - $totalKreditHutangSebelumnya - $totalKreditPembelianSebelumnya;

        // Bulan ini
        $debitFarras = \App\Models\SaldoHutangUmum::where('transaksi', 'farras')
            ->where('jenis_transaksi', 'debit')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('jumlah');

        $kreditFarrasHutang = \App\Models\SaldoHutangUmum::where('transaksi', 'farras')
            ->where('jenis_transaksi', 'kredit')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('jumlah');

        $kreditFarrasPembelian = \App\Models\Pembelian::where('transaksi', 'farras')
            ->where('jenis_transaksi', 'kredit')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('jumlah');

        $saldoFarras = $saldoFarrasLalu + $debitFarras - $kreditFarrasPembelian - $kreditFarrasHutang;

        // =========================
        // TRIAL BALANCE PEMBELIAN
        // =========================
        $kategori = [
            'harian' => 'Pembelian Harian',
            'bahan utama' => 'Pembelian Bahan Utama',
            'sembako' => 'Pembelian Sembako',
            'plastik' => 'Pembelian Plastik',
            'buah' => 'Pembelian Buah',
            'gas' => 'Pembelian Gas',
            'vip' => 'Pembelian VIP',
            'stiker' => 'Pembelian Stiker',
        ];

        $debitPembelian = collect();
        $totalDebitPembelian = 0;

        foreach ($kategori as $key => $label) {
            $jumlah = \App\Models\Pembelian::where('jenis_pembelian', $key)
                ->where('jenis_transaksi', 'debit')
                ->whereMonth('tanggal', $bulanIni->month)
                ->whereYear('tanggal', $bulanIni->year)
                ->sum('jumlah');

            $debitPembelian->push(['nama' => $label, 'debit' => $jumlah, 'kredit' => null]);
            $totalDebitPembelian += $jumlah;
        }

        $kreditPembelian = \App\Models\Pembelian::where('jenis_transaksi', 'kredit')
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->select('transaksi as nama')
            ->selectRaw('SUM(jumlah) as kredit')
            ->groupBy('transaksi')
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->nama,
                    'debit' => null,
                    'kredit' => $item->kredit,
                ];
            });

        $rowsPembelian = $debitPembelian->merge($kreditPembelian)->toArray();
        $rowsPembelian[] = ['nama' => '', 'debit' => null, 'kredit' => null];
        $rowsPembelian[] = ['nama' => 'TOTAL', 'debit' => $totalDebitPembelian, 'kredit' => $kreditPembelian->sum('kredit')];

        // =========================
        // TRIAL BALANCE SALDO HUTANG
        // =========================
        $dataSaldo = \App\Models\SaldoHutangUmum::whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->get()
            ->groupBy('transaksi');

        $rowsSaldo = [];
        $totalDebitSaldo = 0;
        $totalKreditSaldo = 0;
        $totalHutang = 0;

        foreach ($dataSaldo as $nama => $items) {
            $totalDebit = $items->where('jenis_transaksi', 'debit')->sum('jumlah');
            $totalKredit = $items->where('jenis_transaksi', 'kredit')->sum('jumlah');

            $isHutang = str()->of(strtolower($nama))->contains('hutang');

            $hutang = null;
            if ($isHutang) {
                $kreditPembelian = \App\Models\Pembelian::where('transaksi', $nama)
                    ->where('jenis_transaksi', 'kredit')
                    ->whereMonth('tanggal', $bulanIni->month)
                    ->whereYear('tanggal', $bulanIni->year)
                    ->sum('jumlah');

                $hutang = ($totalKredit + $kreditPembelian) - $totalDebit;
                $totalHutang += $hutang;
            }

            $rowsSaldo[] = [
                'nama' => $nama,
                'debit' => $totalDebit ?: null,
                'kredit' => $totalKredit ?: null,
                'hutang' => $hutang ?: null,
            ];

            $totalDebitSaldo += $totalDebit;
            $totalKreditSaldo += $totalKredit;
        }

        // Sorting sesuai urutan prioritas
        $urutanPrioritas = [
            'BCA Pak Sigit',
            'Farras',
            'Pembelian Mei',
            'Hutang Bu Dwi',
            'Hutang Sendok',
            'Hutang Indo Telor',
            'Hutang Beras',
            'Hutang Daging',
            'Hutang Ayam',
            'Hutang Plastik',
            'Beban Ongkir',
            'Beban Perlengkapan',
            'Beban Konsumsi',
            'Beban Sampah',
        ];

        $mapSaldo = collect($rowsSaldo)->keyBy('nama');
        $sortedRowsSaldo = [];

        foreach ($urutanPrioritas as $nama) {
            if ($mapSaldo->has($nama)) {
                $sortedRowsSaldo[] = $mapSaldo->pull($nama);
            }
        }

        $sortedRowsSaldo = array_merge($sortedRowsSaldo, $mapSaldo->sortKeys()->values()->all());
        $sortedRowsSaldo[] = ['nama' => '', 'debit' => null, 'kredit' => null, 'hutang' => null];
        $sortedRowsSaldo[] = ['nama' => 'TOTAL', 'debit' => $totalDebitSaldo, 'kredit' => $totalKreditSaldo, 'hutang' => $totalHutang];

        $rowsSaldo = $sortedRowsSaldo;
    @endphp

    {{-- 💰 Saldo Farras --}}
    <div class="my-6 p-4 rounded-xl border bg-gradient-to-r from-green-50 to-green-100 dark:from-gray-800 dark:to-gray-900 shadow text-green-800 dark:text-green-200 font-semibold">
        💰 Saldo Farras per {{ $bulanIni->translatedFormat('F Y') }}:
        <span class="text-black dark:text-white">Rp {{ number_format($saldoFarras, 0, ',', '.') }}</span>
    </div>

    {{-- 📋 Laporan Hutang --}}
    <div class="mt-6 border rounded-md shadow-sm overflow-x-auto bg-white dark:bg-gray-900 w-full max-w-md mx-auto">
        <div class="bg-purple-200 dark:bg-gray-800 text-center font-semibold py-2 text-sm text-purple-900 dark:text-white rounded-t-md">
            📋 Laporan Hutang
        </div>
        <table class="w-full text-xs border border-gray-300 dark:border-gray-700 border-collapse table-fixed">
            <thead class="bg-gray-700 text-white">
                <tr>
                    <th class="px-2 py-1 border border-gray-300 dark:border-gray-600 text-left">Keterangan</th>
                    <th class="px-2 py-1 border border-gray-300 dark:border-gray-600 text-right">Hutang</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900">
                @php
                    $rowsHutang = collect($rowsSaldo)
                        ->filter(fn($row) => $row['hutang'] !== null && $row['hutang'] != 0)
                        ->map(fn($row) => [
                            'nama' => $row['nama'],
                            'hutang' => $row['hutang']
                        ])->values();
                @endphp

                @foreach ($rowsHutang as $row)
                    <tr class="hover:bg-yellow-50 dark:hover:bg-gray-800">
                        <td class="px-2 py-1 border border-gray-300 dark:border-gray-700 text-left">
                            {{ $row['nama'] }}
                        </td>
                        <td class="px-2 py-1 border border-gray-300 dark:border-gray-700 text-right text-blue-700">
                            Rp {{ number_format($row['hutang'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 mt-10">
        {{-- 📘 Trial Balance Pembelian --}}
        <div class="w-full lg:w-1/2 border rounded-xl shadow-md overflow-x-auto bg-white dark:bg-gray-900">
            <div class="bg-gradient-to-r from-yellow-300 to-yellow-200 dark:from-gray-700 dark:to-gray-800 text-center font-bold py-3 text-lg text-black dark:text-white rounded-t-xl">
                📘 Trial Balance Pembelian
            </div>
            <table class="w-full text-sm border border-gray-300 dark:border-gray-700 table-fixed border-collapse">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left">Keterangan</th>
                        <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Debit</th>
                        <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900">
                    @foreach ($rowsPembelian as $row)
                        <tr class="{{ $row['nama'] === 'TOTAL' ? 'bg-yellow-200 font-bold dark:bg-yellow-600/20' : 'hover:bg-yellow-50 dark:hover:bg-gray-800' }}">
                            <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-left">{{ $row['nama'] }}</td>
                            <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-green-700">
                                {{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-red-700">
                                {{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 📟 Trial Balance Saldo + Hutang Umum --}}
        <div class="w-full border rounded-xl shadow-lg bg-white dark:bg-gray-900">
            <div class="bg-gradient-to-r from-yellow-300 to-yellow-200 dark:from-gray-700 dark:to-gray-800 text-center font-bold py-3 text-lg text-black dark:text-white rounded-t-xl">
                📟 Trial Balance Saldo + Hutang Umum
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-300 dark:border-gray-700 table-fixed border-collapse">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left">Keterangan</th>
                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Debit</th>
                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Kredit</th>
                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-right">Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @foreach ($rowsSaldo as $row)
                            <tr class="{{ $row['nama'] === 'TOTAL' ? 'bg-yellow-200 font-bold dark:bg-yellow-600/20' : 'hover:bg-yellow-50 dark:hover:bg-gray-800' }}">
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-left">{{ $row['nama'] }}</td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-green-700">
                                    {{ $row['debit'] !== null ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-red-700">
                                    {{ $row['kredit'] !== null ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-right text-blue-700">
                                    {{ $row['hutang'] !== null ? 'Rp ' . number_format($row['hutang'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::page>
