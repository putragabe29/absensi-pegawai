<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Models\PengaturanKantor;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// LOGIN API
Route::post('/login', [AuthController::class, 'apiLogin'])
    ->name('api.login');

// ABSENSI API (WAJIB LOGIN)
Route::middleware('auth')->post('/absensi', [AbsensiController::class, 'store'])
    ->name('api.absensi');

// LOKASI KANTOR
Route::get('/lokasi-kantor', function () {
    $kantor = PengaturanKantor::first();

    return response()->json([
        'latitude'  => (float) ($kantor->latitude ?? 0),
        'longitude' => (float) ($kantor->longitude ?? 0),
        'radius'    => (int)   ($kantor->radius ?? 0),
    ]);
});
