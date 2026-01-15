<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\PengaturanKantor;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /* ======================================================
       1. HALAMAN FORM ABSENSI (WEB – PAKAI SESSION)
    =======================================================*/
    public function index()
    {
        $pegawaiId = Auth::id();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

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

    /* ======================================================
       2. RIWAYAT ABSENSI PEGAWAI (WEB)
    =======================================================*/
    public function riwayatPegawai()
    {
        $pegawaiId = Auth::id();
        if (!$pegawaiId) {
            abort(403);
        }

        $riwayat = Absensi::where('pegawai_id', $pegawaiId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        return view('absensi.riwayat', compact('riwayat'));
    }

    /* ======================================================
       3. SIMPAN ABSENSI (API – TANPA SESSION & CSRF)
    =======================================================*/
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

        $kantor = PengaturanKantor::first();
        if ($kantor) {
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $kantor->latitude,
                $kantor->longitude
            );

            if ($jarak > $kantor->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Di luar radius kantor'
                ], 403);
            }
        } else {
            $jarak = 0;
        }

        // Cegah double absensi
        $cek = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->where('tipe', $request->tipe)
            ->first();

        if ($cek) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi ' . $request->tipe . ' sudah dilakukan'
            ], 409);
        }

        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        $now = Carbon::now('Asia/Jakarta');

$absen = Absensi::create([
    'pegawai_id' => $pegawai->id,
    'tanggal'    => $now->toDateString(), // YYYY-MM-DD
    'jam'        => $now->toTimeString(), // HH:MM:SS
    'foto'       => $fotoPath,
    'latitude'   => $request->latitude,
    'longitude'  => $request->longitude,
    'jarak'      => $jarak,
    'status'     => 'Hadir',
    'tipe'       => $request->tipe,
]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil',
            'jam'     => $absen->jam
        ]);
        
    }

    /* ======================================================
       4. HITUNG JARAK (METER)
    =======================================================*/
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
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
