<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    /* =====================================================
     * HALAMAN FORM ABSENSI
     * ===================================================== */
    public function index()
    {
        $pegawaiId = auth()->user()->id;
        $hariIni   = Carbon::today()->toDateString();

        $absenMasuk = Absensi::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Pulang')
            ->first();

        return view('absensi.form', compact('absenMasuk', 'absenPulang'));
    }

    /* =====================================================
     * API SIMPAN ABSENSI (WEB + WEBVIEW)
     * ===================================================== */
    public function simpanAjax(Request $request)
    {
        try {
            $request->validate([
                'tipe'      => 'required|in:Masuk,Pulang',
                'foto'      => 'required|image|mimes:jpg,jpeg,png',
                'latitude'  => 'required',
                'longitude' => 'required',
            ]);

            $pegawaiId = auth()->user()->id;
            $hariIni   = Carbon::today()->toDateString();
            $jam       = Carbon::now()->format('H:i:s');

            // ❌ CEK DOUBLE ABSEN
            $sudahAda = Absensi::where('pegawai_id', $pegawaiId)
                ->where('tanggal', $hariIni)
                ->where('tipe', $request->tipe)
                ->exists();

            if ($sudahAda) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda sudah absen {$request->tipe} hari ini"
                ], 422);
            }

            // 📍 AMBIL DATA KANTOR
            $kantor = PengaturanKantor::first();
            if (!$kantor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi kantor belum diatur admin'
                ], 500);
            }

            // 📏 HITUNG JARAK (HAVERSINE)
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $kantor->latitude,
                $kantor->longitude
            );

            if ($jarak > $kantor->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar radius absensi'
                ], 403);
            }

            // 📸 SIMPAN FOTO
            $fotoPath = $request->file('foto')
                ->store('absensi', 'public');

            // 💾 SIMPAN DATABASE
            Absensi::create([
                'pegawai_id' => $pegawaiId,
                'tanggal'    => $hariIni,
                'jam'        => $jam,
                'foto'       => $fotoPath,
                'latitude'   => $request->latitude,
                'longitude'  => $request->longitude,
                'jarak'      => round($jarak),
                'status'     => 'Hadir',
                'tipe'       => $request->tipe,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Absensi {$request->tipe} berhasil",
                'jam'     => $jam
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /* =====================================================
     * HITUNG JARAK (METER)
     * ===================================================== */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}