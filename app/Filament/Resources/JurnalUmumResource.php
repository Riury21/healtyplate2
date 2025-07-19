<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JurnalUmumResource\Pages;
use App\Filament\Resources\JurnalUmumResource\RelationManagers;
use App\Models\JurnalUmum;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;

class JurnalUmumResource extends Resource
{
    protected static ?string $model = JurnalUmum::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $pluralModelLabel = 'Jurnal Umum';
    protected static ?string $navigationGroup = 'Inputan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->required()
                ->label('Tanggal'),

            Forms\Components\Select::make('transaksi')
                ->label('Nama Transaksi')
                ->required()
                ->searchable()
                ->options([
                    // BCA
                    'BCA 484' => 'BCA 484',
                    'BCA Pak Sigit' => 'BCA Pak Sigit',

                    // Beban
                    'Beban Alat' => 'Beban Alat',
                    'Beban Entertain' => 'Beban Entertain',
                    'Beban Gaji' => 'Beban Gaji',
                    'Beban Listrik' => 'Beban Listrik',
                    'Beban Wifi' => 'Beban Wifi',

                    // Pendapatan
                    'Pendapatan April' => 'Pendapatan April',
                    'Pendapatan Mei' => 'Pendapatan Mei',
                    'Pendapatan Daily' => 'Pendapatan Daily',
                    'Pendapatan QL' => 'Pendapatan QL',
                    'Pendapatan UAD' => 'Pendapatan UAD',
                    'Pendapatan PKU Kotagede' => 'Pendapatan PKU Kotagede',
                    'Pendapatan Sedayu' => 'Pendapatan Sedayu',
                    'Pendapatan AVO' => 'Pendapatan AVO',
                    'Pendapatan Klinik Indira' => 'Pendapatan Klinik Indira',
                    'Pendapatan Siloam' => 'Pendapatan Siloam',

                    // Potongan & Pajak
                    'Potongan' => 'Potongan',
                    'Pph 23 atas jasa' => 'Pph 23 atas jasa',

                    // Piutang QL
                    'Piutang QL' => 'Piutang QL',
                    'Piutang QL Januari' => 'Piutang QL Januari',
                    'Piutang QL Februari' => 'Piutang QL Februari',
                    'Piutang QL Maret' => 'Piutang QL Maret',
                    'Piutang QL April' => 'Piutang QL April',
                    'Piutang QL Mei' => 'Piutang QL Mei',
                    'Piutang QL Juni' => 'Piutang QL Juni',
                    'Piutang QL Juli' => 'Piutang QL Juli',
                    'Piutang QL Agustus' => 'Piutang QL Agustus',
                    'Piutang QL September' => 'Piutang QL September',
                    'Piutang QL Oktober' => 'Piutang QL Oktober',
                    'Piutang QL November' => 'Piutang QL November',
                    'Piutang QL Desember' => 'Piutang QL Desember',

                    // Piutang UAD
                    'Piutang UAD' => 'Piutang UAD',
                    'Piutang UAD Januari' => 'Piutang UAD Januari',
                    'Piutang UAD Februari' => 'Piutang UAD Februari',
                    'Piutang UAD Maret' => 'Piutang UAD Maret',
                    'Piutang UAD April' => 'Piutang UAD April',
                    'Piutang UAD Mei' => 'Piutang UAD Mei',
                    'Piutang UAD Juni' => 'Piutang UAD Juni',
                    'Piutang UAD Juli' => 'Piutang UAD Juli',
                    'Piutang UAD Agustus' => 'Piutang UAD Agustus',
                    'Piutang UAD September' => 'Piutang UAD September',
                    'Piutang UAD Oktober' => 'Piutang UAD Oktober',
                    'Piutang UAD November' => 'Piutang UAD November',
                    'Piutang UAD Desember' => 'Piutang UAD Desember',

                    // Piutang PKU Kotagede
                    'Piutang PKU Kotagede' => 'Piutang PKU Kotagede',
                    'Piutang PKU Kotagede Januari' => 'Piutang PKU Kotagede Januari',
                    'Piutang PKU Kotagede Februari' => 'Piutang PKU Kotagede Februari',
                    'Piutang PKU Kotagede Maret' => 'Piutang PKU Kotagede Maret',
                    'Piutang PKU Kotagede April' => 'Piutang PKU Kotagede April',
                    'Piutang PKU Kotagede Mei' => 'Piutang PKU Kotagede Mei',
                    'Piutang PKU Kotagede Juni' => 'Piutang PKU Kotagede Juni',
                    'Piutang PKU Kotagede Juli' => 'Piutang PKU Kotagede Juli',
                    'Piutang PKU Kotagede Agustus' => 'Piutang PKU Kotagede Agustus',
                    'Piutang PKU Kotagede September' => 'Piutang PKU Kotagede September',
                    'Piutang PKU Kotagede Oktober' => 'Piutang PKU Kotagede Oktober',
                    'Piutang PKU Kotagede November' => 'Piutang PKU Kotagede November',
                    'Piutang PKU Kotagede Desember' => 'Piutang PKU Kotagede Desember',

                    // Piutang Sedayu
                    'Piutang Sedayu' => 'Piutang Sedayu',
                    'Piutang Sedayu Januari' => 'Piutang Sedayu Januari',
                    'Piutang Sedayu Februari' => 'Piutang Sedayu Februari',
                    'Piutang Sedayu Maret' => 'Piutang Sedayu Maret',
                    'Piutang Sedayu April' => 'Piutang Sedayu April',
                    'Piutang Sedayu Mei' => 'Piutang Sedayu Mei',
                    'Piutang Sedayu Juni' => 'Piutang Sedayu Juni',
                    'Piutang Sedayu Juli' => 'Piutang Sedayu Juli',
                    'Piutang Sedayu Agustus' => 'Piutang Sedayu Agustus',
                    'Piutang Sedayu September' => 'Piutang Sedayu September',
                    'Piutang Sedayu Oktober' => 'Piutang Sedayu Oktober',
                    'Piutang Sedayu November' => 'Piutang Sedayu November',
                    'Piutang Sedayu Desember' => 'Piutang Sedayu Desember',

                    // Piutang AVO
                    'Piutang AVO' => 'Piutang AVO',
                    'Piutang AVO Januari' => 'Piutang AVO Januari',
                    'Piutang AVO Februari' => 'Piutang AVO Februari',
                    'Piutang AVO Maret' => 'Piutang AVO Maret',
                    'Piutang AVO April' => 'Piutang AVO April',
                    'Piutang AVO Mei' => 'Piutang AVO Mei',
                    'Piutang AVO Juni' => 'Piutang AVO Juni',
                    'Piutang AVO Juli' => 'Piutang AVO Juli',
                    'Piutang AVO Agustus' => 'Piutang AVO Agustus',
                    'Piutang AVO September' => 'Piutang AVO September',
                    'Piutang AVO Oktober' => 'Piutang AVO Oktober',
                    'Piutang AVO November' => 'Piutang AVO November',
                    'Piutang AVO Desember' => 'Piutang AVO Desember',

                    // Piutang Klinik Indira
                    'Piutang Klinik Indira' => 'Piutang Klinik Indira',
                    'Piutang Klinik Indira Januari' => 'Piutang Klinik Indira Januari',
                    'Piutang Klinik Indira Februari' => 'Piutang Klinik Indira Februari',
                    'Piutang Klinik Indira Maret' => 'Piutang Klinik Indira Maret',
                    'Piutang Klinik Indira April' => 'Piutang Klinik Indira April',
                    'Piutang Klinik Indira Mei' => 'Piutang Klinik Indira Mei',
                    'Piutang Klinik Indira Juni' => 'Piutang Klinik Indira Juni',
                    'Piutang Klinik Indira Juli' => 'Piutang Klinik Indira Juli',
                    'Piutang Klinik Indira Agustus' => 'Piutang Klinik Indira Agustus',
                    'Piutang Klinik Indira September' => 'Piutang Klinik Indira September',
                    'Piutang Klinik Indira Oktober' => 'Piutang Klinik Indira Oktober',
                    'Piutang Klinik Indira November' => 'Piutang Klinik Indira November',
                    'Piutang Klinik Indira Desember' => 'Piutang Klinik Indira Desember',

                    // Piutang Siloam
                    'Piutang Siloam' => 'Piutang Siloam',
                    'Piutang Siloam Januari' => 'Piutang Siloam Januari',
                    'Piutang Siloam Februari' => 'Piutang Siloam Februari',
                    'Piutang Siloam Maret' => 'Piutang Siloam Maret',
                    'Piutang Siloam April' => 'Piutang Siloam April',
                    'Piutang Siloam Mei' => 'Piutang Siloam Mei',
                    'Piutang Siloam Juni' => 'Piutang Siloam Juni',
                    'Piutang Siloam Juli' => 'Piutang Siloam Juli',
                    'Piutang Siloam Agustus' => 'Piutang Siloam Agustus',
                    'Piutang Siloam September' => 'Piutang Siloam September',
                    'Piutang Siloam Oktober' => 'Piutang Siloam Oktober',
                    'Piutang Siloam November' => 'Piutang Siloam November',
                    'Piutang Siloam Desember' => 'Piutang Siloam Desember',
                ]),

            Forms\Components\Select::make('jenis_transaksi')
                ->options([
                    'debit' => 'Debit',
                    'kredit' => 'Kredit',
                ])
                ->required()
                ->label('Jenis Transaksi'),

            Forms\Components\TextInput::make('jumlah')
                ->label('Jumlah')
                ->required()
                ->numeric()
                ->prefix('Rp'),

            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tanggal')
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make('transaksi'),

            Tables\Columns\BadgeColumn::make('jenis_transaksi')
                ->colors([
                    'primary' => 'debit',
                    'danger' => 'kredit',
                ]),

            Tables\Columns\TextColumn::make('jumlah')
                ->money('IDR', true)
                ->label('Jumlah'),

            Tables\Columns\TextColumn::make('keterangan')->limit(30),
        ])
        ->defaultSort('tanggal', 'desc')
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
        ->filtersLayout(FiltersLayout::AboveContent)
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalUmums::route('/'),
            'create' => Pages\CreateJurnalUmum::route('/create'),
            'edit' => Pages\EditJurnalUmum::route('/{record}/edit'),
        ];
    }
}
