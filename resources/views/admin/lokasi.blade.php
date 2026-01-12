@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <div class="page-title">Pengaturan Absensi</div>
        <div class="page-subtitle">
            Atur lokasi kantor & jam absensi masuk/pulang.
        </div>
    </div>
</div>

<div class="card-soft">
    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.lokasi.update') }}" class="row g-3">
        @csrf

        <div class="col-md-6">
            <label class="form-label fw-semibold">Nama Kantor</label>
            <input type="text" name="nama_kantor" value="{{ old('nama_kantor', $lokasi->nama_kantor) }}"
                   class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Latitude</label>
            <input type="text" name="latitude" value="{{ old('latitude', $lokasi->latitude) }}"
                   class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Longitude</label>
            <input type="text" name="longitude" value="{{ old('longitude', $lokasi->longitude) }}"
                   class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Radius (meter)</label>
            <input type="number" name="radius" value="{{ old('radius', $lokasi->radius) }}"
                   class="form-control" min="10" required>
        </div>

        <div class="col-md-9">
            <div class="small text-muted mt-4">
                Pegawai hanya bisa absen jika berada di dalam radius ini.
            </div>
        </div>

        <hr class="mt-3 mb-2">

        <div class="col-md-3">
            <label class="form-label fw-semibold">Jam Masuk Mulai</label>
            <input type="time" name="jam_masuk_mulai"
                   value="{{ old('jam_masuk_mulai', $lokasi->jam_masuk_mulai) }}"
                   class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Jam Masuk Selesai</label>
            <input type="time" name="jam_masuk_selesai"
                   value="{{ old('jam_masuk_selesai', $lokasi->jam_masuk_selesai) }}"
                   class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Jam Pulang Mulai</label>
            <input type="time" name="jam_pulang_mulai"
                   value="{{ old('jam_pulang_mulai', $lokasi->jam_pulang_mulai) }}"
                   class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Jam Pulang Selesai</label>
            <input type="time" name="jam_pulang_selesai"
                   value="{{ old('jam_pulang_selesai', $lokasi->jam_pulang_selesai) }}"
                   class="form-control" required>
        </div>

        <div class="col-12 mt-3">
            <button class="btn w-100 py-2 fw-semibold"
                    style="background: linear-gradient(90deg,#F47C20,#F15A24); border:none; color:#fff;">
                💾 Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
