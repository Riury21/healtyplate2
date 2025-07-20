<x-filament::page>
    <h2 class="text-xl font-bold mb-4">Laporan Laba-Rugi</h2>

    {{-- Filter Bulan --}}
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

    {{-- Info Ringkas: Pajak, Laba, Pengembangan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mb-4 text-sm">
        <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
            <strong>Pajak UMKM (0,5%)</strong><br>
            Rp {{ number_format($pajakUmkm, 0, ',', '.') }}<br>
            <span class="text-xs text-gray-500">
                ({{ $totalSeluruh > 0 ? number_format(($pajakUmkm / $totalSeluruh) * 100, 2) . '%' : '0%' }})
            </span>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
            <strong>Laba Awal</strong><br>
            Rp {{ number_format($labaAwal, 0, ',', '.') }}<br>
            <span class="text-xs text-gray-500">
                ({{ $totalSeluruh > 0 ? number_format(($labaAwal / $totalSeluruh) * 100, 2) . '%' : '0%' }})
            </span>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
            <strong>Pengembangan (25%)</strong><br>
            Rp {{ number_format($pengembangan, 0, ',', '.') }}<br>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
            <strong>Charity (5%)</strong><br>
            Rp {{ number_format($charity, 0, ',', '.') }}<br>
        </div>

        <div class="bg-yellow-100 dark:bg-yellow-900 p-2 rounded font-semibold text-gray-900 dark:text-white">
            <strong>Laba Akhir</strong><br>
            Rp {{ number_format($labaAkhir, 0, ',', '.') }}<br>
            <span class="text-xs text-gray-500">
                ({{ $totalSeluruh > 0 ? number_format(($labaAkhir / $totalSeluruh) * 100, 2) . '%' : '0%' }})
            </span>
        </div>
    </div>

    {{-- Diagram Komposisi Laba --}}
    <h3 class="text-sm font-semibold mt-8 mb-2">Diagram Komposisi Laba</h3>
    <div class="max-w-xs mx-auto">
        <canvas id="diagramLabaPie" width="250" height="250"></canvas>
    </div>

    {{-- Tabel: Total Pendapatan --}}
    <h3 class="text-sm font-semibold mb-2">Total Pendapatan per Sumber Pendapatan</h3>
    <table class="min-w-full table-auto border border-gray-300 text-xs">
        <thead class="bg-gray-700 text-white">
            <tr>
                <th class="px-2 py-1 border">Nama Pendapatan</th>
                <th class="px-2 py-1 border text-right">Total</th>
                <th class="px-2 py-1 border text-center">%</th>
                <th class="px-2 py-1 border text-center">Total Pax</th>
                <th class="px-2 py-1 border text-center">Presentase Keuntungan</th>
                <th class="px-2 py-1 border text-center">Nilai Rupiah</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSemua = $totalSeluruh ?: 1; // Hindari pembagian 0
            @endphp
            @forelse ($pendapatans as $p)
                <tr>
                    <td class="px-2 py-1 border text-gray-800">{{ $p->nama }}</td>
                    <td class="px-2 py-1 border text-right text-gray-800">
                        Rp {{ number_format($p->total, 0, ',', '.') }}
                    </td>
                    <td class="px-2 py-1 border text-center text-gray-800">
                        {{ number_format(($p->total / $totalSeluruh) * 100, 2) }}%
                    </td>
                    <td class="px-2 py-1 border text-center">{{ $p->pax ?? 0 }}</td>

                    @php
                        $jumlahPax = $p->pax ?? 0;
                        $totalPendapatanBaris = $p->total ?? 0;
                        $presentasePax = ($totalPax ?? 0) > 0 ? ($jumlahPax / $totalPax) : 0;
                        $alokasiBeban = $presentasePax * ($totalBeban ?? 0);
                        $alokasiPembelian = $presentasePax * ($totalPembelian ?? 0);
                        $nilaiKeuntungan = $totalPendapatanBaris - ($alokasiPembelian + $alokasiBeban);
                        $presentaseKeuntungan = $totalPendapatanBaris > 0 ? ($nilaiKeuntungan / $totalPendapatanBaris) * 100 : 0;
                    @endphp

                    <td class="px-2 py-1 border text-center">
                        {{ number_format($presentaseKeuntungan, 2) }}%
                    </td>
                    <td class="px-2 py-1 border text-right text-gray-800">
                        Rp {{ number_format($nilaiKeuntungan, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-2 py-1 border text-center text-gray-500">
                        Tidak ada data
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-yellow-100 text-black font-semibold">
                <td class="px-2 py-1 border text-right">Jumlah</td>
                <td class="px-2 py-1 border text-right">
                    Rp {{ number_format($totalSeluruh, 0, ',', '.') }}
                </td>
                <td class="px-2 py-1 border text-center">100%</td>
                <td class="px-2 py-1 border text-center">{{ $totalPax }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Tabel: Total Pembelian --}}
    <h3 class="text-sm font-semibold mt-8 mb-2">Total Pembelian per Jenis Pembelian</h3>
    <table class="min-w-full table-auto border border-gray-300 text-xs">
        <thead class="bg-gray-700 text-white">
            <tr>
                <th class="px-2 py-1 border">Jenis Pembelian</th>
                <th class="px-2 py-1 border text-right">Total</th>
                <th class="px-2 py-1 border text-center">%</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPembelianAll = $totalPembelian ?: 1;
            @endphp
            @forelse ($pembelianPerJenis as $p)
                <tr>
                    <td class="px-2 py-1 border text-gray-800">{{ $p->jenis_pembelian }}</td>
                    <td class="px-2 py-1 border text-right text-gray-800">
                        Rp {{ number_format($p->total, 0, ',', '.') }}
                    </td>
                    <td class="px-2 py-1 border text-center text-gray-800">
                        {{ number_format(($p->total / $totalSeluruh) * 100, 2) }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-2 py-1 border text-center text-gray-500">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-yellow-100 text-black font-semibold">
                <td class="px-2 py-1 border text-right">Jumlah</td>
                <td class="px-2 py-1 border text-right">
                    Rp {{ number_format($totalPembelian, 0, ',', '.') }}
                </td>
                <td class="px-2 py-1 border text-center">
                    {{ $totalSeluruh > 0 ? number_format(($totalPembelian / $totalSeluruh) * 100, 2) . '%' : '0%' }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Tabel: Beban --}}
    <h3 class="text-sm font-semibold mt-8 mb-2">Daftar Beban</h3>
    <table class="min-w-full table-auto border border-gray-300 text-xs">
        <thead class="bg-gray-700 text-white">
            <tr>
                <th class="px-2 py-1 border">Transaksi</th>
                <th class="px-2 py-1 border text-right">Total</th>
                <th class="px-2 py-1 border text-center">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bebanGabungan as $b)
                <tr>
                    <td class="px-2 py-1 border text-gray-800">{{ $b->transaksi }}</td>
                    <td class="px-2 py-1 border text-right text-gray-800">
                        Rp {{ number_format($b->total, 0, ',', '.') }}
                    </td>
                    <td class="px-2 py-1 border text-center text-gray-800">
                        {{ $totalSeluruh > 0 ? number_format(($b->total / $totalSeluruh) * 100, 2) . '%' : '0%' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-yellow-100 text-black font-semibold">
                <td class="px-2 py-1 border text-right">Jumlah</td>
                <td class="px-2 py-1 border text-right">
                    Rp {{ number_format($totalBeban, 0, ',', '.') }}
                </td>
                <td class="px-2 py-1 border text-center">
                    {{ $totalSeluruh > 0 ? number_format(($totalBeban / $totalSeluruh) * 100, 2) . '%' : '0%' }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Script Chart.js untuk Diagram Laba --}}
    <canvas id="diagramLabaPie" width="400" height="400" class="my-6"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('diagramLabaPie').getContext('2d');

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Laba Awal', 'Total Beban', 'Pajak UMKM'],
                datasets: [{
                    label: 'Komposisi Laba',
                    data: [
                        {{ $totalSeluruh > 0 ? round(($labaAwal / $totalSeluruh) * 100, 1) : 0 }},
                        {{ $totalSeluruh > 0 ? round(($totalBeban / $totalSeluruh) * 100, 1) : 0 }},
                        {{ $totalSeluruh > 0 ? round(($pajakUmkm / $totalSeluruh) * 100, 1) : 0 }},
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.7)',   // Laba - Hijau
                        'rgba(239, 68, 68, 0.7)',   // Beban - Merah
                        'rgba(251, 191, 36, 0.7)'   // Pajak - Kuning
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(251, 191, 36, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-filament::page>
