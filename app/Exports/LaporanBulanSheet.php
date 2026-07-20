<?php

namespace App\Exports;

use App\Models\LaporanUtama;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class LaporanBulanSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $bulan;

    public function __construct($bulan)
    {
        $this->bulan = $bulan;
    }

    public function collection()
    {
        $pecah = explode('-', $this->bulan);
        
        return LaporanUtama::with('siarans')
            ->whereYear('tanggal_tugas', $pecah[0])
            ->whereMonth('tanggal_tugas', $pecah[1])
            ->orderBy('tanggal_tugas', 'ASC')
            ->get();
    }

    public function map($laporan): array
    {
        $waktu = [];
        $program = [];
        $jenis = [];
        $status = [];
        
        foreach($laporan->siarans as $siaran) {
            $waktu[] = Carbon::parse($siaran->jam_tayang)->format('H:i') . ' - ' . Carbon::parse($siaran->jam_selesai)->format('H:i');
            $program[] = $siaran->nama_program;
            $jenis[] = $siaran->jenis_acara;
            $kendala = $siaran->catatan_kendala ? ' (' . $siaran->catatan_kendala . ')' : '';
            $status[] = $siaran->status_siaran . $kendala;
        }

        // AMBIL WAKTU SUBMIT ASLI DARI DATABASE (created_at)
        // Kita paksa formatnya ke zona waktu WITA agar seragam
        $waktuSubmit = Carbon::parse($laporan->created_at)
                             ->timezone('Asia/Makassar')
                             ->format('d-M-Y H:i:s') . ' WITA';

        return [
            $waktuSubmit, // <-- Field waktu form disubmit
            Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            $laporan->nama_petugas,
            $laporan->pdu_nama,
            $laporan->tx_petugas_nama,
            implode("\n", $waktu),
            implode("\n", $program),
            implode("\n", $jenis),
            implode("\n", $status),
            $laporan->kesimpulan
        ];
    }

    public function headings(): array
    {
        // 2. TAMBAHKAN JUDUL HEADER BARU
        return [
            'Timestamp', 
            'Tanggal Tugas', 
            'TD', 
            'PDU', 
            'TX', 
            'Waktu Siaran', 
            'Nama Program', 
            'Jenis Acara', 
            'Status & Kendala', 
            'Kesimpulan'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 3. GESER ABJAD STYLING KARENA KOLOM BERTAMBAH (Dari I menjadi J)
        $sheet->getStyle('F:I')->getAlignment()->setWrapText(true); // Kolom waktu sampai status
        $sheet->getStyle('A:J')->getAlignment()->setVertical('top');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
    }

    public function title(): string
    {
        return Carbon::createFromFormat('Y-m', $this->bulan)->translatedFormat('M Y');
    }
}