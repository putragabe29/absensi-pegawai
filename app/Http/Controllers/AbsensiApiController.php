<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AbsensiApiController extends Controller
{
    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipe' => 'required|in:Masuk,Pulang',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|max:5120' // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
        }

        // Ambil data lokasi kantor
        $kantor = PengaturanKantor::first();
        if (!$kantor) {
            return response()->json(['status'=>'error','message'=>'Lokasi kantor belum diatur'], 400);
        }

        $latUser = (float)$request->latitude;
        $lngUser = (float)$request->longitude;
        $latKantor = (float)$kantor->latitude;
        $lngKantor = (float)$kantor->longitude;
        $radius = (float)$kantor->radius; // dalam meter

        // hitung jarak (Haversine)
        $jarak = $this->hitungJarak($latUser, $lngUser, $latKantor, $lngKantor);

        if ($jarak > $radius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda berada di luar radius kantor (' . round($jarak,2) . ' m)'
            ], 403);
        }

        // simpan foto
        $path = $request->file('foto')->store('foto_absen', 'public');

        // Simpan absensi (anda bisa ambil pegawai_id dari token / payload jika pakai auth)
        $absen = Absensi::create([
            'pegawai_id' => $request->input('pegawai_id') ?? null, // jika ada
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'jam' => Carbon::now()->format('H:i:s'),
            'foto' => $path,
            'latitude' => $latUser,
            'longitude' => $lngUser,
            'jarak' => $jarak,
            'status' => 'Hadir',
            'tipe' => $request->tipe,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil',
            'data' => [
                'id' => $absen->id,
                'tipe' => $absen->tipe,
                'jam' => $absen->jam
            ]
        ], 200);
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
