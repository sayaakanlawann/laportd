<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LaporanChartWidget extends ChartWidget
{
    protected ?string $heading = 'Statistik Laporan (6 Bulan Terakhir)';
    protected static ?int $sort = 3; 
    protected int | string | array $columnSpan = 'full'; 

    protected function getData(): array
    {
        $user = Auth::user();
        $query = LaporanUtama::query();

        // Terapkan Aturan Role (TD lihat miliknya, Admin/Dev lihat semua)
        if ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $query->where('nama_petugas', $user->name);
        }

        $dataPerBulan = [];
        $labelBulan = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulanTarget = Carbon::now()->subMonths($i);
            $labelBulan[] = $bulanTarget->translatedFormat('M Y');
            
            $jumlah = (clone $query)
                ->whereYear('tanggal_tugas', $bulanTarget->year)
                ->whereMonth('tanggal_tugas', $bulanTarget->month)
                ->count();
                
            $dataPerBulan[] = $jumlah;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Laporan',
                    'data' => $dataPerBulan,
                    // 1. FILL KACA: Warna sangat transparan (Opacity 0.2)
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.2)',   // Biru
                        'rgba(168, 85, 247, 0.2)',   // Ungu
                        'rgba(236, 72, 153, 0.2)',   // Pink
                        'rgba(244, 63, 94, 0.2)',    // Merah Muda
                        'rgba(249, 115, 22, 0.2)',   // Orange
                        'rgba(16, 185, 129, 0.2)',   // Hijau
                    ],
                    // 2. EFEK GLOW / PINGGIRAN KACA: Warna solid terang (Opacity 1.0)
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',     
                        'rgba(168, 85, 247, 1)',     
                        'rgba(236, 72, 153, 1)',     
                        'rgba(244, 63, 94, 1)',      
                        'rgba(249, 115, 22, 1)',     
                        'rgba(16, 185, 129, 1)',     
                    ],
                    // 3. EFEK HOVER: Kaca sedikit menyala saat disorot mouse (Opacity 0.4)
                    'hoverBackgroundColor' => [
                        'rgba(59, 130, 246, 0.4)',
                        'rgba(168, 85, 247, 0.4)',
                        'rgba(236, 72, 153, 0.4)',
                        'rgba(244, 63, 94, 0.4)',
                        'rgba(249, 115, 22, 0.4)',
                        'rgba(16, 185, 129, 0.4)',
                    ],
                    'borderWidth' => 2, // Ketebalan bingkai kaca
                    'borderRadius' => 8, // Ujung melengkung mulus ala UI modern
                    'borderSkipped' => false, // Memastikan garis bawah juga ikut digambar agar efek kacanya penuh
                ],
            ],
            'labels' => $labelBulan,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Diubah menjadi grafik batang agar warnanya lebih menonjol
    }
}