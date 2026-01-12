@extends('layouts.app')

@section('content')
<div class="container">
  <h4>🕒 Edit Kehadiran Pegawai</h4>
  <a href="/admin/dashboard" class="btn btn-secondary mb-3">⬅️ Kembali</a>

  <form method="POST" action="{{ route('admin.absensi.update', $absensi->id) }}">
    @csrf
    <div class="mb-3">
      <label>Tanggal</label>
      <input type="date" name="tanggal" class="form-control" value="{{ $absensi->tanggal }}" required>
    </div>
    <div class="mb-3">
      <label>Jam</label>
      <input type="time" name="jam" class="form-control" value="{{ $absensi->jam }}" required>
    </div>
    <div class="mb-3">
      <label>Status</label>
      <input type="text" name="status" class="form-control" value="{{ $absensi->status }}" required>
    </div>
    <div class="mb-3">
      <label>Tipe</label>
      <select name="tipe" class="form-select">
        <option value="Masuk" {{ $absensi->tipe == 'Masuk' ? 'selected' : '' }}>Masuk</option>
        <option value="Pulang" {{ $absensi->tipe == 'Pulang' ? 'selected' : '' }}>Pulang</option>
      </select>
    </div>
    <button class="btn btn-kpu">💾 Simpan Perubahan</button>
  </form>
</div>
@endsection
