<x-filament::page>
    <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
        📘 Manual Book - Laporan Keuangan Healthy Plate YK
    </h1>

    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        Berikut adalah istilah dan keterangan yang digunakan dalam laporan keuangan:
    </p>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-gray-300 dark:border-gray-700">
            <thead class="bg-yellow-400 text-black dark:bg-gray-700 dark:text-white">
                <tr>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Istilah</th>
                    <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $data = [
                        ['Debit', 'Uang/ Persediaan/ Hutang yang masuk'],
                        ['Kredit', 'Uang/ Persediaan/ Hutang yang keluar atau dibayar'],
                        ['Saldo', 'Jumlah uang yang ada di kas'],
                        ['Hutang', 'Jumlah uang yang harus dibayar'],
                        ['Umum', 'Jenis transaksi umum yang tidak masuk kategori khusus'],
                        ['Beban', 'Pengeluaran untuk operasional usaha'],
                        ['Pendapatan', 'Pemasukan dari penjualan atau layanan'],
                        ['Laba Rugi', 'Selisih antara pendapatan dan beban'],
                        ['Neraca', 'Laporan posisi keuangan pada suatu waktu tertentu'],
                        ['Trial Balance', 'Untuk melihat apakah Debit maupun Kredit sudah sesuai pada pos nya atau belum'],
                        ['Pembelian Harian', 'Sayur, Tempe, Galon'],
                        ['Pembelian Bahan Utama', 'Ayam, Daging, Beras, Telur, Ikan'],
                        ['Pembelian Sembako', 'Sembako'],
                        ['Pembelian Plastik', 'Plastik dan Packaging'],
                        ['Pembelian Buah', 'Buah'],
                        ['Pembelian Gas', 'Gas'],
                        ['Pembelian VIP', 'Galantin, Rolade dll'],
                        ['Pembelian Stiker', 'Stiker dan Print'],
                        ['Beban Ongkir', 'Biaya Ongkos kirim menggunakan Aplikasi Jasa Kirim'],
                        ['Beban Perlengkapan', 'Biaya Pembelian Alat ataupun Perlengkapan' ],
                        ['Beban Listrik', 'Biaya untuk listrik'],
                        ['Beban Air', 'Biaya untuk air'],
                        ['Beban Gaji', 'Biaya untuk gaji karyawan'],
                        ['Beban Pajak', 'Biaya untuk pajak'],
                        ['Beban Perawatan', 'Biaya untuk perawatan alat dan perlengkapan'],
                        ['Beban Promosi', 'Biaya untuk promosi usaha'],
                        ['Beban Transportasi', 'Biaya transportasi karyawan'],
                        ['Beban Konsumsi', 'Biaya untuk konsumsi rapat dll'],
                        ['Beban Sampah', 'Biaya untuk sampah'],
                    ];
                @endphp

                @foreach ($data as [$istilah, $keterangan])
                    <tr class="hover:bg-yellow-100 dark:hover:bg-yellow-900">
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-100">
                            {{ $istilah }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                            {{ $keterangan }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament::page>
