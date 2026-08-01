<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem; 

class TdPanelProvider extends PanelProvider
{
    
        public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('td')
            ->path('td')
            ->brandLogo(asset('logo-tvri.png'))
            ->favicon(asset('logo-tvri.png')) // Ganti dengan nama file SVG Abang di public
            ->brandLogoHeight('3rem')
            ->brandName('TD Report - TVRI Kalsel') // <--- Tambah nama aplikasi
            ->defaultThemeMode(ThemeMode::Dark)    // <--- Paksa mode gelap
            ->colors([
                'primary' => Color::hex('#3B82F6'), // <--- Ubah warna primer jadi biru
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\AnalyticsWidget::class,
                
            
            ])
            // --- TAMBAHKAN BLOK KODE INI UNTUK LINK EKSTERNAL ---
            ->navigationItems([
                NavigationItem::make('Rundown')
                    ->url('https://datastudio.google.com/u/0/reporting/88988b59-171c-41dd-85b9-ea29dce40337/page/p_a4l0p9bqzc?s=pVafbh6gEqw')
                    ->icon('heroicon-o-document-text')
                    ->group('Tautan Eksternal')
                    ->sort(10)
                    ->openUrlInNewTab(),

                NavigationItem::make('Jadwal Petugas TX')
                    ->url('https://shiftingtx-kalsel.lanjung-tvrikalsel.id')
                    ->icon('heroicon-o-calendar-days')
                    ->group('Tautan Eksternal')
                    ->sort(11)
                    ->openUrlInNewTab(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
