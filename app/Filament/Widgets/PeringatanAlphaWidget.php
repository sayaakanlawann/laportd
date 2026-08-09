<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama;
use Filament\Widgets\Widget;

class PeringatanAlphaWidget extends Widget
{
    protected string $view = 'filament.widgets.peringatan-alpha-widget';
    
    // Paksa posisi widget ini agar selalu berada di paling atas Dashboard
    protected static ?int $sort = -3; 
    
    // Buat widget ini melebar penuh (tidak terpotong setengah layar)
    protected int | string | array $columnSpan = 'full';

    // FUNGSI SAKTI: Sembunyikan widget jika tidak ada teguran
    public static function canView(): bool
    {
        return LaporanUtama::where('status', 'alpha')
            ->where('nama_petugas', auth()->user()->name)
            ->exists();
    }

    // Kirim data laporan alpha ke tampilan Blade
    protected function getViewData(): array
    {
        return [
            'alphaLaporans' => LaporanUtama::where('status', 'alpha')
                ->where('nama_petugas', auth()->user()->name)
                ->orderBy('tanggal_tugas', 'asc')
                ->get(),
        ];
    }
}