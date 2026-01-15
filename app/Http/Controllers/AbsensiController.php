<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\PengaturanKantor;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /* =========================
       HALAMAN FORM ABSENSI
    ==========================*/
    public function index()
    {
        $pegawaiId = Auth::id();
        $today = date('Y-m-d');

        $absenMasuk = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $today)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $today)
            ->where('tipe', 'Pulang')
            ->first();

        return view('absensi.form', compact('absenMasuk', 'absenPulang'));
    }

    /* =========================
       SIMPAN ABSENSI (API – TANPA SESSION)
    ==========================*/
    public function simpanAjax(Request $request)
    {
        $request->validate([
            'nip'       => 'required',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'tipe'      => 'required|in:Masuk,Pulang',
            'foto'      => 'required|image|mimes:jpg,jpeg,png'
        ]);

        $pegawai = Pegawai::where('nip', $request->nip)->first();
        if (!$pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }

        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        $absen = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal'    => date('Y-m-d'),
            'jam'        => date('H:i:s'),
            'foto'       => $fotoPath,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => 0,
            'status'     => 'Hadir',
            'tipe'       => $request->tipe,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil',
            'jam'     => $absen->jam
        ]);
    }
}
