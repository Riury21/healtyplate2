<?php

namespace App\Filament\Resources;

use App\Models\Pendapatan;
use App\Models\Menu;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\PendapatanResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Card;
use Filament\Tables\Columns\TextColumn;

class PendapatanResource extends Resource
{
    protected static ?string $model = Pendapatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Inputan';
    protected static ?string $modelLabel = 'Pendapatan';
    protected static ?string $pluralModelLabel = 'Pendapatan';

    private static function updateSemua(callable $get, callable $set)
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        $totalDiskon = 0;
        $sesiUnik = [];

        $jumlahPagi = 0;
        $jumlahSiang = 0;
        $jumlahSore = 0;
        $jumlahAllSesi = 0;

        foreach ($items as $item) {
            $jumlah = (int) ($item['jumlah'] ?? 0);
            $harga = (int) ($item['harga_satuan'] ?? 0);
            $diskonPersen = (int) ($item['diskon'] ?? 0);
            $totalItem = $harga * $jumlah;
            $diskonItem = ($diskonPersen / 100) * $totalItem;

            $subtotal += $totalItem;
            $totalDiskon += $diskonItem;

            $sesi = strtolower($item['sesi'] ?? '');
            if (!empty($sesi)) {
                $sesiUnik[$sesi] = true;
                if ($sesi === 'pagi') $jumlahPagi += $jumlah;
                elseif ($sesi === 'siang') $jumlahSiang += $jumlah;
                elseif ($sesi === 'sore') $jumlahSore += $jumlah;
            }
        }

        $jumlahSesiUnik = count($sesiUnik);
        $ongkir = $get('pakai_ongkir') ? ((int) $get('ongkir') * $jumlahSesiUnik) : 0;
        $total = $subtotal + $ongkir - $totalDiskon;

