<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Models\PengaturanKantor;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route API (TANPA CSRF, TANPA SESSION)
|--------------------------------------------------------------------------
*/

// ===============================
// LOGIN API (ANDROID / AJAX)
// ===============================
Route::post('/login', [AuthController::class, 'apiLogin']);

// ===============================
// ABSENSI API (SELFIE WAJIB)
// ===============================
Route::post('/absensi', [AbsensiController::class, 'simpanAjax']);

// ===============================
// LOKASI KANTOR (RADIUS)
// ===============================
Route::get('/lokasi-kantor', function () {
    $kantor = PengaturanKantor::first();

    return response()->json([
        'latitude'  => $kantor->latitude ?? 0,
        'longitude' => $kantor->longitude ?? 0,
        'radius'    => $kantor->radius ?? 0,
    ]);
});
