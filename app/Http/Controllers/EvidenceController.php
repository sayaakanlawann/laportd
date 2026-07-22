<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanUtama;
use Illuminate\Support\Facades\Storage; // Mesin Penyimpanan Lokal
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Petugas;
use App\Models\ProgramSiaran;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class EvidenceController extends Controller
{
    public function create(Request $request) 
    {
        // 1. SOLUSI SHIFT HILANG: Gunakan Session agar aplikasi tidak pelupa
        if ($request->has('shift') && in_array($request->query('shift'), ['pagi', 'sore'])) {
            $shift = $request->query('shift');
            session(['shift_terpilih' => $shift]); // Kunci pilihan di memori
        } else {
            // Jika URL cuma /upload, ambil dari memori (default ke sore jika kosong)
            $shift = session('shift_terpilih', 'sore'); 
        }

        $td = Petugas::where('is_aktif', true)->where('jabatan_utama', 'Technical Director')->orderBy('nama')->get();
        $pdu = Petugas::where('is_aktif', true)->where('jabatan_utama', 'PDU')->orderBy('nama')->get();
        $tx = Petugas::where('is_aktif', true)->where('jabatan_utama', 'Transmisi')->orderBy('nama')->get();
        
        // 2. SOLUSI JAM ACAK: Urutkan berdasarkan jam terlebih dahulu
        $programsGrouped = ProgramSiaran::where('is_aktif', true)
            ->orderBy('jam_tayang_default') // <--- PERBAIKAN 1: Urutkan Jam 09.00 -> 10.00 dst
            ->orderBy('nama_program')       // <--- PERBAIKAN 2: Baru urutkan abjad A-Z
            ->get()
            ->filter(function ($program) use ($shift) {
                // Ambil 2 digit pertama dari jam_tayang_default (misal "09" dari "09:00|09:59")
                $jamMulai = (int) substr($program->jam_tayang_default, 0, 2);
                
                if ($shift == 'pagi') {
                    // Shift Pagi: Tampilkan jadwal di bawah jam 15:00
                    return $jamMulai < 15;
                } else {
                    // Shift Sore: Tampilkan jadwal jam 15:00 ke atas
                    return $jamMulai >= 15;
                }
            })
            ->groupBy('jam_tayang_default'); 
        
        return view('upload', compact('td', 'pdu', 'tx', 'programsGrouped', 'shift'));
    }

    // MENYIMPAN DATA KE LOCAL STORAGE
    // MENYIMPAN DATA (LOKAL + REPEATER JAM SIARAN)
    // MENYIMPAN DATA (LOKAL + REPEATER JAM SIARAN)
    public function store(Request $request)
    {
        // 1. Validasi Data (Termasuk Array dari Form Repeater)
        $request->validate([
            'shift'             => 'required|in:pagi,sore', // <-- TAMBAHAN BEDAH MIKRO
            'tanggal_tugas'     => 'required|date',
            'nama_petugas'      => 'required',
            'pdu_nama'          => 'required',
            'tx_petugas_nama'   => 'required',
            'pra_kendala'       => 'required',
            'kru_lengkap'       => 'required',
            'kesimpulan'        => 'required',
            
            // Validasi Array Repeater
            'waktu_siaran'      => 'required|array',
            'waktu_siaran.*'    => 'required',
            'jenis_acara'       => 'required|array',
            'jenis_acara.*'     => 'required',
            'nama_program'      => 'required|array',
            'nama_program.*'    => 'required',
            'status_siaran'     => 'required|array',
            'status_siaran.*'   => 'required',
            'catatan_kendala'   => 'nullable|array',

            // --- VALIDASI FILE MAX 10MB (10240 KB) ---
            'ev_alat_studio'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'ev_jaringan'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'ev_jalur_av'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'pra_ev_kendala'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            $kumpulanEvidence = []; 

            // Helper function untuk Upload Lokal (Tetap sama)
            $uploadKeLokal = function ($namaInputFile, $keterangan) use ($request, &$kumpulanEvidence) {
                if ($request->hasFile($namaInputFile)) {
                    $file = $request->file($namaInputFile);
                    $namaFile = time() . '_' . $namaInputFile . '_' . $file->getClientOriginalName();
                    
                    $path = $file->storeAs('evidence', $namaFile, 'public');
                    
                    $kumpulanEvidence[] = [
                        'keterangan' => $keterangan,
                        'filename'   => $namaFile,
                        'file_id'    => $path,
                        'link_drive' => asset('storage/' . $path)
                    ];
                }
            };

            // Jalankan upload lokal satu per satu
            $uploadKeLokal('ev_alat_studio', 'Alat Studio & Master');
            $uploadKeLokal('ev_jaringan', 'Pengecekan Jaringan');
            $uploadKeLokal('ev_jalur_av', 'Jalur Audio & Video');
            $uploadKeLokal('pra_ev_kendala', 'Evidence Kendala (Pra-Siaran)');

            // 2. Simpan Data Induk dan tampung di dalam variabel $laporanUtama
            $laporanUtama = LaporanUtama::create([
                'shift'           => $request->shift, // <-- TAMBAHAN BEDAH MIKRO
                'tanggal_tugas'   => $request->tanggal_tugas,
                'nama_petugas'    => $request->nama_petugas,
                'pdu_nama'        => $request->pdu_nama,
                'tx_petugas_nama' => $request->tx_petugas_nama,
                'pra_kendala'     => $request->pra_kendala,
                'pra_ket_kendala' => $request->pra_ket_kendala,
                'kru_lengkap'     => $request->kru_lengkap,
                'kesimpulan'      => $request->kesimpulan,
                'evidence'        => $kumpulanEvidence, 
            ]);

            // 3. Simpan Data Anak (Jam Siaran) menggunakan Relasi Eloquent
            $dataSiaran = [];
            foreach ($request->waktu_siaran as $index => $waktu) {
                $namaAcara = $request->nama_program[$index];
                if ($namaAcara === 'Other') {
                    $namaAcara = $request->nama_program_custom[$index];
                }
                
                // Membelah '15:00|15:59' menjadi array: [0] => 15:00, [1] => 15:59
                $pecahWaktu = explode('|', $waktu); 
                
                $dataSiaran[] = [
                    'jam_tayang'      => $pecahWaktu[0], // Ambil 15:00
                    'jam_selesai'     => $pecahWaktu[1], // Ambil 15:59
                    'nama_program'    => $namaAcara,
                    'jenis_acara'     => $request->jenis_acara[$index],
                    'status_siaran'   => $request->status_siaran[$index],
                    'catatan_kendala' => $request->catatan_kendala[$index] ?? null, 
                ];
            }
            
            // Keajaiban Laravel: Simpan semua array anak sekaligus ke tabel laporan_siarans!
            $laporanUtama->siarans()->createMany($dataSiaran);

            return redirect('/admin')->with('success', 'HORE! Laporan Induk & Log Jam Tayang berhasil disimpan secepat kilat!');
        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $evidences = LaporanUtama::with('siarans')->latest()->get();
        return view('index', compact('evidences'));
    }

    // MENGHAPUS FILE DARI LOCAL STORAGE
    public function destroy($id)
    {
        try {
            $laporan = LaporanUtama::findOrFail($id);

            // Hapus file fisik lokalnya
            if (is_array($laporan->evidence)) {
                foreach ($laporan->evidence as $ev) {
                    if (isset($ev['file_id']) && Storage::disk('public')->exists($ev['file_id'])) {
                        Storage::disk('public')->delete($ev['file_id']);
                    }
                }
            }

            $laporan->delete();

            return back()->with('success', 'Laporan dan SEMUA file di server lokal berhasil dihapus bersih!');

        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL menghapus: ' . $e->getMessage());
        }
    }

    // GENERATOR PDF (Tetap Menggunakan Fungsi yang Sama)
    public function download($id)
    {
        // Tambahkan with('siarans') agar data log siaran ikut terpanggil
        $laporan = LaporanUtama::with('siarans')->findOrFail($id);
        
        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                  ->loadView('pdf_resume', compact('laporan'));
        
        return $pdf->download('Resume_Laporan_TD_' . $laporan->nama_petugas . '.pdf');
    }
    // GENERATOR EXCEL REKAP
    // GENERATOR EXCEL REKAP
    // GENERATOR EXCEL REKAP
    public function exportExcel(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Siapkan variabel nama petugas (Jika Admin/Dev, biarkan null)
        $namaPetugas = null;
        if ($user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            $namaPetugas = $user->name;
        }

        // Cek apakah mode Export All ditekan
        if ($request->has('export_all')) {
            $namaFile = 'Rekap_TD_Semua_Bulan_' . date('Ymd') . '.xlsx';
            // Suntikkan $namaPetugas ke parameter kedua
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LaporanExport('all', $namaPetugas), $namaFile);
        } 
        
        // Jika hanya 1 bulan
        $bulan = $request->bulan; 
        $namaFile = 'Rekap_TD_' . $bulan . '.xlsx';
        // Suntikkan $namaPetugas ke parameter kedua
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LaporanExport($bulan, $namaPetugas), $namaFile);
    }

    // MENAMPILKAN FORM EDIT DENGAN DATA LAMA
    public function edit($id)
    {
        // Panggil laporan beserta data log siarannya
        $laporan = LaporanUtama::with('siarans')->findOrFail($id);
        
        // Panggil data master seperti biasa
        $td = Petugas::where('is_aktif', true)->where('jabatan_utama', 'Technical Director')->orderBy('nama')->get();
        $pdu = Petugas::where('is_aktif', true)->where('jabatan_utama', 'PDU')->orderBy('nama')->get();
        $tx = Petugas::where('is_aktif', true)->where('jabatan_utama', 'Transmisi')->orderBy('nama')->get();
        
        $programsGrouped = ProgramSiaran::where('is_aktif', true)
                                ->orderBy('nama_program')
                                ->get()
                                ->groupBy('jam_tayang_default'); 
        
        return view('edit', compact('laporan', 'td', 'pdu', 'tx', 'programsGrouped'));
    }

    // MEMPROSES PERUBAHAN DATA
    public function update(Request $request, $id)
    {
        // 1. Validasi (Sama seperti store, TAPI file upload menjadi nullable)
        $request->validate([
            'tanggal_tugas'     => 'required|date',
            'nama_petugas'      => 'required',
            'pdu_nama'          => 'required',
            'tx_petugas_nama'   => 'required',
            'pra_kendala'       => 'required',
            'kru_lengkap'       => 'required',
            'kesimpulan'        => 'required',
            'waktu_siaran'      => 'required|array',
            'waktu_siaran.*'    => 'required',
            'jenis_acara'       => 'required|array',
            'jenis_acara.*'     => 'required',
            'nama_program'      => 'required|array',
            'nama_program.*'    => 'required',
            'status_siaran'     => 'required|array',
            'status_siaran.*'   => 'required',
            
            // File opsional: Hanya divalidasi kalau user mengunggah file baru
            'ev_alat_studio'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'ev_jaringan'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'ev_jalur_av'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'pra_ev_kendala'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            $laporan = LaporanUtama::findOrFail($id);
            $kumpulanEvidence = is_array($laporan->evidence) ? $laporan->evidence : [];

            // Helper function untuk Replace File Lama
            $updateFile = function ($namaInputFile, $keterangan) use ($request, &$kumpulanEvidence) {
                if ($request->hasFile($namaInputFile)) {
                    // Cari dan hapus file lama dari array & storage
                    foreach ($kumpulanEvidence as $key => $ev) {
                        if ($ev['keterangan'] === $keterangan) {
                            if (isset($ev['file_id']) && Storage::disk('public')->exists($ev['file_id'])) {
                                Storage::disk('public')->delete($ev['file_id']);
                            }
                            unset($kumpulanEvidence[$key]); // Buang dari array
                        }
                    }

                    // Upload file baru
                    $file = $request->file($namaInputFile);
                    $namaFile = time() . '_' . $namaInputFile . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('evidence', $namaFile, 'public');
                    
                    // Masukkan ke array evidence
                    $kumpulanEvidence[] = [
                        'keterangan' => $keterangan,
                        'filename'   => $namaFile,
                        'file_id'    => $path,
                        'link_drive' => asset('storage/' . $path)
                    ];
                }
            };

            // Jalankan cek upload
            $updateFile('ev_alat_studio', 'Alat Studio & Master');
            $updateFile('ev_jaringan', 'Pengecekan Jaringan');
            $updateFile('ev_jalur_av', 'Jalur Audio & Video');
            $updateFile('pra_ev_kendala', 'Evidence Kendala (Pra-Siaran)');

            // Kerucutkan ulang index array (opsional tapi disarankan)
            $kumpulanEvidence = array_values($kumpulanEvidence);

            // 2. Update Data Induk
            $laporan->update([
                'tanggal_tugas'   => $request->tanggal_tugas,
                'nama_petugas'    => $request->nama_petugas,
                'pdu_nama'        => $request->pdu_nama,
                'tx_petugas_nama' => $request->tx_petugas_nama,
                'pra_kendala'     => $request->pra_kendala,
                'pra_ket_kendala' => $request->pra_ket_kendala,
                'kru_lengkap'     => $request->kru_lengkap,
                'kesimpulan'      => $request->kesimpulan,
                'evidence'        => $kumpulanEvidence, 
            ]);

            // 3. Update Log Siaran (Trik Cepat: Hapus semua log lama, masukkan log baru dari form)
            $laporan->siarans()->delete(); 
            
            $dataSiaran = [];
            foreach ($request->waktu_siaran as $index => $waktu) {
                $namaAcara = $request->nama_program[$index];
                if ($namaAcara === 'Other') {
                    $namaAcara = $request->nama_program_custom[$index];
                }
                
                $pecahWaktu = explode('|', $waktu); 
                
                $dataSiaran[] = [
                    'jam_tayang'      => $pecahWaktu[0],
                    'jam_selesai'     => $pecahWaktu[1],
                    'nama_program'    => $namaAcara,
                    'jenis_acara'     => $request->jenis_acara[$index],
                    'status_siaran'   => $request->status_siaran[$index],
                    'catatan_kendala' => $request->catatan_kendala[$index] ?? null, 
                ];
            }
            
            $laporan->siarans()->createMany($dataSiaran);

            return redirect('/evidence')->with('success', 'Laporan berhasil diperbarui dengan sempurna!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }
}