<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /* ================= HALAMAN ABSENSI ================= */
    public function index()
    {
        $pegawai = Auth::user(); // login pegawai
        $hariIni = Carbon::today()->toDateString();

        $absenMasuk = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Pulang')
            ->first();

        return view('absensi.form', compact(
            'absenMasuk',
            'absenPulang'
        ));
    }

    /* ================= API SIMPAN ABSENSI ================= */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'tipe'      => 'required|in:Masuk,Pulang',
                'latitude'  => 'required',
                'longitude' => 'required',
                'foto'      => 'required|image|max:5120'
            ]);

            $pegawai = Auth::user();
            $hariIni = Carbon::today()->toDateString();

            // ❌ CEGAH DOUBLE ABSENSI
            $sudah = Absensi::where('pegawai_id', $pegawai->id)
                ->where('tanggal', $hariIni)
                ->where('tipe', $request->tipe)
                ->first();

            if ($sudah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen ' . strtoupper($request->tipe)
                ]);
            }

            /* ================= SIMPAN FOTO ================= */
            $fotoPath = $request->file('foto')
                ->store('absensi', 'public');

            /* ================= HITUNG JARAK ================= */
            $kantorLat = config('absensi.latitude', -6.200000);
            $kantorLng = config('absensi.longitude', 106.816666);

            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $kantorLat,
                $kantorLng
            );

            /* ================= SIMPAN DB ================= */
            $absensi = Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal'    => $hariIni,
                'jam'        => now()->format('H:i:s'),
                'tipe'       => $request->tipe,
                'foto'       => $fotoPath,
                'latitude'   => $request->latitude,
                'longitude'  => $request->longitude,
                'jarak'      => round($jarak),
                'status'     => 'Hadir'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($request->tipe) . ' berhasil',
                'jam'     => $absensi->jam
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /* ================= HITUNG JARAK ================= */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) *
             cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;

        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
