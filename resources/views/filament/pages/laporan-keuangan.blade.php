<x-filament::page>
    <h2 class="text-xl font-bold mb-4">Laporan Laba-Rugi</h2>

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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mb-4 text-sm">
    <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
        <strong>Pajak UMKM (0,5%)</strong><br>
        Rp {{ number_format($pajakUmkm, 0, ',', '.') }}
    </div>
    <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
        <strong>Laba Awal</strong><br>
        Rp {{ number_format($labaAwal, 0, ',', '.') }}
    </div>
    <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
        <strong>Pengembangan (25%)</strong><br>
        Rp {{ number_format($pengembangan, 0, ',', '.') }}
    </div>
    <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded text-gray-900 dark:text-white">
        <strong>Charity (5%)</strong><br>
        Rp {{ number_format($charity, 0, ',', '.') }}
    </div>
    <div class="bg-yellow-100 dark:bg-yellow-900 p-2 rounded font-semibold text-gray-900 dark:text-white">
        <strong>Laba Akhir</strong><br>
        Rp {{ number_format($labaAkhir, 0, ',', '.') }}
    </div>
</div>


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
                    {{ number_format(($p->total / $totalSeluruh) * 100, 1) }}%
                </td>
                <td class="px-2 py-1 border text-center">{{ $p->pax ?? 0 }}</td>
                <td class="px-2 py-1 border text-center">
                    -
                </td>
                <td class="px-2 py-1 border text-center">
                    -
                </td>

            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-2 py-1 border text-center text-gray-500">
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
            <td class="px-2 py-1 border text-center">
                {{ $totalPax }}
            </td>
        </tr>
    </tfoot>
</table>

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
            $totalPembelianAll = $totalPembelian ?: 1; // Hindari pembagian nol
        @endphp
        @forelse ($pembelianPerJenis as $p)
            <tr>
                <td class="px-2 py-1 border text-gray-800">{{ $p->jenis_pembelian }}</td>
                <td class="px-2 py-1 border text-right text-gray-800">
                    Rp {{ number_format($p->total, 0, ',', '.') }}
                </td>
                <td class="px-2 py-1 border text-center text-gray-800">
                    {{ number_format(($p->total / $totalPembelianAll) * 100, 1) }}%
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
            <td class="px-2 py-1 border text-center">100%</td>
        </tr>
    </tfoot>
</table>

<h3 class="text-sm font-semibold mt-8 mb-2">Daftar Beban</h3>

<table class="min-w-full table-auto border border-gray-300 text-xs">
    <thead class="bg-gray-700 text-white">
        <tr>
            <th class="px-2 py-1 border">Transaksi</th>
            <th class="px-2 py-1 border text-right">Jumlah</th>
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
                    {{ number_format($b->persen, 2) }}%
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
            <td class="px-2 py-1 border text-center">100%</td>
        </tr>
    </tfoot>
</table>

</x-filament::page>
