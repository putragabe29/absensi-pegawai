@extends('layouts.app')

@section('content')
<style>
.rekap-card{
    background:#fff;
    border-radius:20px;
    box-shadow:0 8px 30px rgba(0,0,0,.15);
    padding:25px;
}
.rekap-title{
    color:#F47C20;
    font-weight:700;
}
</style>

<div class="rekap-card">
    <h4 class="rekap-title mb-4">📊 Rekap Absensi Bulanan</h4>

    {{-- FILTER --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Pegawai</label>
            <select name="pegawai_id" class="form-select">
                <option value="">-- Semua Pegawai --</option>
                @foreach($pegawais as $p)
                    <option value="{{ $p->id }}"
                        {{ $pegawai_id == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->nip }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select">
                @for($i=1;$i<=12;$i++)
                    <option value="{{ sprintf('%02d',$i) }}"
                        {{ $bulan == sprintf('%02d',$i) ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$i,1)) }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <select name="tahun" class="form-select">
                @for($y=date('Y');$y>=date('Y')-5;$y--)
                    <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-warning w-100 fw-bold">🔍 Tampilkan</button>
        </div>
    </form>

    {{-- TABEL --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-warning text-center">
                <tr>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Jam</th>
                    <th>Tipe</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($absensi as $a)
                <tr>
                    <td>{{ $a->tanggal }}</td>
                    <td>{{ $a->pegawai->nama }}</td>
                    <td>{{ $a->pegawai->nip }}</td>
                    <td class="text-center">{{ $a->jam }}</td>
                    <td class="text-center">
                        <span class="badge {{ $a->tipe=='Masuk'?'bg-success':'bg-primary' }}">
                            {{ $a->tipe }}
                        </span>
                    </td>
                    <td class="text-center">{{ $a->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        ❌ Tidak ada data absensi
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- EXPORT --}}
    <div class="mt-4 text-end">
        <a href="{{ route('admin.rekap.pdf', request()->query()) }}"
           class="btn btn-danger fw-bold">
            📄 Export PDF
        </a>
    </div>
</div>
@endsection
