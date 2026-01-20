<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /* ======================================================
     | HALAMAN FORM ABSENSI
     ====================================================== */
    public function index()
    {
        $userId = auth()->id();
        $hariIni = Carbon::today()->toDateString();

        $absenMasuk = Absensi::where('user_id', $userId)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('user_id', $userId)
            ->where('tanggal', $hariIni)
            ->where('tipe', 'Pulang')
            ->first();

        return view('absensi.form', compact(
            'absenMasuk',
            'absenPulang'
        ));
    }

    /* ======================================================
     | SIMPAN ABSENSI (API / AJAX / WEBVIEW)
     ====================================================== */
    public function simpanAjax(Request $request)
    {
        try {

            /* ================= VALIDASI ================= */
            $request->validate([
                'tipe'      => 'required|in:Masuk,Pulang',
                'latitude'  => 'required',
                'longitude' => 'required',
                'foto'      => 'required|image|max:4096',
            ]);

            $user = auth()->user();
            $hariIni = Carbon::today()->toDateString();
            $jamSekarang = Carbon::now()->format('H:i:s');

            /* ================= CEK DUPLIKASI ================= */
            $cek = Absensi::where('user_id', $user->id)
                ->where('tanggal', $hariIni)
                ->where('tipe', $request->tipe)
                ->first();

            if ($cek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen ' . strtoupper($request->tipe) . ' hari ini'
                ]);
            }

            /* ================= VALIDASI LOKASI ================= */
            $kantor = PengaturanKantor::first();
            if (!$kantor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi kantor belum diatur'
                ]);
            }

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
                ]);
            }

            /* ================= VALIDASI FOTO (ANTI GALERI) ================= */
            $exif = @exif_read_data($request->file('foto')->getRealPath());
            if (!$exif || empty($exif['Make'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto harus diambil dari kamera'
                ]);
            }

            /* ================= SIMPAN FOTO ================= */
            $pathFoto = $request->file('foto')
                ->store('absensi', 'public');

            /* ================= SIMPAN ABSENSI ================= */
            Absensi::create([
                'user_id'   => $user->id,
                'tanggal'   => $hariIni,
                'jam'       => $jamSekarang,
                'tipe'      => $request->tipe,
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
                'foto'      => $pathFoto,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($request->tipe) . ' berhasil',
                'jam'     => $jamSekarang
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /* ======================================================
     | HITUNG JARAK (HAVERSINE)
     ====================================================== */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
