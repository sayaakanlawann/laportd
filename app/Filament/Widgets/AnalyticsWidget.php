<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama;
use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class AnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 2; 
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        $query = LaporanUtama::query();

        if ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $query->where('nama_petugas', $user->name);
        }

        $totalLaporan = (clone $query)->count();
        $laporanBulanIni = (clone $query)
            ->whereYear('tanggal_tugas', Carbon::now()->year)
            ->whereMonth('tanggal_tugas', Carbon::now()->month)
            ->count();
        $laporanHariIni = (clone $query)
            ->whereDate('tanggal_tugas', Carbon::today())
            ->count();

        // 🔥 CSS YANG SUDAH DIPERBAIKI (TIDAK MERUSAK LAYOUT) 🔥
        $cssSpinning = new HtmlString('
            <style>
                @keyframes putar-border { 100% { transform: rotate(360deg); } }
                
                .animasi-spin-border { 
                    position: relative; 
                    isolation: isolate; /* Mengunci tumpukan layer agar aman */
                    overflow: hidden !important; 
                    border: none !important; 
                }
                
                /* Gradient dipindah ke background paling belakang (z-index: -2) */
                .animasi-spin-border::before { 
                    content: ""; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; 
                    background: conic-gradient(transparent 65%, #1e3a8a 85%, #60a5fa 100%); 
                    animation: putar-border 2.5s linear infinite; 
                    opacity: 0; transition: opacity 0.4s; 
                    z-index: -2; pointer-events: none; 
                }
                .animasi-spin-border:hover::before { opacity: 1; }
                
                /* Masking warna putih/gelap di posisi tengah (z-index: -1) */
                .animasi-spin-border::after { 
                    content: ""; position: absolute; inset: 2px; 
                    background: #ffffff; border-radius: inherit; 
                    z-index: -1; pointer-events: none; 
                }
                .dark .animasi-spin-border::after { background: #18181b; }
            </style>
            Keseluruhan data tercatat
        ');

        return [
            Stat::make('Total Laporan', $totalLaporan)
                ->description($cssSpinning) 
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary') 
                ->chart([3, 7, 4, 10, 8, 12, 15])
                ->url(LaporanUtamaResource::getUrl('index')) 
                ->extraAttributes([
                    'class' => 'animasi-spin-border cursor-pointer hover:scale-105 transition-transform duration-300',
                ]),

            Stat::make('Bulan Ini', $laporanBulanIni)
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info') 
                ->chart([1, 3, 2, 6, 4, 8, 10])
                ->url(LaporanUtamaResource::getUrl('index')) 
                ->extraAttributes([
                    'class' => 'animasi-spin-border cursor-pointer hover:scale-105 transition-transform duration-300',
                ]),

            Stat::make('Hari Ini', $laporanHariIni)
                ->description(Carbon::now()->translatedFormat('d F Y'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('indigo') 
                ->chart([0, 1, 0, 2, 1, 3, $laporanHariIni > 0 ? 5 : 0])
                ->url(LaporanUtamaResource::getUrl('index')) 
                ->extraAttributes([
                    'class' => 'animasi-spin-border cursor-pointer hover:scale-105 transition-transform duration-300', 
                ]),
        ];
    }
}