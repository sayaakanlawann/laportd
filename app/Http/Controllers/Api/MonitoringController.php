<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanUtama;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function getShiftHariIni(Request $request)
    {
        // 1. Ambil tanggal dari parameter (default: hari ini)
        $tanggal = $request->query('tanggal', Carbon::today()->format('Y-m-d'));

        // 2. Tarik data Shift Pagi (Tambahkan with('siarans') agar tabel relasi ikut terbawa)
        $pagi = LaporanUtama::with('siarans')
            ->whereDate('tanggal_tugas', $tanggal)
            ->where('shift', 'pagi')
            ->whereIn('status', ['final', 'alpha'])
            ->orderBy('status', 'desc')
            ->first();

        // 3. Tarik data Shift Sore (Tambahkan with('siarans') juga)
        $sore = LaporanUtama::with('siarans')
            ->whereDate('tanggal_tugas', $tanggal)
            ->where('shift', 'sore')
            ->whereIn('status', ['final', 'alpha'])
            ->orderBy('status', 'desc')
            ->first();

        // 4. Keluarkan JSON
        return response()->json([
            'status' => 'success',
            'pesan' => 'Data detail monitoring shift berhasil diambil',
            'data' => [
                'tanggal_request' => $tanggal,
                'shift_pagi' => $this->formatDataShift($pagi),
                'shift_sore' => $this->formatDataShift($sore),
            ]
        ], 200);
    }

    // Fungsi peracik data agar JSON-nya cantik dan sesuai request Putra
    // 🔥 FUNGSI BARU: Cari berdasarkan tanggal di-submit (created_at)
    public function getLaporanByCreatedAt(Request $request)
    {
        // 1. Ambil parameter tanggal (default: hari ini)
        $tanggal = $request->query('tanggal', Carbon::today()->format('Y-m-d'));

        // 2. Tarik semua laporan yang DIBUAT (di-input) pada tanggal tersebut
        // Menggunakan whereDate('created_at') untuk mencocokkan tanggal dari timestamp
        $laporan = LaporanUtama::with('siarans')
            ->whereDate('created_at', $tanggal)
            ->whereIn('status', ['final', 'alpha'])
            ->orderBy('created_at', 'desc') // Urutkan dari jam input terbaru
            ->get();

        // 3. Rombak datanya agar rapi (tetap menggunakan fungsi yang sama agar Putra tidak bingung)
        $dataRapi = $laporan->map(function ($record) {
            return $this->formatDataShift($record);
        });

        // 4. Keluarkan JSON
        return response()->json([
            'status' => 'success',
            'pesan' => "Data laporan yang di-input/dibuat pada tanggal {$tanggal} berhasil diambil",
            'total_data' => $laporan->count(),
            'tanggal_pencarian' => $tanggal,
            'data' => $dataRapi
        ], 200);
    }
    private function formatDataShift($record)
    {
        // Jika TD belum input sama sekali
        if (!$record) {
            return null; // Kita kasih null agar Putra tahu memang kosong
        }

        // Kita format log siarannya agar gampang dibaca Putra
        $logSiaranRapi = $record->siarans->map(function ($siaran) {
            
            // Format jam (jaga-jaga kalau ada data lama yang masih pakai '|')
            $jamMulai = str_contains($siaran->jam_tayang, '|') ? explode('|', $siaran->jam_tayang)[0] : \Carbon\Carbon::parse($siaran->jam_tayang)->format('H:i');
            $jamSelesai = $siaran->jam_selesai ? \Carbon\Carbon::parse($siaran->jam_selesai)->format('H:i') : '-';

            return [
                'waktu_siaran' => "{$jamMulai} - {$jamSelesai}",
                'program' => $siaran->nama_program,
                'jenis_acara' => $siaran->jenis_acara,
                'status_kendala' => $siaran->status_siaran,
                'catatan' => $siaran->catatan_kendala ?? '-'
            ];
        });

        // Struktur utama yang akan diterima Putra
        return [
            'status_laporan' => $record->status === 'final' ? 'Final' : 'Alpha',
            'shift' => ucfirst($record->shift),
            'dibuat_pada' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '-',
            'tanggal_tugas' => $record->tanggal_tugas,
            'nama_td' => $record->nama_petugas,
            'kru_lengkap' => $record->kru_lengkap ? 'Ya' : 'Tidak',
            'kendala_pra_siaran' => $record->pra_kendala ? 'Ada Kendala' : 'Aman',
            'semua_log_siaran' => $logSiaranRapi
        ];
    }
}