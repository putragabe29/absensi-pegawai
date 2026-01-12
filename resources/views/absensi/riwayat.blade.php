@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <div class="page-title">Riwayat Absensi Saya</div>
        <div class="page-subtitle">Lihat jejak hadir masuk & pulang.</div>
    </div>
    <a href="{{ url('/absensi') }}" class="btn btn-sm btn-outline-secondary">
        ← Kembali ke Absensi
    </a>
</div>

@php
    // Biar aman kalau controller kasih $absensi atau $riwayat
    $rows = isset($riwayat) ? $riwayat : ($absensi ?? collect());
@endphp

<div class="card-soft">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold">Log Absensi</span>
        <span class="badge-soft">
            Total: {{ $rows->count() }} catatan
        </span>
    </div>

    <div class="table-responsive mt-2">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-warning">
            <tr>
                <th style="width: 120px;">Tanggal</th>
                <th style="width: 90px;">Jam</th>
                <th style="width: 90px;">Tipe</th>
                <th>Status</th>
                <th style="width: 110px;">Jarak (m)</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $r->jam }}</td>
                    <td>
                        @if($r->tipe === 'Masuk')
                            <span class="badge text-bg-success">Masuk</span>
                        @else
                            <span class="badge text-bg-info">Pulang</span>
                        @endif
                    </td>
                    <td>{{ $r->status }}</td>
                    <td>{{ round($r->jarak, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        Belum ada riwayat absensi yang tercatat.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
