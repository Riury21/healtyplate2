<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Models\Menu;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\MenuResource\Pages;


class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Menu Makanan';
    protected static ?string $navigationGroup = 'Inputan';
    protected static ?string $pluralModelLabel = 'Daftar Menu';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->required()
                ->label('Nama Menu')
                ->maxLength(255),

            Forms\Components\TextInput::make('harga')
                ->required()
                ->label('Harga (Rp)')
                ->numeric()
                ->prefix('Rp'),

            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3)
                ->placeholder('Contoh: hanya tersedia di hari Senin.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR', true)
                    ->label('Harga'),
                Tables\Columns\TextColumn::make('keterangan')->limit(40)->wrap(),
            ])
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }

}

