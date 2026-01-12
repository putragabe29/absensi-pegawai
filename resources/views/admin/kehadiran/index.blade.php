@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <div class="page-title">Data Kehadiran</div>
        <div class="page-subtitle">
            Lihat dan koreksi absensi semua pegawai.
        </div>
    </div>
</div>

<div class="card-soft mb-3">
    <form method="GET" action="{{ route('admin.kehadiran') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Pegawai</label>
            <select name="pegawai_id" class="form-select">
                <option value="">Semua Pegawai</option>
                @foreach($pegawais as $p)
                    <option value="{{ $p->id }}" {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nip }} - {{ $p->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100">
                🔍 Filter
            </button>
        </div>
    </form>
</div>

<div class="card-soft">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold">Daftar Absensi</span>
        <span class="badge-soft">Total: {{ $absensi->count() }} baris</span>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Pegawai</th>
                <th>Tipe</th>
                <th>Status</th>
                <th>Jarak (m)</th>
                <th style="width: 140px;">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($absensi as $a)
                <tr>
                    <td>{{ $a->tanggal }}</td>
                    <td>{{ $a->jam }}</td>
                    <td>{{ $a->pegawai->nip ?? '' }} - {{ $a->pegawai->nama ?? '' }}</td>
                    <td>{{ $a->tipe }}</td>
                    <td>{{ $a->status }}</td>
                    <td>{{ round($a->jarak, 2) }}</td>
                    <td>
                        <a href="{{ route('admin.kehadiran.edit', $a->id) }}"
                           class="btn btn-sm btn-outline-primary">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('admin.kehadiran.delete', $a->id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                🗑 Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        Belum ada data absensi untuk filter yang dipilih.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
