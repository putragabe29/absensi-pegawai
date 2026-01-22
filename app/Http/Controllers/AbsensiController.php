<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\PengaturanKantor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /* ================= HALAMAN ABSENSI (WEB) ================= */
    public function index()
    {
        $pegawai = auth()->user();
        $hariIni = Carbon::today()->toDateString();

        $absenMasuk = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Pulang')
            ->first();

        return view('absensi.form', compact('absenMasuk', 'absenPulang'));
    }

    /* ================= API SIMPAN ABSENSI ================= */
    public function store(Request $request)
    {
        try {
            // ================= VALIDASI =================
            $request->validate([
                'nip'       => 'required',
                'tipe'      => 'required|in:Masuk,Pulang',
                'latitude'  => 'required',
                'longitude' => 'required',
                'foto'      => 'required|image|max:5120',
            ]);

            // ================= PEGAWAI (PAKAI NIP, BUKAN AUTH) =================
            $pegawai = Pegawai::where('nip', $request->nip)->first();
            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai tidak ditemukan'
                ], 404);
            }

            $hariIni = Carbon::today()->toDateString();

            // ================= CEK DOUBLE ABSENSI =================
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

            // ================= LOKASI KANTOR =================
            $kantor = PengaturanKantor::first();
            if (!$kantor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi kantor belum diatur'
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
                    'message' => 'Anda berada di luar radius absensi'
                ], 403);
            }

            // ================= SIMPAN FOTO =================
            $fotoPath = $request->file('foto')->store('foto_absensi', 'public');

            // ================= SIMPAN DB =================
            $absensi = Absensi::create([
                'pegawai_id' => $pegawai->id,
                'tanggal'    => $hariIni,
                'jam'        => Carbon::now()->format('H:i:s'),
                'tipe'       => $request->tipe,
                'foto'       => $fotoPath,
                'latitude'   => $request->latitude,
                'longitude'  => $request->longitude,
                'jarak'      => round($jarak),
                'status'     => 'Hadir',
            ]);

            // ================= RETURN JSON (WAJIB) =================
            return response()->json([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($request->tipe) . ' berhasil',
                'jam'     => Carbon::now()->format('H:i')
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
