<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /* ======================================================
       1. RIWAYAT ABSENSI PEGAWAI
    =======================================================*/
    public function riwayatPegawai()
    {
        $riwayat = Absensi::where('pegawai_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'asc')
            ->get();

        return view('absensi.riwayat', compact('riwayat'));
    }

    /* ======================================================
       2. FORM ABSENSI
    =======================================================*/
    public function index()
    {
        $pegawaiId = Auth::id();
        $today = date('Y-m-d');

        $absenMasuk = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $today)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $today)
            ->where('tipe', 'Pulang')
            ->first();

        $pengaturan = PengaturanKantor::first();
        $reminderMessage = null;

        if ($pengaturan) {
            $now = date('H:i');

            if (!$absenMasuk &&
                $now >= $pengaturan->jam_masuk_mulai &&
                $now <= $pengaturan->jam_masuk_selesai) {
                $reminderMessage = "⏰ Jangan lupa absensi MASUK.";
            }

            if ($absenMasuk && !$absenPulang &&
                $now >= $pengaturan->jam_pulang_mulai &&
                $now <= $pengaturan->jam_pulang_selesai) {
                $reminderMessage = "⏰ Jangan lupa absensi PULANG.";
            }
        }

        return view('absensi.form', compact(
            'absenMasuk',
            'absenPulang',
            'reminderMessage'
        ));
    }

    /* ======================================================
       3. SIMPAN ABSENSI (WEB)
    =======================================================*/
    public function simpan(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'tipe'      => 'required|in:Masuk,Pulang',
            'foto'      => 'required|image|mimes:jpg,jpeg,png'
        ]);

        $kantor = PengaturanKantor::first();
        if (!$kantor) {
            return back()->with('error', 'Lokasi kantor belum diatur.');
        }

        /* VALIDASI JAM */
        $now = date('H:i');
        if ($request->tipe === 'Masuk' &&
            ($now < $kantor->jam_masuk_mulai || $now > $kantor->jam_masuk_selesai)) {
            return back()->with('error', '⛔ Di luar jam absensi masuk.');
        }

        if ($request->tipe === 'Pulang' &&
            ($now < $kantor->jam_pulang_mulai || $now > $kantor->jam_pulang_selesai)) {
            return back()->with('error', '⛔ Di luar jam absensi pulang.');
        }

        /* VALIDASI JARAK */
        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $kantor->latitude,
            $kantor->longitude
        );

        if ($jarak > $kantor->radius) {
            return back()->with('error', '❌ Di luar radius kantor.');
        }

        /* SIMPAN FOTO */
        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        Absensi::create([
            'pegawai_id' => Auth::id(),
            'tanggal'    => date('Y-m-d'),
            'jam'        => date('H:i:s'),
            'foto'       => $fotoPath,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => $jarak,
            'status'     => 'Hadir',
            'tipe'       => $request->tipe,
        ]);

        return back()->with('success', '✅ Absensi berhasil.');
    }

    /* ======================================================
       4. SIMPAN ABSENSI AJAX (ANDROID WEBVIEW)
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

        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        $absen = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal'    => date('Y-m-d'),
            'jam'        => date('H:i:s'),
            'foto'       => $fotoPath,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => 0,
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
       5. HITUNG JARAK
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
