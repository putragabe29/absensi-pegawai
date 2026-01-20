@extends('layouts.app')

@section('content')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<div class="card p-4">

    <h4 class="mb-1">Form Absensi Pegawai</h4>
    <div class="text-muted mb-3">{{ date('l, d F Y') }}</div>

    {{-- STATUS --}}
    <div class="mb-3">
        @if($absenMasuk && $absenPulang)
            <div class="alert alert-success">
                ✅ Sudah absen MASUK ({{ $absenMasuk->jam }})
                & PULANG ({{ $absenPulang->jam }})
            </div>
        @elseif($absenMasuk)
            <div class="alert alert-info">
                ✅ Sudah absen MASUK ({{ $absenMasuk->jam }})
            </div>
        @else
            <div class="alert alert-warning">
                ❌ Belum absensi hari ini
            </div>
        @endif
    </div>

    <form id="formAbsensi" enctype="multipart/form-data">
        <input type="hidden" name="nip" value="{{ auth()->user()->nip }}">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label>Jenis Absensi</label>
            <select name="tipe" class="form-select"
                @if($absenMasuk && $absenPulang) disabled @endif>
                @if(!$absenMasuk)
                    <option value="Masuk">Masuk</option>
                @elseif(!$absenPulang)
                    <option value="Pulang">Pulang</option>
                @endif
            </select>
        </div>

        <div class="mb-3">
            <label>Foto Selfie (kamera)</label>
            <input type="file"
                   name="foto"
                   accept="image/*"
                   capture="environment"
                   class="form-control"
                   required>
            <small class="text-danger">❗ Harus dari kamera</small>
        </div>

        <div id="map" style="height:240px;border-radius:10px"></div>

        <div class="mt-2 p-3 border rounded">
            <div id="lokasi-info">📡 Mengambil lokasi...</div>
            <div id="radius-info" class="fw-semibold"></div>
            <div id="jarak-info" class="small text-muted"></div>
        </div>

        <button id="btnSubmit"
                class="btn btn-primary w-100 mt-3"
                @if($absenMasuk && $absenPulang) disabled @endif>
            💼 Kirim Absensi
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let kantor = null;
let map, markerUser, circleRadius;
let gpsWatcher = null;

/* ================= AMBIL DATA KANTOR ================= */
fetch('/api/lokasi-kantor')
    .then(res => res.json())
    .then(data => {
        kantor = data;
        initMap();
        startGPS();
    })
    .catch(() => {
        Swal.fire('Error','Gagal mengambil lokasi kantor','error');
    });

/* ================= INIT MAP ================= */
function initMap() {
    map = L.map('map').setView(
        [kantor.latitude, kantor.longitude], 17
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    L.marker([kantor.latitude, kantor.longitude])
        .addTo(map)
        .bindPopup('🏢 Kantor');

    circleRadius = L.circle(
        [kantor.latitude, kantor.longitude],
        {
            radius: kantor.radius,
            color: 'green',
            fillOpacity: 0.15
        }
    ).addTo(map);

    setTimeout(() => {
        map.invalidateSize();
    }, 500);
}

/* ================= GPS REALTIME ================= */
function startGPS() {
    if (!navigator.geolocation) {
        Swal.fire('Error','Browser tidak mendukung GPS','error');
        return;
    }

    gpsWatcher = navigator.geolocation.watchPosition(
        posisi => {
            const lat = posisi.coords.latitude;
            const lng = posisi.coords.longitude;

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            if (!markerUser) {
                markerUser = L.marker([lat, lng]).addTo(map);
            } else {
                markerUser.setLatLng([lat, lng]);
            }

            const jarak = hitungJarak(
                lat, lng,
                kantor.latitude,
                kantor.longitude
            );

            document.getElementById('lokasi-info').innerText =
                `📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

            document.getElementById('jarak-info').innerText =
                `📏 Jarak: ${Math.round(jarak)} meter`;

            const info = document.getElementById('radius-info');
            const btn  = document.getElementById('btnSubmit');

            if (jarak <= kantor.radius) {
                info.innerHTML = '🟢 Dalam radius absensi';
                info.className = 'text-success fw-bold';
                circleRadius.setStyle({ color:'green' });
                btn.disabled = false;
            } else {
                info.innerHTML = '🔴 Di luar radius absensi';
                info.className = 'text-danger fw-bold';
                circleRadius.setStyle({ color:'red' });
                btn.disabled = true;
            }
        },
        err => {
            Swal.fire('Error','Aktifkan GPS & izinkan lokasi','error');
        },
        {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 15000
        }
    );
}

/* ================= HITUNG JARAK ================= */
function hitungJarak(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;

    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) ** 2;

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

/* ================= SUBMIT ABSENSI ================= */
document.getElementById('formAbsensi').addEventListener('submit', async e => {
    e.preventDefault();

    Swal.fire({
        title: 'Mengirim absensi...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch('/api/absensi', {
            method: 'POST',
            body: new FormData(e.target)
        });

        const data = await res.json();
        Swal.close();

        if (data.success) {
            Swal.fire('Berhasil', data.message + ' ('+data.jam+')','success')
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message,'warning');
        }
    } catch (err) {
        Swal.close();
        Swal.fire('Error','Server tidak merespon','error');
    }
});
</script>
@endsection
