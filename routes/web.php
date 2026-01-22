<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\PegawaiController;

/*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/login'));

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PEGAWAI (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // HALAMAN ABSENSI
    Route::get('/absensi', [AbsensiController::class, 'index'])
        ->name('absensi.index');

    // ✅ ROUTE FINAL SUBMIT ABSENSI (INI YANG DIPAKAI FORM)
    Route::post('/absensi/store', [AbsensiController::class, 'store'])
        ->name('absensi.store');

    // RIWAYAT & KALENDER
    Route::get('/riwayat', [AbsensiController::class, 'riwayatPegawai'])
        ->name('pegawai.riwayat');

    Route::get('/kalender', [AbsensiController::class, 'kalenderSaya'])
        ->name('pegawai.kalender');

    // IZIN
    Route::get('/izin', [IzinController::class, 'index']);
    Route::get('/izin/create', [IzinController::class, 'create']);
    Route::post('/izin', [IzinController::class, 'store'])
        ->name('izin.store');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/lokasi', [AdminController::class, 'lokasi'])
        ->name('admin.lokasi');

    Route::post('/lokasi', [AdminController::class, 'updateLokasi'])
        ->name('admin.lokasi.update');

    Route::get('/pegawai', [PegawaiController::class, 'index'])
        ->name('admin.pegawai');

    Route::post('/pegawai', [PegawaiController::class, 'store'])
        ->name('admin.pegawai.store');

    Route::post('/pegawai/update-password', [PegawaiController::class, 'updatePassword'])
        ->name('admin.pegawai.updatePassword');

    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])
        ->name('admin.pegawai.destroy');
});
