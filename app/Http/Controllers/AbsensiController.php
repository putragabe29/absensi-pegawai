<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * ===============================
     * HALAMAN FORM ABSENSI
     * ===============================
     */
    public function index()
    {
        $nip = auth()->user()->nip;
        $hariIni = Carbon::today()->toDateString();

        $absenMasuk = Absensi::where('nip', $nip)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('nip', $nip)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Pulang')
            ->first();

        return view('absensi.form', compact(
            'absenMasuk',
            'absenPulang'
        ));
    }

    /**
     * ===============================
     * API SIMPAN ABSENSI (AJAX)
     * ===============================
     */
    public function simpanAjax(Request $request)
    {
        $request->validate([
            'nip'       => 'required',
            'tipe'      => 'required|in:Masuk,Pulang',
            'latitude'  => 'required',
            'longitude' => 'required',
            'foto'      => 'required|image|max:2048'
        ]);

        $hariIni = Carbon::today()->toDateString();

        // ❌ CEGAH DOBEL ABSEN
        $cek = Absensi::where('nip', $request->nip)
            ->where('tanggal', $hariIni)
            ->where('tipe', $request->tipe)
            ->exists();

        if ($cek) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen ' . strtoupper($request->tipe) . ' hari ini'
            ]);
        }

        // ✅ SIMPAN FOTO (TANPA EXIF)
        $path = $request->file('foto')->store('absensi', 'public');

        $absen = Absensi::create([
            'nip'       => $request->nip,
            'tipe'      => $request->tipe,
            'tanggal'   => $hariIni,
            'jam'       => now()->format('H:i:s'),
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'foto'      => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . strtoupper($request->tipe) . ' berhasil',
            'jam'     => $absen->jam
        ]);
    }
}
