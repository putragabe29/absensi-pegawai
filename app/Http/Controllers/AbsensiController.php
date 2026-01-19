<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\PengaturanKantor;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function simpanAjax(Request $request)
    {
        try {
            $request->validate([
                'nip' => 'required',
                'tipe' => 'required|in:Masuk,Pulang',
                'latitude' => 'required',
                'longitude' => 'required',
                'foto' => 'required|image|max:4096',
            ]);

            $pegawai = Pegawai::where('nip', $request->nip)->first();
            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai tidak ditemukan'
                ], 404);
            }

            $kantor = PengaturanKantor::first();
            if (!$kantor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi kantor belum diset'
                ], 400);
            }

            // ================= HITUNG JARAK =================
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $kantor->latitude,
                $kantor->longitude
            );

            if ($jarak > $kantor->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda di luar radius absensi'
                ], 403);
            }

            // ================= CEK DUPLIKASI =================
            $today = Carbon::today()->toDateString();
            $cek = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->where('tipe', $request->tipe)
                ->first();

            if ($cek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen ' . strtoupper($request->tipe) . ' hari ini'
                ], 409);
            }

            // ================= SIMPAN FOTO =================
            $fotoPath = $request->file('foto')->store('foto_absensi', 'public');

            $absen = Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => $today,
                'jam' => Carbon::now()->format('H:i:s'),
                'tipe' => $request->tipe,
                'foto' => $fotoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jarak' => round($jarak),
                'status' => 'Hadir'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($request->tipe) . ' berhasil',
                'jam' => $absen->jam
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        return $R * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
