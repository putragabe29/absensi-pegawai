@extends('layouts.app')

@section('content')
<div class="card p-4">
    <h4 class="mb-3">Form Absensi</h4>

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
            <input type="file" name="foto" accept="image/*" capture="environment" class="form-control" required>
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
navigator.geolocation.getCurrentPosition(
    pos => {
        document.getElementById('latitude').value = pos.coords.latitude;
        document.getElementById('longitude').value = pos.coords.longitude;
    },
    () => alert('Aktifkan GPS'),
    { enableHighAccuracy: true }
);

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
            Swal.fire('Berhasil', data.message + ' (' + data.jam + ')', 'success');
            this.reset();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Server tidak bisa dihubungi', 'error');
    }
});
</script>
@endsection
