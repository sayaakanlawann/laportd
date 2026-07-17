<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanUtama;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Barryvdh\DomPDF\Facade\Pdf; // Import mesin PDF

class EvidenceController extends Controller
{
    public function create() { return view('upload'); }

    // MENYIMPAN DATA (DENGAN FIELD EVIDENCE GABUNGAN JSON)
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_tugas'   => 'required|date',
            'nama_petugas'    => 'required',
            'pdu_nama'        => 'required',
            'tx_petugas_nama' => 'required',
            'pra_kendala'     => 'required',
            'kru_lengkap'     => 'required',
            'kesimpulan'      => 'required',
        ]);

        try {
            $client = new Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));
            $service = new Drive($client);
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

            $kumpulanEvidence = []; // Array kosong untuk menampung semua evidence

            // Helper function yang langsung menangkap ID dan membuat Link
            // Helper function yang langsung menangkap ID dan membuat Link
            $uploadKeDrive = function ($namaInputFile, $keterangan) use ($request, $service, $folderId, &$kumpulanEvidence) {
                if ($request->hasFile($namaInputFile)) {
                    $file = $request->file($namaInputFile);
                    $namaFile = time() . '_' . $namaInputFile . '_' . $file->getClientOriginalName();
                    
                    $fileMetadata = new DriveFile(['name' => $namaFile, 'parents' => [$folderId]]);
                    $content = file_get_contents($file->getRealPath());
                    
                    // 1. Eksekusi Upload
                    $uploadResult = $service->files->create($fileMetadata, [
                        'data' => $content, 'mimeType' => $file->getMimeType(),
                        'uploadType' => 'multipart', 'fields' => 'id'
                    ]);
                    
                    $fileId = $uploadResult->getId();

                    // 2. --- TAMBAHAN KODE BARU (BUKA KUNCI KEAMANAN FILE) ---
                    // Ubah izin file menjadi "Anyone with the link can view" agar thumbnail bisa dimuat
                    $permission = new \Google\Service\Drive\Permission([
                        'type' => 'anyone',
                        'role' => 'reader'
                    ]);
                    $service->permissions->create($fileId, $permission);
                    // --------------------------------------------------------
                    
                    // 3. Masukkan ke keranjang evidence
                    $kumpulanEvidence[] = [
                        'keterangan' => $keterangan,
                        'filename'   => $namaFile,
                        'file_id'    => $fileId,
                        'link_drive' => 'https://drive.google.com/file/d/' . $fileId . '/view'
                    ];
                }
            };

            // Jalankan upload satu per satu beserta keterangannya
            $uploadKeDrive('ev_alat_studio', 'Alat Studio & Master');
            $uploadKeDrive('ev_jaringan', 'Pengecekan Jaringan');
            $uploadKeDrive('ev_jalur_av', 'Jalur Audio & Video');
            $uploadKeDrive('pra_ev_kendala', 'Evidence Kendala (Pra-Siaran)');

            // Simpan ke database
            LaporanUtama::create([
                'tanggal_tugas'   => $request->tanggal_tugas,
                'nama_petugas'    => $request->nama_petugas,
                'pdu_nama'        => $request->pdu_nama,
                'tx_petugas_nama' => $request->tx_petugas_nama,
                'pra_kendala'     => $request->pra_kendala,
                'pra_ket_kendala' => $request->pra_ket_kendala,
                'kru_lengkap'     => $request->kru_lengkap,
                'kesimpulan'      => $request->kesimpulan,
                'evidence'        => $kumpulanEvidence, // Disimpan sebagai 1 paket JSON
            ]);

            return back()->with('success', 'HORE! Laporan Induk berhasil disimpan dalam format baru!');

        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $evidences = LaporanUtama::latest()->get();
        return view('index', compact('evidences'));
    }

    public function destroy($id)
    {
        try {
            $laporan = LaporanUtama::findOrFail($id);

            // 1. Siapkan Kunci Google Drive
            $client = new Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));
            $service = new Drive($client);

            // 2. Bongkar keranjang JSON dan hapus file fisiknya satu per satu di Drive
            if (is_array($laporan->evidence)) {
                foreach ($laporan->evidence as $ev) {
                    if (isset($ev['file_id'])) {
                        try {
                            // Tembak file berdasarkan ID aslinya
                            $service->files->delete($ev['file_id']);
                        } catch (\Exception $e) {
                            // Jika file di Drive sudah terhapus manual, abaikan dan lanjut ke file berikutnya
                            continue; 
                        }
                    }
                }
            }

            // 3. Hapus catatan laporan dari database
            $laporan->delete();

            return back()->with('success', 'Laporan dan SEMUA file Evidence di Google Drive berhasil dihapus bersih!');

        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL menghapus: ' . $e->getMessage());
        }
    }

    // DOWNLOAD SEKARANG BERUBAH MENJADI GENERATOR PDF!
    public function download($id)
    {
        $laporan = LaporanUtama::findOrFail($id);
        
        // Konfigurasi PDF agar mengizinkan render image dari URL eksternal (Google Drive)
        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                  ->loadView('pdf_resume', compact('laporan'));
        
        return $pdf->download('Resume_Laporan_TD_' . $laporan->nama_petugas . '.pdf');
    }
}