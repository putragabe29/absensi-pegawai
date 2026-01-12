@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-2">
    <div>
        <div class="page-title">Grafik Kehadiran</div>
        <div class="page-subtitle">
            Analitik absensi per pegawai & per bulan.
        </div>
    </div>
</div>

<div class="card-soft mb-3">
    <form method="GET" action="{{ route('admin.grafik') }}" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Pilih Pegawai</label>
            <select name="pegawai_id" class="form-select" onchange="this.form.submit()">
                @foreach($pegawai as $p)
                    <option value="{{ $p->id }}" {{ $pegawaiId == $p->id ? 'selected' : '' }}>
                        {{ $p->nip }} - {{ $p->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 text-end small text-muted">
            Grafik otomatis berubah saat pegawai diganti.
        </div>
    </form>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card-soft h-100">
            <h6 class="fw-semibold mb-2">Grafik Per Hari (Pegawai Terpilih)</h6>
            <canvas id="chartHarian" height="150"></canvas>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card-soft h-100">
            <h6 class="fw-semibold mb-2">Rekap Bulanan (Semua Pegawai)</h6>
            <canvas id="chartBulanan" height="150"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labelsHarian = {!! json_encode($labels->values()) !!};
    const dataHarian   = {!! json_encode($data->values()) !!};

    const ctxHarian = document.getElementById('chartHarian').getContext('2d');
    new Chart(ctxHarian, {
        type: 'line',
        data: {
            labels: labelsHarian,
            datasets: [{
                label: 'Jumlah Absensi',
                data: dataHarian,
                fill: false,
                borderColor: '#F47C20',
                tension: 0.25
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, precision: 0 }
            }
        }
    });

    const labelsBulanan = {!! json_encode($rekapBulanan->pluck('bulan')->map(fn($b) => "Bulan ".$b)) !!};
    const dataBulanan   = {!! json_encode($rekapBulanan->pluck('total')) !!};

    const ctxBulanan = document.getElementById('chartBulanan').getContext('2d');
    new Chart(ctxBulanan, {
        type: 'bar',
        data: {
            labels: labelsBulanan,
            datasets: [{
                label: 'Total Absensi',
                data: dataBulanan,
                backgroundColor: '#FFB347',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, precision: 0 }
            }
        }
    });
</script>
@endpush
@endsection
