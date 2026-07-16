<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Untuk upload ke Drive
use App\Models\Evidence; // Untuk simpan ke Database

class EvidenceController extends Controller
{
    public function index()
{
    // Ambil semua data dari database, urutkan dari yang terbaru
    $evidences = Evidence::latest()->get();
    
    // Kirim data tersebut ke view bernama 'index'
    return view('index', compact('evidences'));
}

    public function create()
    {
        return view('upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'file'  => 'required|file'
        ]);

        try {
            $file = $request->file('file');
            $namaFile = time() . '_' . $file->getClientOriginalName();

            // 1. Siapkan Kunci Google Langsung (Bypass Flysystem)
            $client = new \Google\Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));
            
            $service = new \Google\Service\Drive($client);

            // 2. Tentukan target Folder ID secara spesifik
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $namaFile,
                'parents' => [$folderId] // Tembak langsung ke brankas asli!
            ]);
            
            // 3. Eksekusi Upload ke Google Drive
            $content = file_get_contents($file->getRealPath());
            $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            // 4. Simpan ke Database
            Evidence::create([
                'judul' => $request->judul,
                'file_path' => $namaFile,
            ]);

            return back()->with('success', 'HORE! Dokumen berhasil mendarat TEPAT di dalam brankas TVRI_Evidence!');

        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // 1. Cari data di database
            $evidence = Evidence::findOrFail($id);

            // 2. Siapkan Kunci Google Langsung
            $client = new \Google\Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));
            
            $service = new \Google\Service\Drive($client);

            // 3. Pencarian Peluru Kendali: Cari filenya di SELURUH Drive berdasarkan namanya yang unik
            // Kita hilangkan batasan folder agar file yang "nyasar" pun tetap terlacak
            $query = "name='" . $evidence->file_path . "' and trashed=false";
            
            $results = $service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);
            
            $files = $results->getFiles();

            // 4. Eksekusi Hapus Fisik
            $pesanTambahan = "";
            if (count($files) > 0) {
                // Jika ketemu, hancurkan!
                $service->files->delete($files[0]->getId());
                $pesanTambahan = " & File di Google Drive berhasil dihapus!";
            } else {
                $pesanTambahan = " (Namun file fisiknya tidak ditemukan di Google Drive).";
            }

            // 5. Hapus catatan dari database
            $evidence->delete();

            return back()->with('success', 'Data dihapus' . $pesanTambahan);

        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL menghapus: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        try {
            // 1. Cari data di database
            $evidence = Evidence::findOrFail($id);

            // 2. Perintahkan Laravel untuk mengambil file dari Google Drive dan mengunduhnya
            return Storage::disk('google')->download($evidence->file_path);

        } catch (\Exception $e) {
            return back()->with('error', 'YAH GAGAL mendownload: ' . $e->getMessage());
        }
    }
}