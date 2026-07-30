<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsWidget extends BaseWidget
{
    // Tetap pertahankan urutannya
    protected static ?int $sort = 2; 

    // Opsional: Membuat widget melebar penuh agar chart terlihat elegan
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        
        // 1. Inisiasi Query Utama
        $query = LaporanUtama::query();

        // 2. Terapkan Aturan Role (TD lihat miliknya, Admin/Dev lihat semua)
        if ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $query->where('nama_petugas', $user->name);
        }

        // 3. Kalkulasi Data
        $totalLaporan = (clone $query)->count();
        
        $laporanBulanIni = (clone $query)
            ->whereYear('tanggal_tugas', Carbon::now()->year)
            ->whereMonth('tanggal_tugas', Carbon::now()->month)
            ->count();
            
        $laporanHariIni = (clone $query)
            ->whereDate('tanggal_tugas', Carbon::today())
            ->count();

        // 4. Render UI Widget ala SaaS (Dominan Biru/Cyan/Indigo)
        return [
            Stat::make('Total Laporan', $totalLaporan)
                ->description('Keseluruhan data tercatat')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary') // Biru Utama
                ->chart([3, 7, 4, 10, 8, 12, 15]), // Sparkline dummy ala SaaS

            Stat::make('Bulan Ini', $laporanBulanIni)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info') // Cyan / Biru Muda
                ->chart([1, 3, 2, 6, 4, 8, 10]),

            Stat::make('Hari Ini', $laporanHariIni)
                ->description(Carbon::now()->translatedFormat('d F Y'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('indigo') // Indigo / Biru Gelap
                ->chart([0, 1, 0, 2, 1, 3, $laporanHariIni > 0 ? 5 : 0]),
        ];
    }
}