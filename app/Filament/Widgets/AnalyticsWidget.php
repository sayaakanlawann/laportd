<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsWidget extends BaseWidget
{
    // Taruh di urutan ke-2 (di bawah kartu shift)
    protected static ?int $sort = 2; 

    protected function getStats(): array
    {
        // Contoh: Menghitung total laporan di database
        $totalLaporan = LaporanUtama::count();

        return [
            Stat::make('Total Laporan TD', $totalLaporan)
                ->description('Seluruh riwayat siaran')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Sistem Status', 'Online')
                ->description('Server berjalan normal')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
        ];
    }
}