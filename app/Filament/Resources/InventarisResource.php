<?php

namespace App\Filament\Resources;

use App\Models\Inventaris;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\InventarisResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Tables\Columns\TextColumn;

class InventarisResource extends Resource
{
    protected static ?string $model = Inventaris::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Inventaris';
    protected static ?string $pluralModelLabel = 'Inventaris';
    protected static ?string $navigationGroup = 'Inputan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->required(),

                TextInput::make('jumlah')
                    ->label('Jumlah Barang')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total_nilai', (int)$state * (int)$get('nilai_barang'));
                    }),

            TextInput::make('nilai_barang')
                ->label('Nilai per Barang')
                ->numeric()
                ->required()
                ->reactive()
                ->debounce(750) // ➕ Tambah waktu debounce sedikit agar lebih aman
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (is_numeric($state)) {
                        $jumlah = (int) $get('jumlah') ?: 0;
                        $set('total_nilai', (int)$state * $jumlah);
                    }
                }),

                TextInput::make('total_nilai')
                    ->label('Total Nilai')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                TextInput::make('tahun_pembelian')
                    ->label('Tahun Pembelian')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_barang')->label('Nama Barang')->searchable(),
                TextColumn::make('jumlah')->label('Jumlah'),
                TextColumn::make('nilai_barang')->label('Nilai Satuan')->money('IDR'),
                TextColumn::make('total_nilai')->label('Total Nilai')->money('IDR'),
                TextColumn::make('tahun_pembelian')->label('Tahun Pembelian'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventaris::route('/'),
            'create' => Pages\CreateInventaris::route('/create'),
            'edit' => Pages\EditInventaris::route('/{record}/edit'),
        ];
    }
}
