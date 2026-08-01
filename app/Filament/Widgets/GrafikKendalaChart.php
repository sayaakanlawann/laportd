<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanUtama;
// PENTING: Sesuaikan "LaporanSiaran" dengan nama Model yang Abang pakai untuk log siaran
use App\Models\LaporanSiaran; 

class GrafikKendalaChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Jenis Kendala';
    protected static ?int $sort = 2; 
    protected int | string | array $columnSpan = 1; 

    protected function getData(): array
    {
        $user = Auth::user();
        
        // 1. Buat antrean query dasar untuk Laporan Utama
        $queryUtama = LaporanUtama::query();

        // 2. Terapkan Aturan Role (Sama persis seperti di LaporanChartWidget)
        if ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $queryUtama->where('nama_petugas', $user->name);
        }

        // 3. KUNCI RAHASIA: Ambil kumpulan ID Laporan yang boleh diakses user ini
        $laporanIds = $queryUtama->pluck('id')->toArray();

        // 4. Hitung Kendala Log Siaran HANYA dari ID laporan yang diizinkan tersebut
        // Catatan: Jika kolom relasi di database Abang bukan 'laporan_utama_id', silakan diganti
        $kendalaAudio = LaporanSiaran::whereIn('laporan_utama_id', $laporanIds)
            ->where('status_siaran', 'Audio')->count();
            
        $kendalaVideo = LaporanSiaran::whereIn('laporan_utama_id', $laporanIds)
            ->where('status_siaran', 'Video')->count();
            
        $kendalaPerangkat = LaporanSiaran::whereIn('laporan_utama_id', $laporanIds)
            ->where('status_siaran', 'Perangkat Lainnya')->count();

        // 5. Hitung Kendala Pra-Siaran (Bisa langsung pakai query utama yang sudah difilter)
        $kendalaPra = (clone $queryUtama)->where('pra_kendala', '1')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Total Kejadian',
                    'data' => [$kendalaPra, $kendalaAudio, $kendalaVideo, $kendalaPerangkat],
                    'backgroundColor' => [
                        '#FF6384', 
                        '#36A2EB', 
                        '#FFCE56', 
                        '#4BC0C0', 
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'barThickness' => 15,      
                    'maxBarThickness' => 20,
                    'borderRadius' => 4,       
                ],
            ],
            'labels' => ['Pra-Siaran', 'Masalah Audio', 'Masalah Video', 'Perangkat Lain'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1, 
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false, 
                ],
            ],
        ];
    }
}