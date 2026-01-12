@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <div class="page-title">Edit Data Absensi</div>
        <div class="page-subtitle">
            Koreksi tanggal/jam/tipe/status absensi.
        </div>
    </div>
    <a href="{{ route('admin.kehadiran') }}" class="btn btn-sm btn-outline-secondary">
        ← Kembali ke Kehadiran
    </a>
</div>

<div class="card-soft">
    <form method="POST" action="{{ route('admin.kehadiran.update', $absen->id) }}" class="row g-3">
        @csrf

        <div class="col-md-4">
            <label class="form-label fw-semibold">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $absen->tanggal }}" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Jam</label>
            <input type="time" name="jam" value="{{ substr($absen->jam, 0, 5) }}" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Tipe</label>
            <select name="tipe" class="form-select" required>
                <option value="Masuk" {{ $absen->tipe == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                <option value="Pulang" {{ $absen->tipe == 'Pulang' ? 'selected' : '' }}>Pulang</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <input type="text" name="status" class="form-control"
                   value="{{ $absen->status }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Pegawai</label>
            <input type="text" class="form-control" value="{{ $absen->pegawai->nama ?? '-' }}" disabled>
        </div>

        <div class="col-12 mt-2">
            <button class="btn w-100 py-2 fw-semibold"
                    style="background: linear-gradient(90deg,#F47C20,#F15A24); border:none; color:#fff;">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
