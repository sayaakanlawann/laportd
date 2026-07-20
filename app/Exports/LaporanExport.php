<?php

namespace App\Exports;

use App\Models\LaporanUtama;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class LaporanExport implements WithMultipleSheets
{
    protected $mode;

    public function __construct($mode)
    {
        $this->mode = $mode; // Bisa berisi "2026-07" atau "all"
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->mode === 'all') {
            // Jika mode "all", cari semua bulan yang ada datanya di tabel laporan
            $listBulan = LaporanUtama::select('tanggal_tugas')
                ->get()
                ->map(function ($item) {
                    return Carbon::parse($item->tanggal_tugas)->format('Y-m');
                })
                ->unique()
                ->sortDesc(); // Urutkan dari bulan terbaru

            // Looping dan buatkan Sheet untuk setiap bulan yang ditemukan
            foreach ($listBulan as $bulan) {
                $sheets[] = new LaporanBulanSheet($bulan);
            }
        } else {
            // Jika hanya pilih 1 bulan spesifik, buat 1 sheet saja
            $sheets[] = new LaporanBulanSheet($this->mode);
        }

        return $sheets;
    }
}