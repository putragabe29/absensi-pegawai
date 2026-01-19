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
document.getElementById('formAbsensi').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;

    Swal.fire({
        title: 'Mengirim absensi...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch('/api/absensi', {
            method: 'POST',
            body: new FormData(this)
        });

        const text = await res.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch {
            throw new Error('Response bukan JSON');
        }

        Swal.close();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message + ' (' + data.jam + ')'
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'warning');
            btn.disabled = false;
        }

    } catch (err) {
        Swal.close();
        Swal.fire(
            'Error',
            'Server tidak merespon',
            'error'
        );
        btn.disabled = false;
        console.error(err);
    }
});
</script>
@endsection
