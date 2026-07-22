<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PortalShiftWidget extends Widget
{
    protected string $view = 'filament.widgets.portal-shift-widget';
    
    // Bikin widgetnya membentang full layar
    protected int | string | array $columnSpan = 'full';
    
    // Urutkan paling atas (Angka 1)
    protected static ?int $sort = 1; 
}