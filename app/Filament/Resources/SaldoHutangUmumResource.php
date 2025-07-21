<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaldoHutangUmumResource\Pages;
use App\Models\SaldoHutangUmum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\FiltersLayout;


class SaldoHutangUmumResource extends Resource
{
    protected static ?string $model = SaldoHutangUmum::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Inputan';
    protected static ?string $navigationLabel = 'Saldo Hutang Umum';

    public static function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('tanggal')->required()->label('Tanggal'),
            TextInput::make('transaksi')
                ->label('Nama Transaksi')
                ->required()
                ->datalist([
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
                ]),
            Select::make('jenis_transaksi')
                ->options(['debit' => 'Debit', 'kredit' => 'Kredit'])
                ->required()
                ->label('Jenis Transaksi'),
            TextInput::make('jumlah')->required()->label('Jumlah'),
            Textarea::make('keterangan')->label('Keterangan')->rows(3),
        ]);
    }

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('tanggal')->date('d/m/Y'),
            TextColumn::make('transaksi'),
            BadgeColumn::make('jenis_transaksi')
                ->colors([
                    'primary' => 'debit',
                    'danger' => 'kredit',
                ]),
            TextColumn::make('jumlah')->money('idr'),
            TextColumn::make('keterangan')->limit(40)->wrap(),
        ])
        ->filters([
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

            Tables\Filters\Filter::make('tanggal_hari')
                ->label('Tanggal')
                ->form([
                    DatePicker::make('tanggal_hari')->label('Tanggal')->withoutTime(),
                ])
                ->query(function ($query, array $data) {
                    return $query->when($data['tanggal_hari'], fn($q) =>
                        $q->whereDate('tanggal', $data['tanggal_hari']));
                }),

            Tables\Filters\Filter::make('tanggal_bulan')
                ->label('Bulan')
                ->form([
                    DatePicker::make('tanggal_bulan')
                        ->label('Bulan')
                        ->displayFormat('F Y')
                        ->withoutTime(),
                ])
                ->query(function ($query, array $data) {
                    return $query->when($data['tanggal_bulan'], function ($q) use ($data) {
                        $carbon = \Carbon\Carbon::parse($data['tanggal_bulan']);
                        return $q->whereMonth('tanggal', $carbon->month)
                                 ->whereYear('tanggal', $carbon->year);
                    });
                }),

            Tables\Filters\SelectFilter::make('jenis_transaksi')
                ->label('Jenis Transaksi')
                ->options([
                    'debit' => '💸 Debit',
                    'kredit' => '💰 Kredit',
                ])
                ->placeholder('Semua'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->filtersLayout(FiltersLayout::AboveContent)
        ->defaultSort('tanggal', 'desc')
        ->modifyQueryUsing(function ($query) {
            return $query->orderBy('tanggal', 'desc')
                        ->orderByRaw("CASE jenis_transaksi WHEN 'debit' THEN 0 WHEN 'kredit' THEN 1 ELSE 2 END");
        });
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSaldoHutangUmums::route('/'),
            'create' => Pages\CreateSaldoHutangUmum::route('/create'),
            'edit' => Pages\EditSaldoHutangUmum::route('/{record}/edit'),
        ];
    }
}
