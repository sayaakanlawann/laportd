<?php

namespace App\Exports;

use App\Models\LaporanUtama;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class LaporanExport implements WithMultipleSheets
{
    protected $mode;
    protected $namaPetugas; // <-- Tambahkan properti penampung

    // <-- Terima parameter kedua
    public function __construct($mode, $namaPetugas = null) 
    {
        $this->mode = $mode; 
        $this->namaPetugas = $namaPetugas; // <-- Simpan ke memori
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->mode === 'all') {
            // Bangun query untuk mencari bulan
            $query = LaporanUtama::select('tanggal_tugas');

            // --- FILTER: HANYA CARI BULAN DI MANA TD INI BERTUGAS ---
            if ($this->namaPetugas) {
                $query->where('nama_petugas', $this->namaPetugas);
            }

            $listBulan = $query->get()
                ->map(function ($item) {
                    return Carbon::parse($item->tanggal_tugas)->format('Y-m');
                })
                ->unique()
                ->sortDesc(); 

            foreach ($listBulan as $bulan) {
                // <-- Lempar lagi nama petugas ke pembuat Sheet
                $sheets[] = new LaporanBulanSheet($bulan, $this->namaPetugas);
            }
        } else {
            // <-- Lempar lagi nama petugas ke pembuat Sheet
            $sheets[] = new LaporanBulanSheet($this->mode, $this->namaPetugas);
        }

        return $sheets;
    }
}