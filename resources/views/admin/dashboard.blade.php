@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <div class="page-title">Dashboard Admin</div>
        <div class="page-subtitle">
            Ringkasan kehadiran hari ini & navigasi utama.
        </div>
    </div>
    <span class="badge-soft">
        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
    </span>
</div>

{{-- Kartu ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-6">
        <div class="card-soft h-100">
            <div class="small text-muted mb-1">Total Pegawai</div>
            <div class="fs-4 fw-bold">{{ $tabelRingkasan['totalPegawai'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card-soft h-100">
            <div class="small text-muted mb-1">Sudah Absen Masuk</div>
            <div class="fs-4 fw-bold text-success">{{ $tabelRingkasan['hadirMasuk'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card-soft h-100">
            <div class="small text-muted mb-1">Sudah Absen Pulang</div>
            <div class="fs-4 fw-bold text-info">{{ $tabelRingkasan['hadirPulang'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card-soft h-100">
            <div class="small text-muted mb-1">Belum Hadir</div>
            <div class="fs-4 fw-bold text-danger">{{ $tabelRingkasan['tidakHadir'] ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- Menu grid --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <a href="{{ route('admin.pegawai') }}" class="text-decoration-none">
            <div class="card-soft h-100" style="background: linear-gradient(135deg,#ffb347,#ff7a18); color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold mb-1">Kelola Pegawai</div>
                        <div class="small">Tambah, edit dan reset password pegawai.</div>
                    </div>
                    <div style="font-size: 34px;">👥</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-sm-6">
        <a href="{{ route('admin.kehadiran') }}" class="text-decoration-none">
            <div class="card-soft h-100" style="background: linear-gradient(135deg,#a767e5,#6f2dbd); color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold mb-1">Ralat Kehadiran</div>
                        <div class="small">Lihat dan koreksi data absensi.</div>
                    </div>
                    <div style="font-size: 34px;">📝</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-sm-6">
        <a href="{{ route('admin.lokasi') }}" class="text-decoration-none">
            <div class="card-soft h-100" style="background: linear-gradient(135deg,#4facfe,#00f2fe); color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold mb-1">Pengaturan Absensi</div>
                        <div class="small">Lokasi kantor & jam kerja.</div>
                    </div>
                    <div style="font-size: 34px;">📍</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-sm-6">
        <a href="{{ route('admin.izin') }}" class="text-decoration-none">
            <div class="card-soft h-100" style="background: linear-gradient(135deg,#ff6a6a,#ff3e3e); color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold mb-1">Kelola Izin/Cuti</div>
                        <div class="small">Setujui atau tolak pengajuan.</div>
                    </div>
                    <div style="font-size: 34px;">📋</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-sm-6">
        <a href="{{ route('admin.rekap') }}" class="text-decoration-none">
            <div class="card-soft h-100" style="background: linear-gradient(135deg,#ff9a3c,#ff5722); color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold mb-1">Rekap Bulanan</div>
                        <div class="small">Laporan & export PDF.</div>
                    </div>
                    <div style="font-size: 34px;">📅</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4 col-sm-6">
        <a href="{{ route('admin.grafik') }}" class="text-decoration-none">
            <div class="card-soft h-100" style="background: linear-gradient(135deg,#009688,#004D40); color:#fff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold mb-1">Grafik Kehadiran</div>
                        <div class="small">Analitik per pegawai & per bulan.</div>
                    </div>
                    <div style="font-size: 34px;">📊</div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Rekap harian tabel sederhana --}}
<div class="card-soft">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold">Rekap Kehadiran Hari Ini</span>
        <span class="badge-soft">Monitoring cepat semua pegawai</span>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Masuk</th>
                <th>Pulang</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rekap as $r)
                <tr>
                    <td>{{ $r['nip'] }}</td>
                    <td>{{ $r['nama'] }}</td>
                    <td>{{ $r['masuk'] }}</td>
                    <td>{{ $r['pulang'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
