<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PeringatanAlphaWidget extends Widget
{
    protected string $view = 'filament.widgets.peringatan-alpha-widget';
    
    // Paksa posisi widget ini agar selalu berada di paling atas Dashboard
    protected static ?int $sort = -3; 
    
    // Buat widget ini melebar penuh (tidak terpotong setengah layar)
    protected int | string | array $columnSpan = 'full';

    // FUNGSI SAKTI: Ruqyah Mandiri + Tampilkan jika masih ada sisa
    public static function canView(): bool
    {
        $userName = Auth::user()->name;

        // --- 1. PROSES SAPU OTOMATIS (AUTO-CLEAN) ---
        // Ambil semua daftar hutang 'alpha' milik TD yang sedang login
        $alphas = LaporanUtama::where('status', 'alpha')
            ->where('nama_petugas', $userName)
            ->get();

        foreach ($alphas as $alpha) {
            // Cek silang: apakah si TD sudah punya laporan 'final' di tanggal dan shift ini?
            $sudahLunas = LaporanUtama::where('status', 'final')
                ->whereDate('tanggal_tugas', $alpha->tanggal_tugas)
                ->where('shift', $alpha->shift)
                ->exists();

            // Jika ternyata sudah lunas (data ganda akibat bug masa lalu), hapus si alpha!
            if ($sudahLunas) {
                $alpha->forceDelete();
            }
        }

        // --- 2. KEPUTUSAN TAMPIL / TIDAK ---
        // Setelah disapu bersih, cek lagi ke database. 
        // Apakah MASIH ADA laporan alpha yang benar-benar belum dikerjakan?
        return LaporanUtama::where('status', 'alpha')
            ->where('nama_petugas', $userName)
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