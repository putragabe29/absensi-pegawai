@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <div class="page-title">Form Absensi Pegawai</div>
        <div class="page-subtitle">
            {{ date('l, d F Y') }}
        </div>
    </div>
    <span class="badge-soft">
        📍 Lokasi + 📸 Selfie · Real-time
    </span>
</div>

<div class="card-soft">
    {{-- Status hari ini --}}
    <div class="mb-3">
        <h6 class="fw-semibold mb-2">Status Kehadiran Hari Ini</h6>
        @if($absenMasuk && $absenPulang)
            <p class="mb-1">✅ <b>Sudah Absen Masuk</b> pukul {{ $absenMasuk->jam }}</p>
            <p class="mb-0">✅ <b>Sudah Absen Pulang</b> pukul {{ $absenPulang->jam }}</p>
        @elseif($absenMasuk)
            <p class="mb-1">✅ <b>Sudah Absen Masuk</b> pukul {{ $absenMasuk->jam }}</p>
            <p class="mb-0 text-danger">❌ <b>Belum Absen Pulang</b></p>
        @else
            <p class="mb-0 text-danger">❌ <b>Belum ada absensi hari ini.</b></p>
        @endif
    </div>

    {{-- Notifikasi flash dengan SweetAlert --}}
    @if(session('error') || session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: '{{ session("error") ? "error" : "success" }}',
                title: '{{ session("error") ? "Gagal!" : "Berhasil!" }}',
                text: '{{ session("error") ?? session("success") }}',
                confirmButtonColor: '#F47C20'
            });
        </script>
    @endif

    <form id="formAbsensi" enctype="multipart/form-data" class="mt-2">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Jenis Absensi</label>
                <select name="tipe" class="form-select" required>
                    <option value="Masuk">Masuk</option>
                    <option value="Pulang">Pulang</option>
                </select>
                <div class="form-text">
                    Sistem akan mengecek jam kerja sesuai pengaturan admin.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Ambil Foto Selfie</label>
                <input type="file" name="foto" accept="image/*" capture="environment"
                       class="form-control" required>
                <div class="form-text">
                    Kamera wajib aktif, tidak bisa ambil dari galeri.
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded-3 border d-flex align-items-center" id="lokasi-box">
            <div class="me-2">📡</div>
            <div id="lokasi-info" class="small">
                Mengambil lokasi Anda...
            </div>
        </div>

        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <button type="submit" class="btn w-100 mt-3 py-2 fw-semibold"
                style="background: linear-gradient(90deg,#F47C20,#F15A24); border:none; color:#fff;">
            💼 Kirim Absensi Sekarang
        </button>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('formAbsensi').addEventListener('submit', async function (e) {
    e.preventDefault();

    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!lat || !lng) {
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi belum terdeteksi',
            text: 'Pastikan GPS aktif dan lokasi sudah diizinkan.',
            confirmButtonColor: '#F47C20'
        });
        return;
    }

    const formData = new FormData(this);

    Swal.fire({
        title: 'Memproses...',
        text: 'Sedang mengirim data absensi...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
      
    headers: {
        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
    },
    body: formData
});

        const data = await response.json();
        Swal.close();

        if (response.ok && data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: `${data.message}<br>🕒 <b>Waktu:</b> ${data.jam} WIB`,
                confirmButtonColor: '#F47C20'
            });

            this.reset();
            ambilLokasi();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Gagal',
                text: data.message || 'Terjadi kesalahan saat absensi.',
                confirmButtonColor: '#F47C20'
            });
        }

    } catch (err) {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan',
            text: 'Gagal menghubungi server.',
            confirmButtonColor: '#F47C20'
        });
        console.error(err);
    }
});
</script>

@endpush
@endsection
