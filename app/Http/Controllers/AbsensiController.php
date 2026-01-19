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
      try {
        $request->validate([
            'nip' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'tipe' => 'required|in:Masuk,Pulang',
            'foto' => 'required|image|max:5120',
        ]);

        $pegawai = \App\Models\Pegawai::where('nip', $request->nip)->first();
        if (!$pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }

        // ❌ BLOK FOTO GALERI
        $foto = $request->file('foto');
        $exif = null;

if (function_exists('exif_read_data')) {
    $exif = @exif_read_data($foto->getPathname());
}

if (!$exif || empty($exif['Make'])) {
    return response()->json([
        'success' => false,
        'message' => 'Foto harus diambil langsung dari kamera (bukan galeri)'
    ], 422);
}

        $fotoPath = $foto->store('foto_absen', 'public');

        $absen = \App\Models\Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal'    => now()->toDateString(),
            'jam'        => now()->format('H:i:s'),
            'foto'       => $fotoPath,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => 0,
            'status'     => 'Hadir',
            'tipe'       => $request->tipe,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . $request->tipe . ' berhasil',
            'jam' => $absen->jam
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
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
