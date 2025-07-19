<x-filament::page>
    <div class="mb-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label for="jenis" class="text-sm font-semibold">Jenis Pembelian:</label>
                <select
                    name="jenis"
                    id="jenis"
                    class="border border-gray-300 rounded px-3 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800"
                >
                    <option value="">Semua</option>
                    @foreach (['harian','bahan utama','sembako','plastik','buah','gas','vip','stiker'] as $item)
                        <option value="{{ $item }}" @selected(request('jenis') === $item)>
                            {{ ucfirst($item) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tanggal_dari" class="text-sm font-semibold">Dari Tanggal:</label>
                <input type="date" id="tanggal_dari" name="tanggal_dari"
                    value="{{ request('tanggal_dari') }}"
                    class="border border-gray-300 rounded px-3 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800"
                >
            </div>

            <div>
                <label for="tanggal_sampai" class="text-sm font-semibold">Sampai Tanggal:</label>
                <input type="date" id="tanggal_sampai" name="tanggal_sampai"
                    value="{{ request('tanggal_sampai') }}"
                    class="border border-gray-300 rounded px-3 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800"
                >
            </div>

            <div>
                <label for="cari" class="text-sm font-semibold">Nama Transaksi:</label>
                <input type="text" id="cari" name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Contoh: Brokoli, Ayam, Beras"
                    class="border border-gray-300 rounded px-3 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800"
                >
            </div>

            <div class="mb-0 p-0 border rounded bg-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white px-4 py-1 rounded text-sm hover:opacity-90 transition"
                    >
                        Tampilkan
                    </button>

                    <a
                        href="{{ route('filament.admin.pages.kontrol-pembelian') }}"
                        class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white px-4 py-1 rounded text-sm hover:opacity-90 transition"
                    >
                        Reset
                    </a>
                </form>
            </div>

        </form>
    </div>

    @php
        $query = \App\Models\Pembelian::query();
        $query->where('jenis_transaksi', 'debit');
        
        if (request('jenis')) {
            $query->where('jenis_pembelian', request('jenis'));
        }

        if (request('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', request('tanggal_dari'));
        }

        if (request('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', request('tanggal_sampai'));
        }

        if (request('cari')) {
            $query->where('transaksi', 'like', '%' . request('cari') . '%');
        }

        $data = $query
            ->selectRaw('transaksi, SUM(kuantiti) as total_kuantiti, SUM(jumlah) as total_jumlah')
            ->groupBy('transaksi')
            ->get()
            ->map(function ($item) {
                return [
                    'transaksi' => $item->transaksi,
                    'kuantiti' => $item->total_kuantiti,
                    'jumlah' => $item->total_jumlah,
                    'harga_dasar' => $item->total_kuantiti > 0
                        ? $item->total_jumlah / $item->total_kuantiti
                        : 0,
                ];
            });
    @endphp

    <div class="overflow-x-auto border rounded">
        <table class="min-w-full text-sm border">
            <thead class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white">
                <tr>
                    <th class="px-4 py-2 border text-left">Nama Transaksi</th>
                    <th class="px-4 py-2 border text-right">Kuantitas</th>
                    <th class="px-4 py-2 border text-right">Harga Dasar</th>
                    <th class="px-4 py-2 border text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr class="hover:bg-yellow-50">
                        <td class="px-4 py-1 border text-left">{{ $row['transaksi'] }}</td>
                        <td class="px-4 py-1 border text-right">{{ $row['kuantiti'] }}</td>
                        <td class="px-4 py-1 border text-right">Rp {{ number_format($row['harga_dasar'], 0, ',', '.') }}</td>
                        <td class="px-4 py-1 border text-right">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-2">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::page>
