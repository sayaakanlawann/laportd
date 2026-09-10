<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 👇 TAMBAHKAN JALUR PELAYAN UNTUK PUTRA DI SINI 👇
Route::get('/monitoring-hari-ini', [MonitoringController::class, 'getShiftHariIni']);