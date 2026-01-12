@extends('layouts.app')
@section('content')

<div class="p-4 rounded mb-4 text-white"
     style="background:linear-gradient(135deg,#F47C20,#FF9F4A);border-radius:20px;">
    <h2 class="fw-bold">Dashboard Admin</h2>
    <div>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="p-3 text-white shadow"
             style="background:#F47C20;border-radius:16px;">
            <h5>Total Pegawai</h5>
            <h2>{{ $tabelRingkasan['totalPegawai'] }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="p-3 text-white shadow"
             style="background:#0CA97B;border-radius:16px;">
            <h5>Hadir Masuk</h5>
            <h2>{{ $tabelRingkasan['hadirMasuk'] }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="p-3 text-white shadow"
             style="background:#FF9A3C;border-radius:16px;">
            <h5>Hadir Pulang</h5>
            <h2>{{ $tabelRingkasan['hadirPulang'] }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="p-3 text-white shadow"
             style="background:#B35412;border-radius:16px;">
            <h5>Belum Hadir</h5>
            <h2>{{ $tabelRingkasan['tidakHadir'] }}</h2>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('admin.pegawai') }}"
       class="btn btn-orange px-4 py-2 fw-bold"
       style="border-radius:12px;">Kelola Pegawai</a>

    <a href="{{ route('admin.kehadiran') }}"
       class="btn btn-orange px-4 py-2 fw-bold"
       style="border-radius:12px;">Kehadiran Pegawai</a>

    <a href="{{ route('admin.lokasi') }}"
       class="btn btn-orange px-4 py-2 fw-bold"
       style="border-radius:12px;">Pengaturan Absensi</a>

    <a href="{{ route('admin.rekap') }}"
       class="btn btn-orange px-4 py-2 fw-bold"
       style="border-radius:12px;">Rekap Bulanan</a>
</div>

@endsection
