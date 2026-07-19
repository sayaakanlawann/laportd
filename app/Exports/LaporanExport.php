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
    public function collection()
    {
        // Kita ambil semua log siaran (anak) dan hubungkan ke laporan utama (induk)
        return \App\Models\LaporanSiaran::with('laporanUtama')->latest()->get();
    }

    public function map($siaran): array
    {
        // Mengambil data dari induk (LaporanUtama)
        $laporan = $siaran->laporanUtama;

        return [
            Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            $laporan->nama_petugas,
            $laporan->pdu_nama,
            $laporan->tx_petugas_nama,
            
            // Data Detail Log Siaran
            Carbon::parse($siaran->jam_tayang)->format('H:i') . ' - ' . Carbon::parse($siaran->jam_selesai)->format('H:i'),
            $siaran->nama_program,
            $siaran->jenis_acara,
            $siaran->status_siaran,
            $siaran->catatan_kendala ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'TD',
            'PDU',
            'TX',
            'Waktu Siaran',
            'Nama Program',
            'Jenis Acara',
            'Status',
            'Catatan Kendala'
        ];
    }
}