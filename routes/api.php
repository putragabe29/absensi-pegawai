<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Models\PengaturanKantor;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route API diletakkan di sini. File ini akan otomatis memakai
| middleware "api" sesuai konfigurasi Laravel.
|--------------------------------------------------------------------------
*/

// ===============================
// LOGIN API
// ===============================
Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

// ===============================
// ABSENSI API (Android / WebView)
// ===============================
Route::post('/absensi', [AbsensiController::class, 'simpanAjax'])->name('api.absensi');

// ===============================
// GET LOKASI KANTOR
// ===============================
Route::get('/lokasi-kantor', function () {
    $kantor = PengaturanKantor::first();

    return response()->json([
        'latitude'  => $kantor->latitude ?? 0,
        'longitude' => $kantor->longitude ?? 0,
        'radius'    => $kantor->radius ?? 0,
    ]);
})->name('api.lokasi.kantor');
