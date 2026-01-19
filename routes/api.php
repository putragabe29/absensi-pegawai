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
*/Route::post('/cek-radius', function (\Illuminate\Http\Request $request) {
    $kantor = \App\Models\PengaturanKantor::first();

    if (!$kantor) {
        return response()->json(['error' => 'Lokasi kantor belum diatur'], 500);
    }

    $lat1 = deg2rad($request->latitude);
    $lon1 = deg2rad($request->longitude);
    $lat2 = deg2rad($kantor->latitude);
    $lon2 = deg2rad($kantor->longitude);

    $R = 6371000;
    $jarak = $R * acos(
        cos($lat1) * cos($lat2) * cos($lon2 - $lon1) +
        sin($lat1) * sin($lat2)
    );

    return response()->json([
        'dalam_radius' => $jarak <= $kantor->radius,
        'jarak' => round($jarak),
        'radius' => $kantor->radius
    ]);
});


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
