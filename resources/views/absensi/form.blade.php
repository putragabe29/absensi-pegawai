@extends('layouts.app')

@section('content')
<div class="card p-4">

    <h4 class="mb-2">Form Absensi Pegawai</h4>
    <div class="mb-3 text-muted">
        {{ date('l, d F Y') }}
    </div>

    {{-- ================= STATUS HARI INI ================= --}}
    @if($absenMasuk && $absenPulang)
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Absensi Lengkap',
                text: 'Anda sudah absen MASUK dan PULANG hari ini.',
                confirmButtonColor: '#F47C20'
            });
        </script>
    @elseif($absenMasuk)
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Sudah Absen Masuk',
                text: 'Anda sudah melakukan absensi MASUK hari ini.',
                confirmButtonColor: '#F47C20'
            });
        </script>
    @endif

    {{-- ================= FORM ================= --}}
    <form id="formAbsensi" enctype="multipart/form-data">

        {{-- kirim nip untuk API --}}
        <input type="hidden" name="nip" value="{{ auth()->user()->nip }}">

        {{-- JENIS ABSENSI --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Absensi</label>
            <select name="tipe" class="form-select"
                {{ $absenMasuk && $absenPulang ? 'disabled' : '' }}
                required>
                <option value="Masuk" {{ $absenMasuk ? 'disabled' : '' }}>
                    Masuk
                </option>
                <option value="Pulang" {{ !$absenMasuk || $absenPulang ? 'disabled' : '' }}>
                    Pulang
                </option>
            </select>
        </div>

        {{-- FOTO SELFIE --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Foto Selfie (Kamera)</label>
            <input type="file"
                   name="foto"
                   id="foto"
                   class="form-control"
                   accept="image/*"
                   capture="user"
                   {{ $absenMasuk && $absenPulang ? 'disabled' : '' }}
                   required>
            <small class="text-muted">
                ❗ Wajib foto langsung dari kamera (bukan galeri)
            </small>
        </div>

        {{-- INFO LOKASI --}}
        <div class="mb-3 p-2 border rounded">
            <span id="lokasi-info">📡 Mengambil lokasi...</span>
        </div>

        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        {{-- TOMBOL --}}
        <button type="submit"
                id="btnSubmit"
                class="btn btn-primary w-100"
                {{ $absenMasuk && $absenPulang ? 'disabled' : '' }}>
            💼 Kirim Absensi
        </button>

        @if($absenMasuk && $absenPulang)
            <div class="text-center text-success mt-2 fw-semibold">
                ✅ Absensi hari ini sudah lengkap
            </div>
        @endif
    </form>
</div>

{{-- ================= SCRIPT ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ================= AMBIL LOKASI ================= */
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('latitude').value = pos.coords.latitude;
            document.getElementById('longitude').value = pos.coords.longitude;
            document.getElementById('lokasi-info').innerText =
                '📍 Lokasi: ' +
                pos.coords.latitude.toFixed(5) + ', ' +
                pos.coords.longitude.toFixed(5);
        },
        () => {
            document.getElementById('lokasi-info').innerText =
                '❌ Gagal mengambil lokasi. Aktifkan GPS.';
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
} else {
    document.getElementById('lokasi-info').innerText =
        '❌ Browser tidak mendukung GPS';
}

/* ================= SUBMIT ABSENSI ================= */
document.getElementById('formAbsensi').addEventListener('submit', async function(e) {
    e.preventDefault();

    const foto = document.getElementById('foto').files[0];
    const lat  = document.getElementById('latitude').value;
    const lng  = document.getElementById('longitude').value;

    if (!lat || !lng) {
        Swal.fire('Gagal', 'Lokasi belum terdeteksi', 'warning');
        return;
    }

    if (!foto) {
        Swal.fire('Gagal', 'Foto selfie wajib diambil dari kamera', 'warning');
        return;
    }

    // ❌ CEGAH GALERI
    if (!foto.type.startsWith('image/')) {
        Swal.fire(
            'Tidak Valid',
            'Foto harus diambil langsung dari kamera (bukan galeri)',
            'error'
        );
        return;
    }

    const formData = new FormData(this);

    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch('/api/absensi', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        Swal.close();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Absensi Berhasil',
                text: data.message,
                confirmButtonColor: '#F47C20'
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'warning');
        }
    } catch {
        Swal.fire('Error', 'Gagal menghubungi server', 'error');
    }
});
</script>
@endsection
