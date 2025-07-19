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
    <div class="my-4 p-4 rounded border bg-yellow-100 text-yellow-900 font-semibold">
        💰 Saldo Farras per {{ $bulanIni->translatedFormat('F Y') }}:
        <span class="text-black">Rp {{ number_format($saldoFarras, 0, ',', '.') }}</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- 📘 Trial Balance Pembelian --}}
        <div class="w-full lg:w-1/2 border rounded overflow-x-auto">
            <div class="bg-yellow-300 text-center font-bold py-2">Trial Balance Pembelian</div>
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 border text-left">Keterangan</th>
                        <th class="px-4 py-2 border text-right">Debit</th>
                        <th class="px-4 py-2 border text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rowsPembelian as $row)
                        <tr class="{{ $row['nama'] === 'TOTAL' ? 'bg-yellow-200 font-bold' : 'hover:bg-purple-100' }}">
                            <td class="px-4 py-1 border text-left">{{ $row['nama'] }}</td>
                            <td class="px-4 py-1 border text-right text-green-700">
                                {{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-1 border text-right text-red-700">
                                {{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 📟 Trial Balance Saldo Hutang Umum --}}
        <div class="w-full lg:w-1/2 border rounded overflow-x-auto">
            <div class="bg-yellow-300 text-center font-bold py-2">Trial Balance Saldo + Hutang Umum</div>
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 border text-left">Keterangan</th>
                        <th class="px-4 py-2 border text-right">Debit</th>
                        <th class="px-4 py-2 border text-right">Kredit</th>
                        <th class="px-4 py-2 border text-right">Hutang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rowsSaldo as $row)
                        <tr class="{{ $row['nama'] === 'TOTAL' ? 'bg-yellow-200 font-bold' : 'hover:bg-purple-100' }}">
                            <td class="px-4 py-1 border text-left">{{ $row['nama'] }}</td>
                            <td class="px-4 py-1 border text-right text-green-700">
                                {{ $row['debit'] !== null ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-1 border text-right text-red-700">
                                {{ $row['kredit'] !== null ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-1 border text-right text-blue-700">
                                {{ $row['hutang'] !== null ? 'Rp ' . number_format($row['hutang'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>
    </div>
</x-filament::page>
