<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Models\PengaturanKantor;

Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/absensi', [AbsensiController::class, 'simpanAjax']);

Route::get('/lokasi-kantor', function () {
    $kantor = PengaturanKantor::first();
    return response()->json([
        'latitude' => $kantor->latitude ?? 0,
        'longitude' => $kantor->longitude ?? 0,
        'radius' => $kantor->radius ?? 0,
    ]);
});
