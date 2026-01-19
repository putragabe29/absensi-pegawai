@extends('layouts.app')

@section('content')
<div class="card p-4">

    <h4 class="mb-2">Form Absensi Pegawai</h4>
    <div class="mb-3 text-muted">
        {{ date('l, d F Y') }}
    </div>

    {{-- STATUS HARI INI --}}
    <div class="mb-3">
        @if($absenMasuk && $absenPulang)
            <div class="alert alert-success">
                ✅ Sudah absen MASUK ({{ $absenMasuk->jam }}) dan PULANG ({{ $absenPulang->jam }})
            </div>
        @elseif($absenMasuk)
            <div class="alert alert-warning">
                ✅ Sudah absen MASUK ({{ $absenMasuk->jam }}), belum absen PULANG
            </div>
        @else
            <div class="alert alert-danger">
                ❌ Belum melakukan absensi hari ini
            </div>
        @endif
    </div>

    <form id="formAbsensi" enctype="multipart/form-data">
        @csrf

        {{-- NIP --}}
        <input type="hidden" name="nip" value="{{ auth()->user()->nip }}">

        <div class="mb-3">
            <label>Jenis Absensi</label>
            <select name="tipe" class="form-select" required>
                <option value="Masuk">Masuk</option>
                <option value="Pulang">Pulang</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Foto Selfie (WAJIB Kamera)</label>
            <input type="file"
                   name="foto"
                   class="form-control"
                   accept="image/*"
                   capture="user"
                   required>
            <small class="text-muted">
                Foto harus diambil langsung dari kamera depan.
            </small>
        </div>

        {{-- INFO LOKASI --}}
        <div class="mb-3 p-2 border rounded">
            <div id="lokasi-info">📡 Mengambil lokasi...</div>
            <div id="radius-info" class="fw-semibold mt-1">⏳ Mengecek radius...</div>
        </div>

        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <button class="btn btn-primary w-100" type="submit">
            Kirim Absensi
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let kantor = null;

// ===============================
// HITUNG JARAK (HAVERSINE)
// ===============================
function hitungJarak(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2-lat1) * Math.PI/180;
    const dLon = (lon2-lon1) * Math.PI/180;

    const a =
        Math.sin(dLat/2) ** 2 +
        Math.cos(lat1 * Math.PI/180) *
        Math.cos(lat2 * Math.PI/180) *
        Math.sin(dLon/2) ** 2;

    return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

// ===============================
// AMBIL DATA KANTOR DULU
// ===============================
async function ambilDataKantor() {
    try {
        const res = await fetch('/api/lokasi-kantor', {
            headers: { 'Accept': 'application/json' }
        });
        kantor = await res.json();
        ambilLokasiUser(); // ⬅️ LANJUT KE GPS
    } catch {
        document.getElementById('radius-info').innerHTML =
            '❌ Gagal memuat data kantor';
    }
}

// ===============================
// AMBIL LOKASI USER
// ===============================
function ambilLokasiUser() {
    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            latitude.value = lat;
            longitude.value = lng;

            document.getElementById('lokasi-info').innerHTML =
                `📍 Lokasi Anda: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

            const jarak = hitungJarak(
                lat, lng,
                kantor.latitude, kantor.longitude
            );

            if (jarak <= kantor.radius) {
                document.getElementById('radius-info').innerHTML =
                    `🟢 Anda DI DALAM radius absensi (${Math.round(jarak)} m)`;
                document.getElementById('radius-info').style.color = 'green';
            } else {
                document.getElementById('radius-info').innerHTML =
                    `🔴 Anda DI LUAR radius absensi (${Math.round(jarak)} m)`;
                document.getElementById('radius-info').style.color = 'red';
            }
        },
        () => {
            document.getElementById('lokasi-info').innerHTML =
                '❌ Gagal mengambil lokasi. Aktifkan GPS & izin lokasi.';
            document.getElementById('radius-info').innerHTML = '';
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
}

// ===============================
// JALANKAN
// ===============================
ambilDataKantor();
</script>
@endsection
