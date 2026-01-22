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

Route::post('/login', [AuthController::class, 'apiLogin']);

Route::post('/absensi', [AbsensiController::class, 'simpanAjax'])
    ->withoutMiddleware([
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
    ]);

Route::get('/lokasi-kantor', function () {
    $kantor = PengaturanKantor::first();

    return response()->json([
        'latitude'  => (float) $kantor->latitude,
        'longitude' => (float) $kantor->longitude,
        'radius'    => (int) $kantor->radius,
    ]);
});
