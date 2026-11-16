<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanUtama;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Helper: Konversi format DDMMYY ke Y-m-d
     */
    private function parseTanggalDmy(?string $input, Carbon $default): ?Carbon
    {
        if (empty($input)) {
            return $default;
        }

        // Pastikan input tepat 6 digit angka (DDMMYY)
        if (!preg_match('/^\d{6}$/', $input)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('dmy', $input)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Ambil Data Laporan Berdasarkan Tanggal Tugas
     * 
     * Mengambil daftar shift TD dan log siaran. Jika parameter tanggal dikosongkan, sistem otomatis mengambil data hari ini.
     * 
     * @queryParam tanggal string Format DDMMYY (6 digit angka). Contoh: 161126
     */
    public function getByTanggalTugas(Request $request)
    {
        $tanggalObj = $this->parseTanggalDmy($request->query('tanggal'), Carbon::today());

        if (!$tanggalObj) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Format tanggal tidak valid. Gunakan format DDMMYY (contoh: 161126).'
            ], 400);
        }

        $laporan = LaporanUtama::with('siarans')
            ->whereDate('tanggal_tugas', $tanggalObj->format('Y-m-d'))
            ->whereIn('status', ['final', 'alpha'])
            ->orderBy('shift', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'filter' => 'tanggal_tugas',
            'tanggal' => $tanggalObj->format('dmy'),
            'total_data' => $laporan->count(),
            'data' => $laporan->map(fn($record) => $this->formatDataShift($record))
        ], 200);
    }

    /**
     * Ambil Data Laporan Berdasarkan Rentang Tanggal Tugas
     * 
     * Mengambil rekap shift dalam rentang waktu tertentu. Default mengambil 7 hari terakhir hingga hari ini.
     * 
     * @queryParam mulai string Tanggal awal format DDMMYY. Contoh: 011126
     * @queryParam akhir string Tanggal akhir format DDMMYY. Contoh: 161126
     */
    public function getByRentangTanggal(Request $request)
    {
        $mulaiObj = $this->parseTanggalDmy($request->query('mulai'), Carbon::today()->subDays(7));
        $akhirObj = $this->parseTanggalDmy($request->query('akhir'), Carbon::today());

        if (!$mulaiObj || !$akhirObj) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Format tanggal mulai/akhir tidak valid. Gunakan format DDMMYY (contoh: 011126).'
            ], 400);
        }

        $laporan = LaporanUtama::with('siarans')
            ->whereBetween('tanggal_tugas', [$mulaiObj->format('Y-m-d'), $akhirObj->format('Y-m-d')])
            ->whereIn('status', ['final', 'alpha'])
            ->orderBy('tanggal_tugas', 'desc')
            ->orderBy('shift', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'filter' => 'rentang_tanggal',
            'rentang' => [
                'mulai' => $mulaiObj->format('dmy'),
                'akhir' => $akhirObj->format('dmy'),
            ],
            'total_data' => $laporan->count(),
            'data' => $laporan->map(fn($record) => $this->formatDataShift($record))
        ], 200);
    }

    /**
     * Ambil Data Laporan Berdasarkan Waktu Submit
     * 
     * Mengambil data laporan berdasarkan kapan laporan diinput ke sistem (created_at).
     * 
     * @queryParam tanggal string Format DDMMYY (6 digit angka). Contoh: 161126
     */
    public function getByWaktuSubmit(Request $request)
    {
        $tanggalObj = $this->parseTanggalDmy($request->query('tanggal'), Carbon::today());

        if (!$tanggalObj) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Format tanggal tidak valid. Gunakan format DDMMYY (contoh: 161126).'
            ], 400);
        }

        $laporan = LaporanUtama::with('siarans')
            ->whereDate('created_at', $tanggalObj->format('Y-m-d'))
            ->whereIn('status', ['final', 'alpha'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'filter' => 'waktu_submit',
            'tanggal' => $tanggalObj->format('dmy'),
            'total_data' => $laporan->count(),
            'data' => $laporan->map(fn($record) => $this->formatDataShift($record))
        ], 200);
    }

    /**
     * Helper: Memformat struktur JSON tiap baris laporan shift & siaran
     */
    private function formatDataShift($record): array
    {
        return [
            'id' => $record->id,
            'tanggal_tugas' => Carbon::parse($record->tanggal_tugas)->format('Y-m-d'),
            'shift' => ucfirst($record->shift),
            'status' => $record->status,
            'petugas' => [
                'td' => $record->nama_petugas,
                'pdu' => $record->pdu_nama,
                'asisten_pdu' => $record->asisten_pdu,
                'tx' => $record->tx_petugas_nama,
            ],
            'kesimpulan' => $record->kesimpulan,
            'waktu_submit' => Carbon::parse($record->created_at)
                ->timezone('Asia/Makassar')
                ->format('Y-m-d H:i:s') . ' WITA',
            'daftar_siaran' => $record->siarans->map(function ($siaran) {
                return [
                    'jam_tayang' => Carbon::parse($siaran->jam_tayang)->format('H:i'),
                    'jam_selesai' => Carbon::parse($siaran->jam_selesai)->format('H:i'),
                    'nama_program' => $siaran->nama_program,
                    'jenis_acara' => $siaran->jenis_acara,
                    'status_siaran' => $siaran->status_siaran,
                    'catatan_kendala' => $siaran->catatan_kendala,
                ];
            }),
        ];
    }
}