        $set('jumlah_menu_pagi', $jumlahPagi);
        $set('jumlah_menu_siang', $jumlahSiang);
        $set('jumlah_menu_sore', $jumlahSore);
        $set('jumlah_all_sesi', $jumlahPagi + $jumlahSiang + $jumlahSore);
        $set('total_ongkir', (int) $ongkir);
        $set('total_diskon', (int) $totalDiskon);
        $set('total_pendapatan', (int) $total);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Forms\Components\Grid::make(3)->schema([
                    TextInput::make('nama')->label('Nama Pendapatan / Customer')->required(),
                    DatePicker::make('tanggal')->required(),
                    Textarea::make('keterangan')->label('Keterangan')->rows(3),
                ]),
            ]),

            Card::make()->schema([
                Repeater::make('items')->relationship('items')->label('Menu yang Dipesan')->schema([
                    Select::make('sesi')
                        ->options(['pagi' => 'Pagi', 'siang' => 'Siang', 'sore' => 'Sore'])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, $set, $get) => self::updateSemua($get, $set)),

                    Select::make('menu_id')
                        ->label('Menu')
                        ->relationship('menu', 'nama')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
->afterStateUpdated(function ($state, $set, $get) {
    $menu = Menu::find($state);
    $harga = $menu?->harga ?? 0;

    // Hanya set harga jika bukan sedang edit
    if (!$get('../../is_editing')) {
        $set('harga_satuan', $harga);
    }

    $jumlah = (int) $get('jumlah') ?: 1;
    $diskon = (int) $get('diskon') ?: 0;
    $totalSebelumDiskon = $harga * $jumlah;
    $diskonNominal = ($diskon / 100) * $totalSebelumDiskon;
    $set('total', $totalSebelumDiskon - $diskonNominal);

    self::updateSemua($get, $set);
}),


                    TextInput::make('jumlah')
                        ->numeric()
                        ->default(1)
                        ->reactive()
                        ->debounce(500)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $harga = (int) $get('harga_satuan') ?: 0;
                            $diskon = (int) $get('diskon') ?: 0;
                            $totalSebelumDiskon = $harga * (int) $state;
                            $diskonNominal = ($diskon / 100) * $totalSebelumDiskon;
                            $set('total', $totalSebelumDiskon - $diskonNominal);
                            self::updateSemua($get, $set);
                        }),

                    TextInput::make('harga_satuan')
                        ->label('Harga Satuan')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->reactive()
                        ->debounce(500)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $jumlah = (int) $get('jumlah') ?: 1;
                            $diskon = (int) $get('diskon') ?: 0;
                            $totalSebelumDiskon = $state * $jumlah;
                            $diskonNominal = ($diskon / 100) * $totalSebelumDiskon;
                            $set('total', $totalSebelumDiskon - $diskonNominal);
                            self::updateSemua($get, $set);
                        }),

                    TextInput::make('diskon')
                        ->label('Diskon (%)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100)
                        ->dehydrated()
                        ->reactive()
                        ->debounce(500)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if ($state > 100) {
                                $set('diskon', 100);
                                \Filament\Notifications\Notification::make()
                                    ->title('Diskon tidak boleh lebih dari 100%')
                                    ->warning()
                                    ->send();
                            }

                            $harga = (int) $get('harga_satuan') ?: 0;
                            $jumlah = (int) $get('jumlah') ?: 1;
                            $totalSebelumDiskon = $harga * $jumlah;
                            $diskonNominal = (min($state, 100) / 100) * $totalSebelumDiskon;
                            $set('total', $totalSebelumDiskon - $diskonNominal);

                            self::updateSemua($get, $set);
                        }),

                    TextInput::make('total')
                        ->numeric()
                        ->label('Total')
                        ->default(0)
                        ->required()
                        ->dehydrated(),
                ])->columns(6)->reactive()->afterStateUpdated(fn ($state, $set, $get) => self::updateSemua($get, $set)),
            ]),

            Card::make()->schema([
                Forms\Components\Grid::make(4)->schema([
                    TextInput::make('jumlah_menu_pagi')
                        ->label('Jumlah Menu Pagi')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('jumlah_menu_siang')
                        ->label('Jumlah Menu Siang')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('jumlah_menu_sore')
                        ->label('Jumlah Menu Sore')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('jumlah_all_sesi')
                        ->label('Jumlah Semua Sesi')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->dehydrated(false),
                ]),
            ]),

            Card::make()->schema([
                Checkbox::make('pakai_ongkir')
                    ->label('Pakai Ongkir')
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set, $get) => self::updateSemua($get, $set)),

                TextInput::make('ongkir')
                    ->label('Ongkir')
                    ->default(0)
                    ->numeric()
                    ->debounce(500)
                    ->reactive()
                    ->visible(fn ($get) => $get('pakai_ongkir'))
                    ->afterStateUpdated(fn ($state, $set, $get) => self::updateSemua($get, $set)),

                Forms\Components\Grid::make(3)->schema([
                    TextInput::make('total_ongkir')
                        ->label('Total Ongkir')
                        ->numeric()
                        ->default(0)
                        ->readOnly()
                        ->dehydrated(),

                    TextInput::make('total_diskon')
                        ->label('Total Diskon')
                        ->numeric()
                        ->default(0)
                        ->readOnly()
                        ->dehydrated(),

                    TextInput::make('total_pendapatan')
                        ->label('Total Pendapatan')
                        ->numeric()
                        ->default(0)
                        ->readOnly()
                        ->dehydrated(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('tanggal')->label('Tanggal')->date('d/m/Y'),
            TextColumn::make('nama')->label('Nama Customer')->searchable(),
            TextColumn::make('total_pendapatan')->label('Total Pendapatan')->money('IDR', true),
            TextColumn::make('keterangan')->label('Keterangan')->limit(50),
        ])->defaultSort('tanggal', 'desc')->actions([
            Tables\Actions\EditAction::make(),
        ])->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPendapatans::route('/'),
            'create' => Pages\CreatePendapatan::route('/create'),
            'edit'   => Pages\EditPendapatan::route('/{record}/edit'),
        ];
    }
}
