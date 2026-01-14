<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\PegawaiController;

/*
|--------------------------------------------------------------------------
| REDIRECT DEFAULT
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
Route::get('/webview/login', [AuthController::class, 'webviewLogin'])
    ->name('webview.login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PEGAWAI (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/absensi', [AbsensiController::class, 'index']);
    Route::post('/absensi', [AbsensiController::class, 'simpan'])
        ->name('absen.simpan');

    Route::post('/absensi/ajax', [AbsensiController::class, 'simpanAjax'])
        ->name('absen.simpanAjax');

    Route::get('/riwayat', [AbsensiController::class, 'riwayatPegawai'])
        ->name('pegawai.riwayat');

    Route::get('/kalender', [AbsensiController::class, 'kalenderSaya'])
        ->name('pegawai.kalender');

    Route::get('/izin', [IzinController::class, 'index']);
    Route::get('/izin/create', [IzinController::class, 'create']);
    Route::post('/izin', [IzinController::class, 'store'])
        ->name('izin.store');
});

/*
|--------------------------------------------------------------------------
| ADMIN (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/lokasi', [AdminController::class, 'lokasi'])
        ->name('admin.lokasi');

    Route::post('/lokasi', [AdminController::class, 'updateLokasi'])
        ->name('admin.lokasi.update');

    Route::get('/izin', [IzinController::class, 'adminIndex'])
        ->name('admin.izin');

    Route::post('/izin/{id}', [IzinController::class, 'updateStatus'])
        ->name('admin.izin.update');

    Route::get('/rekap', [AdminController::class, 'rekapBulan'])
        ->name('admin.rekap');

    Route::get('/rekap/pdf', [AdminController::class, 'rekapPDF'])
        ->name('admin.rekap.pdf');

    Route::get('/pegawai', [PegawaiController::class, 'index'])
        ->name('admin.pegawai');

    Route::post('/pegawai', [PegawaiController::class, 'store'])
        ->name('admin.pegawai.store');

    Route::post('/pegawai/update-password', [PegawaiController::class, 'updatePassword'])
        ->name('admin.pegawai.updatePassword');

    Route::get('/kehadiran', [AdminController::class, 'listAbsensi'])
        ->name('admin.kehadiran');

    Route::get('/kehadiran/edit/{id}', [AdminController::class, 'editAbsensi'])
        ->name('admin.kehadiran.edit');

    Route::post('/kehadiran/update/{id}', [AdminController::class, 'updateAbsensi'])
        ->name('admin.kehadiran.update');

    Route::delete('/kehadiran/delete/{id}', [AdminController::class, 'deleteAbsensi'])
        ->name('admin.kehadiran.delete');

    Route::get('/grafik', [AdminController::class, 'grafikKehadiran'])
        ->name('admin.grafik');

    Route::get('/broadcast', [AdminController::class, 'broadcastIndex'])
        ->name('admin.broadcast');

    Route::post('/broadcast', [AdminController::class, 'broadcastStore'])
        ->name('admin.broadcast.store');
});
