<x-filament::page>
    <div class="mb-4">
        <form method="GET" class="flex items-center gap-2 mb-4">
            <label for="bulan" class="text-sm font-semibold">Pilih Bulan:</label>
            <input
                type="month"
                id="bulan"
                name="bulan"
                value="{{ request('bulan') ?? now()->format('Y-m') }}"
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
    </div>

    @php
        $bulan = request('bulan') ?? now()->format('Y-m');
        $pembelian = \App\Models\Pembelian::query()
            ->where('jenis_transaksi', 'debit'); // ⬅️ tambahkan ini

        if ($bulan) {
            $tanggal = \Carbon\Carbon::parse($bulan);
            $pembelian->whereMonth('tanggal', $tanggal->month)
                    ->whereYear('tanggal', $tanggal->year);
        }

        $data = $pembelian->get()->groupBy('tanggal');

        $kategori = [
            'harian' => 'Pembelian Harian',
            'bahan utama' => 'Pembelian Bahan Utama',
            'sembako' => 'Pembelian Sembako',
            'buah' => 'Pembelian Buah',
            'plastik' => 'Pembelian Plastik',
            'gas' => 'Pembelian Gas',
            'vip' => 'Pembelian VIP',
            'stiker' => 'Pembelian Stiker',
        ];

        $subtotal = array_fill_keys(array_keys($kategori), 0);
        $subtotal['total'] = 0;
    @endphp


    <div class="overflow-x-auto rounded-lg border">
        <table class="min-w-full text-sm border">
            <thead class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white">
                <tr>
                    <th class="px-4 py-2 border">Tanggal</th>
                    @foreach ($kategori as $key => $label)
                        <th class="px-4 py-2 border">{{ $label }}</th>
                    @endforeach
                    <th class="px-4 py-2 border font-bold">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $tanggal => $items)
                    @php
                        $harianTotal = array_fill_keys(array_keys($kategori), 0);
                        $totalHarian = 0;
                    @endphp
                    <tr>
                        <td class="px-4 py-1 border text-nowrap">{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td>

                        @foreach ($kategori as $jenis => $label)
                            @php
                                $jumlah = $items->where('jenis_pembelian', $jenis)->sum('jumlah');
                                $harianTotal[$jenis] = $jumlah;
                                $subtotal[$jenis] += $jumlah;
                                $totalHarian += $jumlah;
                            @endphp
                            <td class="px-4 py-1 border text-right">
                                {{ $jumlah ? 'Rp ' . number_format($jumlah, 0, ',', '.') : '-' }}
                            </td>
                        @endforeach

                        <td class="px-4 py-1 border text-right font-bold">
                            Rp {{ number_format($totalHarian, 0, ',', '.') }}
                        </td>
                        @php $subtotal['total'] += $totalHarian; @endphp
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($kategori) + 2 }}" class="text-center text-gray-500 py-3">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-yellow-100 font-semibold">
                <tr>
                    <td class="px-4 py-2 border text-center">Total</td>
                    @foreach ($kategori as $jenis => $label)
                        <td class="px-4 py-2 border text-right text-yellow-800">
                            Rp {{ number_format($subtotal[$jenis], 0, ',', '.') }}
                        </td>
                    @endforeach
                    <td class="px-4 py-2 border text-right bg-yellow-300 text-black">
                        Rp {{ number_format($subtotal['total'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament::page>
