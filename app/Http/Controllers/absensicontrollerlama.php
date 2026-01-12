<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /* ======================================================
       1. RIWAYAT ABSENSI PEGAWAI (MENU RIWAYAT)
    =======================================================*/
    public function riwayatPegawai()
    {
        $riwayat = Absensi::where('pegawai_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'asc')
            ->get();

        return view('absensi.riwayat', compact('riwayat'));
    }

    // (opsional, kalau mau dipakai admin, biarkan saja)
    public function riwayat()
    {
        $riwayat = Absensi::where('pegawai_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        return view('absensi.riwayat', compact('riwayat'));
    }

    /* ======================================================
       2. HALAMAN FORM ABSENSI + AUTO REMINDER
    =======================================================*/
    public function index()
    {
        $pegawai_id = Auth::id();
        $tanggalHariIni = date('Y-m-d');

        $absenMasuk = Absensi::where('pegawai_id', $pegawai_id)
            ->whereDate('tanggal', $tanggalHariIni)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawai_id)
            ->whereDate('tanggal', $tanggalHariIni)
            ->where('tipe', 'Pulang')
            ->first();

        // 🔔 AUTO REMINDER
        $pengaturan = PengaturanKantor::first();
        $reminderMessage = null;

        if ($pengaturan) {
            $now = date('H:i');

            // Belum absen MASUK, tapi sudah masuk jam absensi masuk
            if (!$absenMasuk &&
                $now >= $pengaturan->jam_masuk_mulai &&
                $now <= $pengaturan->jam_masuk_selesai
            ) {
                $reminderMessage = "Jangan lupa absensi MASUK antara "
                    . $pengaturan->jam_masuk_mulai . " - " . $pengaturan->jam_masuk_selesai . " WIB.";
            }

            // Sudah absen masuk tapi belum pulang, dan sudah masuk jam pulang
            if ($absenMasuk && !$absenPulang &&
                $now >= $pengaturan->jam_pulang_mulai &&
                $now <= $pengaturan->jam_pulang_selesai
            ) {
                $reminderMessage = "Jangan lupa absensi PULANG antara "
                    . $pengaturan->jam_pulang_mulai . " - " . $pengaturan->jam_pulang_selesai . " WIB.";
            }
        }

        return view('absensi.form', compact('absenMasuk', 'absenPulang', 'reminderMessage'));
    }

    /* ======================================================
       3. SIMPAN ABSENSI VIA FORM (WEB)
    =======================================================*/
    public function simpan(Request $request)
    {
        $kantor = PengaturanKantor::first();

        if (!$kantor) {
            return back()->with('error', 'Data lokasi kantor belum diatur oleh admin.');
        }

        // Validasi jam absensi
        $jamSekarang = date('H:i');
        if ($request->tipe === 'Masuk') {
            if ($jamSekarang < $kantor->jam_masuk_mulai || $jamSekarang > $kantor->jam_masuk_selesai) {
                return back()->with('error', '⛔ Absen Masuk hanya boleh antara '
                    . $kantor->jam_masuk_mulai . ' - ' . $kantor->jam_masuk_selesai);
            }
        }

        if ($request->tipe === 'Pulang') {
            if ($jamSekarang < $kantor->jam_pulang_mulai || $jamSekarang > $kantor->jam_pulang_selesai) {
                return back()->with('error', '⛔ Absen Pulang hanya boleh antara '
                    . $kantor->jam_pulang_mulai . ' - ' . $kantor->jam_pulang_selesai);
            }
        }

        // Validasi lokasi (radius)
        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $kantor->latitude,
            $kantor->longitude
        );

        if ($jarak > $kantor->radius) {
            return back()->with('error', '❌ Anda di luar area kantor! (' . round($jarak, 2) . ' m)');
        }

        // ✅ Cek FAKE GPS (kecepatan tidak wajar)
        $pegawaiId = Auth::id();
        $lastAbsen = Absensi::where('pegawai_id', $pegawaiId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($this->isFakeGps($lastAbsen, $request->latitude, $request->longitude)) {
            return back()->with(
                'error',
                '🚫 Sistem mendeteksi pergerakan lokasi yang tidak wajar (kemungkinan Fake GPS).'
            );
        }

        // Upload foto
        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        // Cek absen masuk & pulang
        $absenMasuk = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', date('Y-m-d'))
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', date('Y-m-d'))
            ->where('tipe', 'Pulang')
            ->first();

        if ($request->tipe === 'Pulang' && !$absenMasuk) {
            return back()->with('error', '⚠️ Anda belum absen masuk!');
        }

        if ($request->tipe === 'Pulang' && $absenPulang) {
            return back()->with('error', '⚠️ Anda sudah absen pulang hari ini!');
        }

        if ($request->tipe === 'Masuk' && $absenMasuk) {
            return back()->with('error', '⚠️ Anda sudah absen masuk hari ini!');
        }

        Absensi::create([
            'pegawai_id' => $pegawaiId,
            'tanggal'    => date('Y-m-d'),
            'jam'        => date('H:i:s'),
            'foto'       => $fotoPath,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => $jarak,
            'status'     => 'Hadir',
            'tipe'       => $request->tipe,
        ]);

        return back()->with('success', "✅ Absensi {$request->tipe} berhasil! Jarak Anda: " . round($jarak, 2) . " m");
    }

    /* ======================================================
       4. SIMPAN ABSENSI AJAX (ANDROID / WEBVIEW)
    =======================================================*/
    public function simpanAjax(Request $request)
    {
        $kantor = PengaturanKantor::first();

        if (!$kantor) {
            return response()->json(['error' => 'Pengaturan kantor belum diatur.'], 400);
        }

        // Validasi jam absensi
        $jamNow = date('H:i');
        $tipe = $request->tipe ?? null;

        if ($tipe === 'Masuk') {
            if ($jamNow < $kantor->jam_masuk_mulai || $jamNow > $kantor->jam_masuk_selesai) {
                return response()->json([
                    'error' => "Absen Masuk hanya boleh antara {$kantor->jam_masuk_mulai} - {$kantor->jam_masuk_selesai}"
                ], 403);
            }
        }

        if ($tipe === 'Pulang') {
            if ($jamNow < $kantor->jam_pulang_mulai || $jamNow > $kantor->jam_pulang_selesai) {
                return response()->json([
                    'error' => "Absen Pulang hanya boleh antara {$kantor->jam_pulang_mulai} - {$kantor->jam_pulang_selesai}"
                ], 403);
            }
        }

        // Validasi jarak
        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $kantor->latitude,
            $kantor->longitude
        );

        if ($jarak > $kantor->radius) {
            return response()->json([
                'error' => 'Anda di luar radius kantor! (' . round($jarak, 2) . ' m)'
            ], 403);
        }

        // Cek absensi hari ini
        $pegawai_id = Auth::id();
        $tanggal = date('Y-m-d');

        $absenMasuk = Absensi::where('pegawai_id', $pegawai_id)
            ->whereDate('tanggal', $tanggal)
            ->where('tipe', 'Masuk')
            ->first();

        $absenPulang = Absensi::where('pegawai_id', $pegawai_id)
            ->whereDate('tanggal', $tanggal)
            ->where('tipe', 'Pulang')
            ->first();

        if ($absenMasuk && $absenPulang) {
            return response()->json(['error' => 'Anda sudah absen masuk & pulang hari ini.'], 403);
        }

        // Tentukan tipe otomatis (kalau tidak diinput dari android)
        if (!$tipe) {
            $tipe = $absenMasuk ? 'Pulang' : 'Masuk';
        }

        // ✅ Cek FAKE GPS berdasarkan absensi terakhir
        $lastAbsen = Absensi::where('pegawai_id', $pegawai_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($this->isFakeGps($lastAbsen, $request->latitude, $request->longitude)) {
            return response()->json([
                'error' => '🚫 Sistem mendeteksi pergerakan lokasi tidak wajar (kemungkinan Fake GPS).'
            ], 403);
        }

        // Upload foto
        if (!$request->hasFile('foto')) {
            return response()->json(['error' => 'Foto tidak terkirim!'], 400);
        }

        $fotoPath = $request->file('foto')->store('foto_absen', 'public');

        // Simpan
        $absensi = Absensi::create([
            'pegawai_id' => $pegawai_id,
            'tanggal'    => $tanggal,
            'jam'        => date('H:i:s'),
            'foto'       => $fotoPath,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'jarak'      => $jarak,
            'status'     => 'Hadir',
            'tipe'       => $tipe,
        ]);

        return response()->json([
            'success' => true,
            'pesan'   => "Absensi $tipe berhasil! Jarak Anda: " . round($jarak, 2) . ' m',
            'jam'     => $absensi->jam
        ], 200);
    }

    /* ======================================================
       5. HITUNG JARAK (Haversine)
    =======================================================*/
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /* ======================================================
       6. CEK FAKE GPS (PERGERAKAN TIDAK WAJAR)
    =======================================================*/
    private function isFakeGps($lastAbsen, $latNow, $lngNow): bool
    {
        // Kalau belum pernah absen, tidak bisa dibandingkan
        if (!$lastAbsen || !$lastAbsen->latitude || !$lastAbsen->longitude) {
            return false;
        }

        $jarakMeter = $this->hitungJarak(
            $lastAbsen->latitude,
            $lastAbsen->longitude,
            $latNow,
            $lngNow
        );

        $lastTime = Carbon::parse($lastAbsen->created_at);
        $now = Carbon::now();

        $selisihMenit = max(1, $lastTime->diffInMinutes($now)); // minimal 1 menit biar nggak bagi 0
        $kecepatanKmPerJam = ($jarakMeter / 1000) / ($selisihMenit / 60);

        // Misal: kalau kecepatan > 150 km/jam kita anggap tidak wajar
        return $kecepatanKmPerJam > 150;
    }
}
