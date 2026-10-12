<?php

namespace App\Exports;

use App\Models\LaporanUtama;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents; // 🔥 Tambahan untuk Tanda Tangan
use Maatwebsite\Excel\Events\AfterSheet;  // 🔥 Tambahan untuk Tanda Tangan
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// 🔥 Tambahkan WithEvents di sini
class LaporanBulanSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected $bulan;
    protected $namaPetugas; // 🔥 Penampung dari LaporanExport

    // 🔥 Tangkap parameter namaPetugas agar tidak error
    public function __construct($bulan, $namaPetugas = null)
    {
        $this->bulan = $bulan;
        $this->namaPetugas = $namaPetugas;
    }

    public function collection()
    {
        $pecah = explode('-', $this->bulan);
        $user = Auth::user(); 
        
        $query = LaporanUtama::with('siarans')
            ->whereYear('tanggal_tugas', $pecah[0])
            ->whereMonth('tanggal_tugas', $pecah[1])
            ->orderBy('tanggal_tugas', 'ASC');

        // Gunakan namaPetugas dari filter LaporanExport, atau fallback ke user login
        if ($this->namaPetugas) {
            $query->where('nama_petugas', $this->namaPetugas);
        } elseif ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $query->where('nama_petugas', $user->name);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Timestamp', 
            'Tanggal Tugas', 
            'Shift', // 🔥 Misi 1: Judul Kolom Shift di urutan ke-3 (Kolom C)
            'TD', 
            'PDU', 
            'Asisten PDU',
            'TX', 
            'Waktu Siaran', 
            'Nama Program', 
            'Jenis Acara', 
            'Status & Kendala', 
            'Kesimpulan',
            'Link Evidence'
        ];
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

        $waktuSubmit = Carbon::parse($laporan->created_at)->timezone('Asia/Makassar')->format('d-M-Y H:i:s') . ' WITA';
        $petugasTx = is_array($laporan->tx_petugas_nama) ? implode(', ', $laporan->tx_petugas_nama) : $laporan->tx_petugas_nama;

        // ... (Fungsi processEvidence biarkan persis seperti kode asli Abang) ...
        $processEvidence = function($labelSection, $data) {
            $textOutput = '';
            if (empty($data)) return $textOutput;
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                $data = is_array($decoded) ? $decoded : [$data];
            }
            if (is_array($data)) {
                $validItems = [];
                foreach ($data as $item) {
                    $path = ''; $caption = '';
                    if (is_array($item)) {
                        $path = $item['path'] ?? $item['file_id'] ?? reset($item);
                        $caption = $item['keterangan'] ?? $item['caption'] ?? '';
                    } else {
                        $path = $item;
                    }
                    if (!empty($path) && is_string($path)) {
                        $validItems[] = ['path' => $path, 'caption' => $caption];
                    }
                }
                foreach ($validItems as $index => $item) {
                    $url = str_starts_with($item['path'], 'http') ? $item['path'] : asset('storage/' . $item['path']);
                    $imgNum = $index + 1;
                    $imgCaption = !empty($item['caption']) ? $item['caption'] : "Gambar {$imgNum} : {$labelSection}";
                    $textOutput .= $imgCaption . " :\n" . $url . "\n\n";
                }
            }
            return $textOutput;
        };

        $evidenceText = '';
        $evidenceText .= $processEvidence('Sebelum Siaran', $laporan->evidence_sebelum_siaran);
        $evidenceText .= $processEvidence('Alat & Master', $laporan->ev_alat_studio);
        $evidenceText .= $processEvidence('Jaringan', $laporan->ev_jaringan);
        $evidenceText .= $processEvidence('Jalur AV', $laporan->ev_jalur_av);
        $evidenceText .= $processEvidence('Evidence Kendala', $laporan->pra_ev_kendala);
        $evidenceText .= $processEvidence('Arsip Evidence (Lama)', $laporan->evidence);
        if (isset($laporan->link_drive)) $evidenceText .= $processEvidence('Arsip Link (Lama)', $laporan->link_drive);

        $evidenceText = trim($evidenceText);
        if (empty($evidenceText)) $evidenceText = 'Tidak ada evidence';

        return [
            $waktuSubmit, 
            Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            ucfirst($laporan->shift), // 🔥 Misi 1: Masukkan Data Shift di sini
            $laporan->nama_petugas,
            $laporan->pdu_nama,
            $laporan->asisten_pdu,
            $petugasTx, 
            implode("\n", $waktu),
            implode("\n", $program),
            implode("\n", $jenis),
            implode("\n", $status),
            $laporan->kesimpulan,
            $evidenceText 
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Sesuaikan rentang style karena kolom kita bertambah panjang sampai M (Kolom ke-13)
        $sheet->getStyle('A:M')->getAlignment()->setVertical('top');
        $sheet->getStyle('G:M')->getAlignment()->setWrapText(true); 
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
    }

    public function title(): string
    {
        return Carbon::createFromFormat('Y-m', $this->bulan)->translatedFormat('M Y');
    }

    // 🔥 Misi 2: SIHIR TANDA TANGAN DI POJOK BAWAH (KOLOM M) 🔥
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // 1. Cari batas baris terakhir data
                $barisTerakhir = $event->sheet->getDelegate()->getHighestRow();
                
                // 2. Lompat 3 baris ke bawah
                $barisTtd = $barisTerakhir + 3;
                
                $tanggalHariIni = Carbon::now()->locale('id')->translatedFormat('d F Y');
                $kolomTerakhir = 'M'; // Karena Link Evidence sekarang pindah ke kolom M

                // 3. Tulis teks tanda tangan
                $event->sheet->setCellValue("{$kolomTerakhir}{$barisTtd}", "Banjarmasin, {$tanggalHariIni}");
                
                $barisTtd++;
                $event->sheet->setCellValue("{$kolomTerakhir}{$barisTtd}", "Mengetahui,");
                $event->sheet->setCellValue("{$kolomTerakhir}" . ($barisTtd + 1), "KPP Teknik");
                
                // Lompat 5 baris untuk ruang tanda tangan basah
                $barisTtd += 5; 
                
                $event->sheet->setCellValue("{$kolomTerakhir}{$barisTtd}", "( ........................................ )");
                $event->sheet->setCellValue("{$kolomTerakhir}" . ($barisTtd + 1), "Lilik Susanto");
                $event->sheet->setCellValue("{$kolomTerakhir}" . ($barisTtd + 2), "NIP. 198003262014091001");

                // 4. Bikin rata tengah khusus area tanda tangan saja
                $event->sheet->getStyle("{$kolomTerakhir}" . ($barisTerakhir + 3) . ":{$kolomTerakhir}" . ($barisTtd + 1)  . ($barisTtd + 2))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ]
                ]);
            },
        ];
    }
}