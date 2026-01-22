@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<div class="card p-4">

<h4 class="mb-1">Form Absensi Pegawai</h4>
<div class="text-muted mb-3">{{ date('l, d F Y') }}</div>

{{-- STATUS --}}
<div class="mb-3">
@if($absenMasuk && $absenPulang)
<div class="alert alert-success">
✅ Sudah absen MASUK ({{ $absenMasuk->jam }}) & PULANG ({{ $absenPulang->jam }})
</div>
@elseif($absenMasuk)
<div class="alert alert-info">
✅ Sudah absen MASUK ({{ $absenMasuk->jam }}) <br>
⏳ Silakan absen PULANG
</div>
@else
<div class="alert alert-warning">
❌ Belum absensi hari ini
</div>
@endif
</div>

<form id="formAbsensi" enctype="multipart/form-data">
@csrf

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
id="foto"
name="foto"
accept="image/*"
capture="environment"
class="form-control"
required>
<small class="text-danger">❗ Harus dari kamera</small>
</div>

<div id="map" style="height:240px;border-radius:10px"></div>

<div class="mt-3 p-3 border rounded">
<div id="lokasi-info">📡 Mengambil lokasi...</div>
<div id="radius-info" class="fw-bold"></div>
<div id="jarak-info" class="small text-muted"></div>
</div>

<button id="btnSubmit" class="btn btn-primary w-100 mt-3"
@if($absenMasuk && $absenPulang) disabled @endif>
💼 Kirim Absensi
</button>

</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let kantor, map, markerUser, circleRadius, gpsReady=false;

/* AMBIL LOKASI KANTOR */
fetch('/api/lokasi-kantor')
.then(r=>r.json())
.then(d=>{kantor=d;initMap();startGPS();})
.catch(()=>Swal.fire('Error','Gagal ambil lokasi kantor','error'));

/* MAP */
function initMap(){
map=L.map('map').setView([kantor.latitude,kantor.longitude],17);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.marker([kantor.latitude,kantor.longitude]).addTo(map).bindPopup('🏢 Kantor');
circleRadius=L.circle([kantor.latitude,kantor.longitude],{
radius:kantor.radius,color:'green',fillOpacity:0.15}).addTo(map);
setTimeout(()=>map.invalidateSize(),500);
}

/* GPS */
navigator.geolocation.watchPosition(pos=>{
gpsReady=true;
latitude.value=pos.coords.latitude;
longitude.value=pos.coords.longitude;

if(!markerUser){
markerUser=L.marker([pos.coords.latitude,pos.coords.longitude]).addTo(map);
}else{
markerUser.setLatLng([pos.coords.latitude,pos.coords.longitude]);
}

const jarak=hitungJarak(
pos.coords.latitude,pos.coords.longitude,
kantor.latitude,kantor.longitude
);

lokasi-info.innerText=`📍 ${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
jarak-info.innerText=`📏 Jarak ${Math.round(jarak)} meter`;

if(jarak<=kantor.radius){
radius-info.innerHTML='🟢 Dalam radius absensi';
radius-info.className='text-success fw-bold';
btnSubmit.disabled=false;
}else{
radius-info.innerHTML='🔴 Di luar radius absensi';
radius-info.className='text-danger fw-bold';
btnSubmit.disabled=true;
}
},()=>Swal.fire('Error','Aktifkan GPS','error'),{enableHighAccuracy:true});

/* HITUNG JARAK */
function hitungJarak(a,b,c,d){
const R=6371000;
const dLat=(c-a)*Math.PI/180;
const dLon=(d-b)*Math.PI/180;
const x=Math.sin(dLat/2)**2+
Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*
Math.sin(dLon/2)**2;
return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x));
}

/* SUBMIT */
formAbsensi.addEventListener('submit',async e=>{
e.preventDefault();

if(!gpsReady){
Swal.fire('Tunggu','Lokasi belum siap','warning');return;
}

if(!foto.files.length){
Swal.fire('Gagal','Foto wajib dari kamera','warning');return;
}

Swal.fire({title:'Mengirim...',didOpen:()=>Swal.showLoading(),allowOutsideClick:false});

try{
const res=await fetch('{{ route("absensi.store") }}',{
method:'POST',
headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},
body:new FormData(formAbsensi)
});

const data=await res.json();
Swal.close();

if(data.success){
Swal.fire('Berhasil',data.message+' ('+data.jam+')','success')
.then(()=>location.reload());
}else{
Swal.fire('Gagal',data.message,'warning');
}
}catch{
Swal.close();
Swal.fire('Error','Server tidak merespon','error');
}
});
</script>
@endsection
