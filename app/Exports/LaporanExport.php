<?php

namespace App\Exports;

use App\Models\LaporanUtama;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class LaporanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    // 1. Ambil Data dari Database (Sertakan relasi log siaran)
    public function collection()
    {
        return LaporanUtama::with('siarans')->latest()->get();
    }

    // 2. Petakan Data ke Baris Excel
    public function map($laporan): array
    {
        // Trik: Gabungkan semua array log siaran menjadi satu teks panjang
        $logSiaranTeks = $laporan->siarans->map(function($siaran) {
            $jamMulai = Carbon::parse($siaran->jam_tayang)->format('H:i');
            $jamSelesai = Carbon::parse($siaran->jam_selesai)->format('H:i');
            
            return "{$jamMulai}-{$jamSelesai} : {$siaran->nama_program} ({$siaran->status_siaran})";
        })->implode("\n");

        return [
            Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            $laporan->nama_petugas,
            $laporan->pdu_nama,
            $laporan->tx_petugas_nama,
            $laporan->kru_lengkap ? 'Lengkap' : 'Tidak Lengkap',
            $laporan->pra_kendala ? 'Ada Kendala' : 'Aman',
            $laporan->pra_ket_kendala ?? '-',
            $laporan->kesimpulan,
            $logSiaranTeks,
        ];
    }

    // 3. Buat Judul Kolom (Header) di Baris Paling Atas
    public function headings(): array
    {
        return [
            'Tanggal',
            'Technical Director',
            'Petugas PDU',
            'Petugas TX',
            'Kehadiran Kru',
            'Status Pra-Siaran',
            'Detail Kendala',
            'Kesimpulan Akhir',
            'Log Siaran Harian'
        ];
    }
}