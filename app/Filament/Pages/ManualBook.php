<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManualBook extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.manual-book';

    protected static ?string $title = 'Manual Book';
}
