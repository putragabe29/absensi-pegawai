@extends('layouts.app')

@section('content')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<div class="card p-4">

    <h4 class="mb-1">Form Absensi Pegawai</h4>
    <div class="text-muted mb-3">{{ date('l, d F Y') }}</div>

    {{-- ================= STATUS HARI INI ================= --}}
    <div class="mb-3">
        @if($absenMasuk && $absenPulang)
            <div class="alert alert-success">
                ✅ Anda sudah absen <b>MASUK</b> ({{ $absenMasuk->jam }})
                dan <b>PULANG</b> ({{ $absenPulang->jam }})
            </div>
        @elseif($absenMasuk)
            <div class="alert alert-info">
                ✅ Anda sudah absen <b>MASUK</b> pukul {{ $absenMasuk->jam }} <br>
                ⏳ Silakan lakukan absensi <b>PULANG</b>
            </div>
        @else
            <div class="alert alert-warning">
                ❌ Anda belum melakukan absensi hari ini
            </div>
        @endif
    </div>

    <form id="formAbsensi" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label class="fw-semibold">Jenis Absensi</label>
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
            <label class="fw-semibold">Foto Selfie (kamera)</label>
            <input type="file"
                   id="foto"
                   name="foto"
                   accept="image/*"
                   capture="environment"
                   class="form-control"
                   required>
            <small class="text-danger">❗ Harus diambil dari kamera</small>
        </div>

        {{-- MAP --}}
        <div class="mb-3">
            <div id="map" style="height:240px;border-radius:10px"></div>
        </div>

        {{-- INFO --}}
        <div class="mb-3 p-3 border rounded">
            <div id="lokasi-info">📡 Mengambil lokasi...</div>
            <div id="radius-info" class="fw-semibold mt-1"></div>
            <div id="jarak-info" class="small text-muted"></div>
        </div>

        <button id="btnSubmit"
                class="btn btn-primary w-100"
                @if($absenMasuk && $absenPulang) disabled @endif>
            💼 Kirim Absensi
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let kantor, map, markerUser, circleRadius;
let gpsReady = false;

// ambil element sekali
const elLat   = document.getElementById('latitude');
const elLng   = document.getElementById('longitude');
const elLok   = document.getElementById('lokasi-info');
const elRad   = document.getElementById('radius-info');
const elJarak = document.getElementById('jarak-info');
const btn     = document.getElementById('btnSubmit');
const foto    = document.getElementById('foto');
const form    = document.getElementById('formAbsensi');

/* ================= POPUP STATUS ================= */
document.addEventListener('DOMContentLoaded', () => {
    @if($absenMasuk && $absenPulang)
        Swal.fire('Info','Anda sudah absen masuk dan pulang hari ini','info');
    @elseif($absenMasuk)
        Swal.fire('Info','Anda sudah absen masuk hari ini','info');
    @endif
});

/* ================= AMBIL LOKASI KANTOR ================= */
fetch('/api/lokasi-kantor')
  .then(r => r.json())
  .then(d => {
    kantor = d;
    initMap();
    startGPS();
  })
  .catch(() => Swal.fire('Error','Gagal mengambil lokasi kantor','error'));

/* ================= MAP ================= */
function initMap() {
    map = L.map('map').setView([kantor.latitude, kantor.longitude], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    L.marker([kantor.latitude, kantor.longitude])
      .addTo(map)
      .bindPopup('🏢 Kantor');

    circleRadius = L.circle(
        [kantor.latitude, kantor.longitude],
        { radius: kantor.radius, color:'green', fillOpacity:0.15 }
    ).addTo(map);

    setTimeout(() => map.invalidateSize(), 500);
}

/* ================= GPS ================= */
function startGPS() {
    if (!navigator.geolocation) {
        Swal.fire('Error','Browser tidak mendukung GPS','error');
        return;
    }

    navigator.geolocation.watchPosition(
        pos => {
            gpsReady = true;

            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            elLat.value = lat;
            elLng.value = lng;

            if (!markerUser) {
                markerUser = L.marker([lat,lng]).addTo(map);
            } else {
                markerUser.setLatLng([lat,lng]);
            }

            const jarak = hitungJarak(lat,lng,kantor.latitude,kantor.longitude);

            elLok.innerText   = `📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            elJarak.innerText = `📏 Jarak: ${Math.round(jarak)} meter`;

            if (jarak <= kantor.radius) {
                elRad.innerHTML = '🟢 Dalam radius absensi';
                elRad.className = 'text-success fw-bold';
                circleRadius.setStyle({color:'green'});
                btn.disabled = false;
            } else {
                elRad.innerHTML = '🔴 Di luar radius absensi';
                elRad.className = 'text-danger fw-bold';
                circleRadius.setStyle({color:'red'});
                btn.disabled = true;
            }
        },
        () => Swal.fire('Error','Aktifkan GPS & izinkan lokasi','error'),
        { enableHighAccuracy:true }
    );
}

/* ================= JARAK ================= */
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

/* ================= SUBMIT ================= */
form.addEventListener('submit', async e => {
    e.preventDefault();

    if (!gpsReady) {
        Swal.fire('Tunggu','Lokasi belum siap','warning');
        return;
    }

    if (!foto.files.length) {
        Swal.fire('Gagal','Foto wajib diambil dari kamera','warning');
        return;
    }

    Swal.fire({title:'Mengirim...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});

    try {
        const res = await fetch('{{ route("absensi.store") }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: new FormData(form)
        });

        const data = await res.json();
        Swal.close();

        if (data.success) {
            Swal.fire('Berhasil',data.message+' ('+data.jam+')','success')
                .then(()=>location.reload());
        } else {
            Swal.fire('Gagal',data.message,'warning');
        }
    } catch {
        Swal.close();
        Swal.fire('Error','Server tidak merespon','error');
    }
});
</script>
@endsection
