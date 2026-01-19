<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /* ======================================================
       1. HALAMAN FORM ABSENSI
    =======================================================*/
    public function index()
    {
        $pegawaiId = Auth::id();
        $today = Carbon::today();

        $absenMasuk = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $today)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $today)
            ->where('tipe', 'Pulang')
            ->first();

        $kantor = PengaturanKantor::first();

        return view('absensi.form', compact(
            'absenMasuk',
            'absenPulang',
            'kantor'
        ));
    }

    /* ======================================================
       2. RIWAYAT ABSENSI
    =======================================================*/
    public function riwayatPegawai()
    {
        $riwayat = Absensi::where('pegawai_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        return view('absensi.riwayat', compact('riwayat'));
    }

    /* ======================================================
       3. SIMPAN ABSENSI (AJAX / API / WEBVIEW)
    =======================================================*/
    public function simpanAjax(Request $request)
    {
       $request->validate([
        'nip' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'tipe' => 'required|in:Masuk,Pulang',
        'foto' => 'required|image|max:4096',
    ]);

    $pegawai = \App\Models\Pegawai::where('nip', $request->nip)->first();
    if (!$pegawai) {
        return response()->json(['success'=>false,'message'=>'Pegawai tidak ditemukan'],404);
    }

    $foto = $request->file('foto');
    if (!str_starts_with($foto->getMimeType(),'image/')) {
        return response()->json(['success'=>false,'message'=>'File bukan gambar'],400);
    }

    $fotoPath = $foto->store('foto_absen','public');

    $absen = \App\Models\Absensi::create([
        'pegawai_id'=>$pegawai->id,
        'tanggal'=>date('Y-m-d'),
        'jam'=>date('H:i:s'),
        'foto'=>$fotoPath,
        'latitude'=>$request->latitude,
        'longitude'=>$request->longitude,
        'jarak'=>0,
        'status'=>'Hadir',
        'tipe'=>$request->tipe,
    ]);

    return response()->json([
        'success'=>true,
        'message'=>'Absensi berhasil',
        'jam'=>$absen->jam
    ]);
    }

    /* ======================================================
       4. HITUNG JARAK (METER)
    =======================================================*/
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // meter

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        return $R * acos(
            cos($lat1) * cos($lat2) * cos($lon2 - $lon1) +
            sin($lat1) * sin($lat2)
        );
    }
}
