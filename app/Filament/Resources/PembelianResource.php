<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\BadgeColumn;


class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Inputan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),

                Select::make('jenis_pembelian')
                    ->label('Jenis Pembelian')
                    ->options([
                        'harian' => 'Harian',
                        'bahan utama' => 'Bahan Utama',
                        'sembako' => 'Sembako',
                        'buah' => 'Buah',
                        'plastik' => 'Plastik',
                        'gas' => 'Gas',
                        'vip' => 'VIP',
                        'stiker' => 'Stiker',
                    ])
                    ->required(),

                TextInput::make('transaksi')
                    ->label('Nama Transaksi')
                    ->datalist([
                        'Farras',
                        'Hutang Ayam',
                        'Hutang Bu Dwi',
                        'Hutang Daging',
                        'Hutang Beras',
                        'Hutang Indo Telor',
                        'Hutang Sendok',
                        'Hutang Plastik',
                    ])
                    ->required(),

                Select::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'debit' => 'Debit',
                        'kredit' => 'Kredit',
                    ])
                    ->required(),

                TextInput::make('kuantiti')
                    ->label('Kuantiti')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('jumlah')
                    ->label('Jumlah Harga')
                    ->numeric()
                    ->required(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('d/m/Y'),
                TextColumn::make('jenis_pembelian'),
                TextColumn::make('transaksi'),
                BadgeColumn::make('jenis_transaksi')
                    ->colors([
                        'primary' => 'debit',
                        'danger' => 'kredit',
                    ]),
                TextColumn::make('kuantiti'),
                TextColumn::make('jumlah')->money('IDR'),
                TextColumn::make('keterangan')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->keterangan ?: 'Tidak ada keterangan'),
            ])
            ->defaultSort('tanggal', 'desc') // ⬅️ default: tanggal terbaru dulu
            ->modifyQueryUsing(function ($query) {
                // ⬅️ urutan jenis pembelian dari harian ke stiker
                $query->orderByRaw("
                    FIELD(jenis_pembelian, 
                        'harian', 
                        'bahan utama', 
                        'sembako', 
                        'buah', 
                        'plastik', 
                        'gas', 
                        'vip', 
                        'stiker'
                    )
                ");

                // ⬅️ urutan debit dulu baru kredit
                $query->orderByRaw("FIELD(jenis_transaksi, 'debit', 'kredit')");
            })
            ->filters([
                // 🔎 Filter Pencarian Transaksi
                Tables\Filters\Filter::make('search_transaksi')
                    ->label('Cari Transaksi')
                    ->form([
                        TextInput::make('keyword')->label('Nama Transaksi'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['keyword'], fn ($q, $keyword) =>
                            $q->where('transaksi', 'like', '%' . $keyword . '%')
                        );
                    })
                    ->indicateUsing(function (array $data) {
                        return $data['keyword']
                            ? ['Cari: ' . $data['keyword']]
                            : [];
                    }),

                // 🔹 Filter Harian
                Filter::make('tanggal_hari')
                    ->label('Tanggal (Harian)')
                    ->form([
                        DatePicker::make('tanggal_hari')
                            ->label('Tanggal')
                            ->withoutTime(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_hari'], fn($q) =>
                                $q->whereDate('tanggal', $data['tanggal_hari'])
                            );
                    })
                    ->indicateUsing(function (array $data) {
                        return !empty($data['tanggal_hari'])
                            ? ['Tanggal: ' . \Carbon\Carbon::parse($data['tanggal_hari'])->format('d M Y')]
                            : [];
                    }),

                // 🔹 Filter Bulanan
                Filter::make('tanggal_bulan')
                    ->label('Tanggal (Bulanan)')
                    ->form([
                        DatePicker::make('tanggal_bulan')
                            ->label('Bulan')
                            ->displayFormat('F Y')
                            ->withoutTime(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_bulan'], function ($q) use ($data) {
                                $carbon = \Carbon\Carbon::parse($data['tanggal_bulan']);
                                return $q->whereMonth('tanggal', $carbon->month)
                                        ->whereYear('tanggal', $carbon->year);
                            });
                    })
                    ->indicateUsing(function (array $data) {
                        return !empty($data['tanggal_bulan'])
                            ? ['Bulan: ' . \Carbon\Carbon::parse($data['tanggal_bulan'])->format('F Y')]
                            : [];
                    }),

                // 🔹 Filter Jenis Pembelian
                Tables\Filters\SelectFilter::make('jenis_pembelian')
                    ->label('Jenis Pembelian')
                    ->options([
                        'harian' => 'Harian',
                        'bahan utama' => 'Bahan Utama',
                        'sembako' => 'Sembako',
                        'buah' => 'Buah',
                        'plastik' => 'Plastik',
                        'gas' => 'Gas',
                        'vip' => 'VIP',
                        'stiker' => 'Stiker',
                    ])
                    ->placeholder('Semua'),

                // 🔹 Filter Jenis Transaksi
                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'debit' => '💸 Debit',
                        'kredit' => '💰 Kredit',
                    ])
                    ->placeholder('Semua'),
            ])
            ->actions([Tables\Actions\EditAction::make(),])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make(),])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(function ($query) {
                return $query->orderBy('tanggal', 'desc')
                            ->orderByRaw("CASE jenis_transaksi WHEN 'debit' THEN 0 WHEN 'kredit' THEN 1 ELSE 2 END");
            });
        }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}
