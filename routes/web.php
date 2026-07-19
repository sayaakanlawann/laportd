<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\MasterDataController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-drive', function () {
    try {
        Storage::disk('google')->put('tes_dari_laravel.txt', 'Halo Falah! Ini tes koneksi Laravel pakai OAuth.');
        return 'HORE! Berhasil upload ke Google Drive! Bye-bye limit kuota!';
    } catch (\Exception $e) {
        return 'YAH GAGAL: ' . $e->getMessage();
    }
});
Route::get('/debug-drive', function () {
    try {
        $path = storage_path('app/google-credentials.json');
        
        $client = new \Google\Client();
        $client->setAuthConfig($path);
        $client->addScope(\Google\Service\Drive::DRIVE);
        
        $service = new \Google\Service\Drive($client);
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        
        // Kita paksa si robot untuk membaca informasi folder tersebut
        $folder = $service->files->get($folderId);
        
        return "SUKSES! Robot berhasil melihat folder. Nama foldernya adalah: " . $folder->getName();
        
    } catch (\Exception $e) {
        return "BONGKAR ERROR GOOGLE: " . $e->getMessage();
    }
});
Route::get('/debug-upload', function () {
    try {
        // Kita siapkan si Robot seperti biasa
        $path = storage_path('app/google-credentials.json');
        $client = new \Google\Client();
        $client->setAuthConfig($path);
        $client->addScope(\Google\Service\Drive::DRIVE);
        
        $service = new \Google\Service\Drive($client);
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

        // Kita buat file "secara manual" dan langsung tembak ke Google
        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name' => 'tes_langsung_dari_robot.txt',
            'parents' => [$folderId]
        ]);
        
        $content = 'Halo! Ini adalah tes upload langsung tanpa lewat perantara Flysystem.';
        
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'text/plain',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        return "SUKSES BESAR! File berhasil diupload langsung. ID File: " . $file->id;
        
    } catch (\Exception $e) {
        return "ERROR GOOGLE LANGSUNG: " . $e->getMessage();
    }
});

// Rute untuk menampilkan form upload
Route::get('/upload', [EvidenceController::class, 'create']);

// Rute untuk menerima data saat tombol upload ditekan
Route::post('/upload', [EvidenceController::class, 'store']);

// Rute untuk melihat daftar dokumen
Route::get('/evidence', [EvidenceController::class, 'index']);

// Rute untuk menghapus dokumen
Route::delete('/evidence/{id}', [EvidenceController::class, 'destroy']);

// Rute untuk mendownload dokumen
Route::get('/evidence/{id}/download', [EvidenceController::class, 'download']);

Route::get('/export-excel', [App\Http\Controllers\EvidenceController::class, 'exportExcel']);



Route::get('/master-data', [MasterDataController::class, 'index']);
Route::post('/master-data/update-petugas', [MasterDataController::class, 'updatePetugas']);

Route::post('/master-data/update-program', [MasterDataController::class, 'updateProgram']);
Route::post('/master-data/store-petugas', [MasterDataController::class, 'storePetugas']);
Route::post('/master-data/store-program', [MasterDataController::class, 'storeProgram']);