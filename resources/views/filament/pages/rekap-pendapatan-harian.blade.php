<x-filament::page>
    <div class="mb-4">
        <form method="GET" class="flex items-center gap-2">
            <label for="bulan" class="text-sm font-semibold">Pilih Bulan:</label>
            <input
                type="month"
                id="bulan"
                name="bulan"
                value="{{ request('bulan') ?? now()->format('Y-m') }}"
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
        // Ambil bulan dari request atau default bulan ini
        $bulan = request('bulan') ?? now()->format('Y-m');
        $tanggal = \Carbon\Carbon::parse($bulan);

        // Filter data sesuai bulan (asumsikan data dari controller)
        $rekap = collect($rekap)
            ->filter(function ($item) use ($tanggal) {
                $itemDate = \Carbon\Carbon::parse($item['tanggal']);
                return $itemDate->month === $tanggal->month && $itemDate->year === $tanggal->year;
            })
            ->values();

        // Ambil semua instansi setelah data difilter
        $allInstansi = $rekap->flatMap(fn($r) => array_keys($r['instansi'] ?? []))->unique()->values();
    @endphp

    <div class="overflow-auto">
        <table class="min-w-full table-auto border">
            <thead class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white">
                <tr>
                    <th class="border px-4 py-2">Tanggal</th>
                    @foreach ($allInstansi as $instansi)
                        <th class="border px-2 py-1 text-center" colspan="2">{{ $instansi }}</th>
                    @endforeach
                    <th class="border px-2 py-1 text-center bg-yellow-100" colspan="3">TOTAL</th>
                </tr>
                <tr>
                    <th class="border px-4 py-2"></th>
                    @foreach ($allInstansi as $instansi)
                        <th class="border px-2 py-1">Pendapatan</th>
                        <th class="border px-2 py-1">Jumlah</th>
                    @endforeach
                    <th class="border px-2 py-1 bg-yellow-50">Pendapatan</th>
                    <th class="border px-2 py-1 bg-yellow-50">Jumlah</th>
                    <th class="border px-2 py-1 bg-yellow-50">Sesi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekap as $baris)
                    <tr>
                        <td class="border px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($baris['tanggal'])->format('j/n/Y') }}</td>

                        @foreach ($allInstansi as $instansi)
                            @php
                                $data = $baris['instansi'][$instansi] ?? ['pendapatan' => 0, 'jumlah' => 0];
                            @endphp
                            <td class="border px-2 py-1 text-right">Rp {{ number_format($data['pendapatan'], 0, ',', '.') }}</td>
                            <td class="border px-2 py-1 text-center">{{ $data['jumlah'] }}</td>
                        @endforeach

                        <td class="border px-2 py-1 text-right bg-yellow-50 font-semibold">
                            Rp {{ number_format($baris['total_pendapatan'], 0, ',', '.') }}
                        </td>
                        <td class="border px-2 py-1 text-center bg-yellow-50 font-semibold">{{ $baris['total_jumlah'] }}</td>
                        <td class="border px-2 py-1 text-center bg-yellow-50 font-semibold">{{ $baris['total_sesi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white">
                    <td class="border px-2 py-1 text-right">Total</td>

                    @foreach ($allInstansi as $instansi)
                        @php
                            $totalPerInstansi = [
                                'pendapatan' => $rekap->sum(fn($r) => $r['instansi'][$instansi]['pendapatan'] ?? 0),
                                'jumlah' => $rekap->sum(fn($r) => $r['instansi'][$instansi]['jumlah'] ?? 0),
                            ];
                        @endphp
                        <td class="border px-2 py-1 text-right">
                            Rp {{ number_format($totalPerInstansi['pendapatan'], 0, ',', '.') }}
                        </td>
                        <td class="border px-2 py-1 text-center">
                            {{ $totalPerInstansi['jumlah'] }}
                        </td>
                    @endforeach

                    <td class="border px-2 py-1 text-right bg-yellow-200">
                        Rp {{ number_format($rekap->sum('total_pendapatan'), 0, ',', '.') }}
                    </td>
                    <td class="border px-2 py-1 text-center bg-yellow-200">
                        {{ $rekap->sum('total_jumlah') }}
                    </td>
                    <td class="border px-2 py-1 text-center bg-yellow-200">
                        {{ $rekap->sum('total_sesi') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament::page>
