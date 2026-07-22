<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PortalShift extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'Portal Shift';
    protected static ?string $title = 'Pilih Jadwal Siaran';
    protected static ?int $navigationSort = -1; // Angka minus agar letaknya paling atas di sidebar

    protected string $view = 'filament.pages.portal-shift';
}