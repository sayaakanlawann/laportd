<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('monitoring')->group(function () {
    // 1. Berdasarkan tanggal tugas (default: hari ini, opsional: ?tanggal=DDMMYY)
    Route::get('/tugas', [MonitoringController::class, 'getByTanggalTugas']);

    // 2. Berdasarkan rentang tanggal tugas (?mulai=DDMMYY&akhir=DDMMYY)
    Route::get('/rentang', [MonitoringController::class, 'getByRentangTanggal']);

    // 3. Berdasarkan waktu input / submit (default: hari ini, opsional: ?tanggal=DDMMYY)
    Route::get('/submit', [MonitoringController::class, 'getByWaktuSubmit']);
});