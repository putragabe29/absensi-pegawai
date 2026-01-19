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
            <label>Foto Selfie (WAJIB Kamera)</label>
            <input type="file"
                   name="foto"
                   class="form-control"
                   accept="image/*"
                   capture="user"
                   required>
            <small class="text-muted">
                Foto harus diambil langsung dari kamera depan (selfie).
            </small>
        </div>

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
// ===== GPS =====
navigator.geolocation.getCurrentPosition(
    pos => {
        latitude.value = pos.coords.latitude;
        longitude.value = pos.coords.longitude;
        document.getElementById('lokasi-info').innerText =
            '📍 Lokasi: ' + pos.coords.latitude.toFixed(5) + ', ' +
            pos.coords.longitude.toFixed(5);
    },
    () => {
        document.getElementById('lokasi-info').innerText =
            '❌ Gagal mengambil lokasi. Aktifkan GPS.';
    },
    { enableHighAccuracy: true, timeout: 15000 }
);

// ===== ANTI GALERI =====
document.querySelector('input[name="foto"]').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (!file.lastModified || file.lastModified < 1000000000000) {
        Swal.fire('Ditolak', 'Foto wajib dari kamera selfie', 'error');
        this.value = '';
    }
});

// ===== SUBMIT =====
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
    } catch {
        Swal.fire('Error', 'Server tidak bisa dihubungi', 'error');
    }
});
</script>
@endsection
