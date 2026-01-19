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
        /* ================= VALIDASI DASAR ================= */
        $request->validate([
            'nip'       => 'required',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'tipe'      => 'required|in:Masuk,Pulang',
            'foto'      => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        /* ================= AMBIL PEGAWAI ================= */
        $pegawai = Pegawai::where('nip', $request->nip)->first();

        if (!$pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }

        /* ================= CEK ABSENSI HARI INI ================= */
        $today = Carbon::today();

        if ($request->tipe === 'Masuk') {
            $sudahMasuk = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->where('tipe', 'Masuk')
                ->exists();

            if ($sudahMasuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen MASUK hari ini'
                ], 409);
            }
        }

        if ($request->tipe === 'Pulang') {
            $sudahPulang = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->where('tipe', 'Pulang')
                ->exists();

            if ($sudahPulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen PULANG hari ini'
                ], 409);
            }
        }

        /* ================= VALIDASI LOKASI ================= */
        $kantor = PengaturanKantor::first();

        if (!$kantor) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi kantor belum diatur'
            ], 500);
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
                'message' => 'Anda berada di luar radius absensi',
                'jarak'   => round($jarak)
            ], 403);
        }

        /* ================= SIMPAN FOTO ================= */
        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        /* ================= SIMPAN ABSENSI ================= */
        $absen = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal'    => $today->format('Y-m-d'),
            'jam'        => Carbon::now()->format('H:i:s'),
            'tipe'       => $request->tipe,
            'status'     => 'Hadir',
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => round($jarak),
            'foto'       => $fotoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . strtoupper($request->tipe) . ' berhasil',
            'jam'     => $absen->jam,
            'jarak'   => round($jarak)
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
