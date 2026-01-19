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
let kantor, map, markerUser, circle;

fetch('/api/lokasi-kantor')
  .then(r => r.json())
  .then(d => {
    kantor = d;
    map = L.map('map').setView([d.latitude, d.longitude], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([d.latitude, d.longitude]).addTo(map).bindPopup('Kantor');
    circle = L.circle([d.latitude, d.longitude], {
        radius: d.radius, color:'green', fillOpacity:0.15
    }).addTo(map);
  });

function hitungJarak(a,b,c,d){
    const R=6371000;
    const dLat=(c-a)*Math.PI/180;
    const dLon=(d-b)*Math.PI/180;
    const x=Math.sin(dLat/2)**2+
      Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*
      Math.sin(dLon/2)**2;
    return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x));
}

navigator.geolocation.watchPosition(pos=>{
    const lat=pos.coords.latitude;
    const lng=pos.coords.longitude;
    latitude.value=lat;
    longitude.value=lng;

    if(!markerUser){
        markerUser=L.marker([lat,lng]).addTo(map);
    } else {
        markerUser.setLatLng([lat,lng]);
    }

    const jarak=hitungJarak(lat,lng,kantor.latitude,kantor.longitude);
    lokasi-info.innerText=`📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    jarak-info.innerText=`📏 ${Math.round(jarak)} meter`;

    if(jarak<=kantor.radius){
        radius-info.innerHTML='🟢 Dalam radius absensi';
        btnSubmit.disabled=false;
        circle.setStyle({color:'green'});
    } else {
        radius-info.innerHTML='🔴 Di luar radius absensi';
        btnSubmit.disabled=true;
        circle.setStyle({color:'red'});
    }
});

formAbsensi.addEventListener('submit', async e=>{
    e.preventDefault();
    btnSubmit.disabled=true;

    Swal.fire({title:'Mengirim...',didOpen:()=>Swal.showLoading()});

    try{
        const res=await fetch('/api/absensi',{
            method:'POST',
            body:new FormData(formAbsensi)
        });
        const data=JSON.parse(await res.text());
        Swal.close();

        if(data.success){
            Swal.fire('Berhasil',data.message+' '+data.jam,'success')
              .then(()=>location.reload());
        } else {
            btnSubmit.disabled=false;
            Swal.fire('Gagal',data.message,'warning');
        }
    }catch{
        btnSubmit.disabled=false;
        Swal.fire('Error','Gagal menghubungi server','error');
    }
});
</script>
@endsection
