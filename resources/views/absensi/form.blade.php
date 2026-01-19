@extends('layouts.app')

@section('content')
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<div class="card p-4">

    <h4 class="mb-2">Form Absensi Pegawai</h4>
    <div class="mb-3 text-muted">{{ date('l, d F Y') }}</div>

    <form id="formAbsensi" enctype="multipart/form-data">
        <input type="hidden" name="nip" value="{{ auth()->user()->nip }}">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label class="fw-semibold">Jenis Absensi</label>
            <select name="tipe" class="form-select">
                <option value="Masuk">Masuk</option>
                <option value="Pulang">Pulang</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="fw-semibold">Foto Selfie (kamera)</label>
            <input type="file"
                   id="foto"
                   name="foto"
                   class="form-control"
                   accept="image/*"
                   capture="user"
                   required>
            <small class="text-danger">❗ Tidak boleh dari galeri</small>
        </div>

        {{-- MAP MINI --}}
        <div class="mb-3">
            <div id="map" style="height:240px;border-radius:10px"></div>
        </div>

        {{-- STATUS --}}
        <div class="mb-3 p-3 border rounded">
            <div id="lokasi-info">📡 Mengambil lokasi...</div>
            <div id="radius-info" class="fw-semibold mt-1"></div>
            <div id="jarak-info" class="small text-muted"></div>
        </div>

        <button id="btnSubmit" class="btn btn-primary w-100" disabled>
            💼 Kirim Absensi
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let kantor = null;
let map, markerUser, markerKantor, circleRadius;

/* ===== AMBIL DATA KANTOR ===== */
fetch('/api/lokasi-kantor')
    .then(r => r.json())
    .then(d => {
        kantor = d;
        initMap();
    });

/* ===== MAP INIT ===== */
function initMap() {
    map = L.map('map').setView(
        [kantor.latitude, kantor.longitude],
        17
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    markerKantor = L.marker([kantor.latitude, kantor.longitude])
        .addTo(map)
        .bindPopup('🏢 Kantor')
        .openPopup();

    circleRadius = L.circle(
        [kantor.latitude, kantor.longitude],
        {
            radius: kantor.radius,
            color: 'green',
            fillOpacity: 0.15
        }
    ).addTo(map);
}

/* ===== HITUNG JARAK ===== */
function hitungJarak(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLon = (lon2-lon1)*Math.PI/180;
    const a =
        Math.sin(dLat/2)**2 +
        Math.cos(lat1*Math.PI/180) *
        Math.cos(lat2*Math.PI/180) *
        Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

/* ===== UPDATE POSISI ===== */
function updateUser(lat, lng) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    if (!markerUser) {
        markerUser = L.marker([lat, lng]).addTo(map)
            .bindPopup('📍 Posisi Anda');
    } else {
        markerUser.setLatLng([lat, lng]);
    }

    const jarak = hitungJarak(
        lat, lng,
        kantor.latitude, kantor.longitude
    );

    document.getElementById('lokasi-info').innerText =
        `📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

    document.getElementById('jarak-info').innerText =
        `📏 Jarak: ${Math.round(jarak)} meter (radius ${kantor.radius} m)`;

    const info = document.getElementById('radius-info');
    const btn  = document.getElementById('btnSubmit');

    if (jarak <= kantor.radius) {
        info.innerHTML = '🟢 <span class="text-success">Dalam radius absensi</span>';
        circleRadius.setStyle({ color:'green' });
        btn.disabled = false;
    } else {
        info.innerHTML = '🔴 <span class="text-danger">Di luar radius absensi</span>';
        circleRadius.setStyle({ color:'red' });
        btn.disabled = true;
    }
}

/* ===== GPS REALTIME ===== */
navigator.geolocation.watchPosition(
    pos => updateUser(
        pos.coords.latitude,
        pos.coords.longitude
    ),
    () => {
        Swal.fire('GPS Error','Aktifkan lokasi','error');
    },
    { enableHighAccuracy:true }
);

/* ===== SUBMIT ===== */
document.getElementById('formAbsensi').addEventListener('submit', async e => {
    e.preventDefault();

    if (!document.getElementById('foto').files.length) {
        Swal.fire('Gagal','Foto wajib dari kamera','warning');
        return;
    }

    Swal.fire({ title:'Mengirim...', didOpen:()=>Swal.showLoading() });

    try {
        const res = await fetch('/api/absensi', {
            method:'POST',
            body:new FormData(e.target)
        });
        const data = await res.json();
        Swal.close();

        if (data.success) {
            Swal.fire('Berhasil',data.message,'success')
                .then(()=>location.reload());
        } else {
            Swal.fire('Gagal',data.message,'warning');
        }
    } catch {
        Swal.fire('Error','Gagal menghubungi server','error');
    }
});
</script>
@endsection
