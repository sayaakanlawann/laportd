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
        $waktuSubmit = Carbon::parse($laporan->created_at)
                             ->timezone('Asia/Makassar')
                             ->format('d-M-Y H:i:s') . ' WITA';

        // 1. OLAH DATA TX (ARRAY -> TEKS)
        $petugasTx = is_array($laporan->tx_petugas_nama) 
            ? implode(', ', $laporan->tx_petugas_nama) 
            : $laporan->tx_petugas_nama;

        // 2. OLAH DATA EVIDENCE (ARRAY -> TEKS BERSUSUN)
        $evidenceText = '';
        if (is_array($laporan->evidence) && count($laporan->evidence) > 0) {
            foreach ($laporan->evidence as $ev) {
                
                // --- PERBAIKAN BEDAH MIKRO MUTLAK ---
                // Jangan gunakan $ev['link_drive'] dari database.
                // Rakit ulang linknya pakai asset() & file_id agar dinamis mengikuti Ngrok/Localhost!
                $linkDinamis = asset('storage/' . $ev['file_id']);
                
                $evidenceText .= $ev['keterangan'] . " :\n" . $linkDinamis . "\n\n";
            }
            // Menghapus enter lebih/kosong di bagian paling bawah teks
            $evidenceText = trim($evidenceText);
        } else {
            $evidenceText = 'Tidak ada evidence';
        }

        return [
            $waktuSubmit, // <-- Field waktu form disubmit
            Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            $laporan->nama_petugas,
            $laporan->pdu_nama,
            $petugasTx, // <-- Variabel TX yang sudah dirapikan
            implode("\n", $waktu),
            implode("\n", $program),
            implode("\n", $jenis),
            implode("\n", $status),
            $laporan->kesimpulan,
            $evidenceText // <-- Tambahan kolom baru untuk Evidence
        ];
    }

    public function headings(): array
    {
        // TAMBAHKAN JUDUL HEADER BARU DI AKHIR
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
            'Kesimpulan',
            'Link Evidence' // <-- Header Baru
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // GESER ABJAD STYLING KARENA KOLOM BERTAMBAH MENJADI K
        // Kita juga tambahkan kolom K ke dalam WrapText agar link drive tidak memanjang ke samping
        $sheet->getStyle('F:K')->getAlignment()->setWrapText(true); 
        $sheet->getStyle('A:K')->getAlignment()->setVertical('top');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
    }

    public function title(): string
    {
        return Carbon::createFromFormat('Y-m', $this->bulan)->translatedFormat('M Y');
    }
}