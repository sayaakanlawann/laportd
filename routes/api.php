<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 👇 TAMBAHKAN JALUR PELAYAN UNTUK PUTRA DI SINI 👇
// URL yang sudah ada (berdasarkan tanggal_tugas)
Route::get('/monitoring-hari-ini', [MonitoringController::class, 'getShiftHariIni']);
Route::get('/monitoring-custom', [MonitoringController::class, 'getLaporanCustom']);

// 🔥 URL BARU: Berdasarkan waktu input (created_at)
Route::get('/monitoring-dibuat-pada', [MonitoringController::class, 'getLaporanByCreatedAt']);