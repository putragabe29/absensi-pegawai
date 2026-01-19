@extends('layouts.app')

@section('content')
<div class="card p-4">

    <h4 class="mb-1">Form Absensi Pegawai</h4>
    <div class="text-muted mb-3">{{ date('l, d F Y') }}</div>

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

    {{-- STATUS RADIUS --}}
    <div id="radius-box" class="p-3 rounded text-white mb-3 d-none">
        <strong id="radius-text"></strong><br>
        <small id="radius-detail"></small>
    </div>

    <form id="formAbsensi" enctype="multipart/form-data">
        <input type="hidden" name="nip" value="{{ auth()->user()->nip }}">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label>Jenis Absensi</label>
            <select name="tipe" class="form-select" required>
                <option value="Masuk">Masuk</option>
                <option value="Pulang">Pulang</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Foto Selfie</label>
            <input type="file"
                   name="foto"
                   class="form-control"
                   accept="image/*"
                   capture="user"
                   required>
        </div>

        <button id="btnAbsen" class="btn btn-primary w-100" type="submit" disabled>
            Kirim Absensi
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================
// AMBIL LOKASI & CEK RADIUS
// ============================
navigator.geolocation.getCurrentPosition(async pos => {

    const lat = pos.coords.latitude
    const lng = pos.coords.longitude

    document.getElementById('latitude').value = lat
    document.getElementById('longitude').value = lng

    const res = await fetch('/api/cek-radius', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ latitude: lat, longitude: lng })
    })

    const data = await res.json()

    const box = document.getElementById('radius-box')
    const text = document.getElementById('radius-text')
    const detail = document.getElementById('radius-detail')
    const btn = document.getElementById('btnAbsen')

    box.classList.remove('d-none')

    if (data.dalam_radius) {
        box.style.background = '#198754' // hijau
        text.innerText = '🟢 Anda berada di dalam radius absensi'
        detail.innerText = `Jarak ${data.jarak} m (radius ${data.radius} m)`
        btn.disabled = false
    } else {
        box.style.background = '#dc3545' // merah
        text.innerText = '🔴 Anda berada di luar radius absensi'
        detail.innerText = `Jarak ${data.jarak} m (radius ${data.radius} m)`
        btn.disabled = true
    }

}, () => {
    Swal.fire('Error', 'GPS tidak aktif / izin lokasi ditolak', 'error')
})

// ============================
// SUBMIT ABSENSI
// ============================
document.getElementById('formAbsensi').addEventListener('submit', async e => {
    e.preventDefault()

    const formData = new FormData(e.target)

    try {
        const res = await fetch('/api/absensi', {
            method: 'POST',
            body: formData
        })

        const data = await res.json()

        if (data.success) {
            Swal.fire('Berhasil', data.message + ' (' + data.jam + ')', 'success')
                .then(() => location.reload())
        } else {
            Swal.fire('Gagal', data.message, 'warning')
        }

    } catch {
        Swal.fire('Error', 'Gagal menghubungi server', 'error')
    }
})
</script>
@endsection
