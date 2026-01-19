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

Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

Route::post('/absensi', [AbsensiController::class, 'simpanAjax'])
    ->name('api.absensi');

Route::get('/lokasi-kantor', function () {
    $kantor = PengaturanKantor::first();

    return response()->json([
        'latitude'  => (float) $kantor->latitude,
        'longitude' => (float) $kantor->longitude,
        'radius'    => (int) $kantor->radius,
    ]);
});
