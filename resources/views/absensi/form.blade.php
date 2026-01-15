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
        {{-- KIRIM NIP --}}
        <input type="hidden" name="nip" value="{{ auth()->user()->nip }}">

        <div class="mb-3">
            <label>Jenis Absensi</label>
            <select name="tipe" class="form-select" required>
                <option value="Masuk">Masuk</option>
                <option value="Pulang">Pulang</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Foto Selfie</label>
            <input type="file" name="foto" class="form-control"
                   accept="image/*" capture="environment" required>
        </div>

        {{-- INFO LOKASI --}}
        <div class="mb-3 p-2 border rounded">
            <span id="lokasi-info">📡 Mengambil lokasi...</span>
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
/* === AMBIL LOKASI === */
navigator.geolocation.getCurrentPosition(
    pos => {
        document.getElementById('latitude').value = pos.coords.latitude;
        document.getElementById('longitude').value = pos.coords.longitude;
        document.getElementById('lokasi-info').innerText =
            '📍 Lokasi: ' + pos.coords.latitude.toFixed(5) +
            ', ' + pos.coords.longitude.toFixed(5);
    },
    () => {
        document.getElementById('lokasi-info').innerText =
            '❌ Gagal mengambil lokasi. Aktifkan GPS.';
    },
    { enableHighAccuracy: true, timeout: 15000 }
);

/* === SUBMIT API === */
document.getElementById('formAbsensi').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const res = await fetch('/api/absensi', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            Swal.fire('Berhasil', data.message + ' (' + data.jam + ')', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'warning');
        }
    } catch (e) {
        Swal.fire('Error', 'Server tidak bisa dihubungi', 'error');
    }
});
</script>
@endsection
