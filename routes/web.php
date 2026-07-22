<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\DeveloperOnly;
Route::get('/', function () {
    return view('portal');
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
// --- PINTU LOGIN UNIVERSAL ---
// Memaksa siapa pun yang mengakses /login portal untuk masuk ke login Filament
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');
// --- RUTE PUBLIK (LOBI & LOGIN) ---
Route::get('/', function () {
    return redirect('/admin/login');
});
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Rute untuk menampilkan form upload
Route::middleware(['auth'])->group(function () {
    Route::get('/upload', [\App\Http\Controllers\EvidenceController::class, 'create']);
    Route::post('/upload', [\App\Http\Controllers\EvidenceController::class, 'store']);
    
    // (Opsional) Jika Admin ingin bisa mengedit laporan dari front-end
    Route::get('/laporan/{id}/edit', [\App\Http\Controllers\EvidenceController::class, 'edit']);
    Route::put('/laporan/{id}', [\App\Http\Controllers\EvidenceController::class, 'update']);
}); 

Route::middleware(['auth'])->group(function () {
    
    Route::delete('/evidence/{id}', [\App\Http\Controllers\EvidenceController::class, 'destroy']);
    Route::get('/evidence/{id}/download', [\App\Http\Controllers\EvidenceController::class, 'download']);
    Route::get('/export-excel', [\App\Http\Controllers\EvidenceController::class, 'exportExcel']);
    
    // Rute Master Data Front-End Abang
    // ... (masukkan sisa rute POST/DELETE master-data di dalam blok ini)
});
// Rute untuk menerima data saat tombol upload ditekan
Route::post('/upload', [EvidenceController::class, 'store']);

// Rute untuk melihat daftar dokumen


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

// Rute untuk menghapus master data
Route::delete('/master-data/delete-petugas/{id}', [MasterDataController::class, 'deletePetugas']);
Route::delete('/master-data/delete-program/{id}', [MasterDataController::class, 'deleteProgram']);

// Rute untuk menampilkan form edit
Route::get('/laporan/{id}/edit', [\App\Http\Controllers\EvidenceController::class, 'edit']);

// Rute untuk memproses update data (wajib pakai PUT/PATCH)
Route::put('/laporan/{id}', [\App\Http\Controllers\EvidenceController::class, 'update']);

Route::middleware(['auth', DeveloperOnly::class])->group(function () {
    Route::get('/evidence', [EvidenceController::class, 'index']);
    
    Route::get('/master-data', [MasterDataController::class, 'index']); 
    // Masukkan rute debugging lainnya di dalam kurung kurawal ini
});