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
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user(); // Ambil data user yang sedang login
        
        $query = LaporanUtama::with('siarans')
            ->whereYear('tanggal_tugas', $pecah[0])
            ->whereMonth('tanggal_tugas', $pecah[1])
            ->orderBy('tanggal_tugas', 'ASC');

        // Jika user BUKAN admin dan BUKAN noa@dev.id, kunci datanya ke nama user tersebut
        if ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $query->where('nama_petugas', $user->name);
        }

        return $query->get();
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

        // 2. OLAH DATA EVIDENCE (GABUNGAN KOLOM BARU DAN DATA LAMA)
        $evidenceText = '';

        // Fungsi Helper untuk merakit teks per kategori kolom
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
                    $path = '';
                    $caption = '';
                    
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

        // Kumpulkan tautan dari semua kolom yang tersedia
        $evidenceText .= $processEvidence('Sebelum Siaran', $laporan->evidence_sebelum_siaran);
        $evidenceText .= $processEvidence('Alat & Master', $laporan->ev_alat_studio);
        $evidenceText .= $processEvidence('Jaringan', $laporan->ev_jaringan);
        $evidenceText .= $processEvidence('Jalur AV', $laporan->ev_jalur_av);
        $evidenceText .= $processEvidence('Evidence Kendala', $laporan->pra_ev_kendala);
        
        // Kumpulkan arsip data lama
        $evidenceText .= $processEvidence('Arsip Evidence (Lama)', $laporan->evidence);
        if (isset($laporan->link_drive)) {
            $evidenceText .= $processEvidence('Arsip Link (Lama)', $laporan->link_drive);
        }

        // Bersihkan spasi kosong berlebih di akhir teks
        $evidenceText = trim($evidenceText);
        if (empty($evidenceText)) {
            $evidenceText = 'Tidak ada evidence';
        }

        return [
            $waktuSubmit, 
            Carbon::parse($laporan->tanggal_tugas)->format('d-m-Y'),
            $laporan->nama_petugas,
            $laporan->pdu_nama,
            $laporan->asisten_pdu,
            $petugasTx, 
            implode("\n", $waktu),
            implode("\n", $program),
            implode("\n", $jenis),
            implode("\n", $status),
            $laporan->kesimpulan,
            $evidenceText // <-- Hasil gabungan link evidence yang sudah berformat rapi
        ];
    }

    public function headings(): array
    {
        return [
            'Timestamp', 
            'Tanggal Tugas', 
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

    public function styles(Worksheet $sheet)
    {
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