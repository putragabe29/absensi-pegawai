@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-2">
    <div>
        <div class="page-title">Kelola Izin / Cuti</div>
        <div class="page-subtitle">
            Tinjau pengajuan izin, cuti, atau sakit pegawai.
        </div>
    </div>
</div>

<div class="card-soft">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Pegawai</th>
                <th>Jenis</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Alasan</th>
                <th>Status</th>
                <th style="width: 200px;">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($izins as $izin)
                <tr>
                    <td>{{ $izin->pegawai->nama ?? '-' }}</td>
                    <td>{{ $izin->jenis }}</td>
                    <td>{{ $izin->tanggal_mulai }}</td>
                    <td>{{ $izin->tanggal_selesai }}</td>
                    <td>{{ $izin->alasan }}</td>
                    <td>{{ strtoupper($izin->status) }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.izin.update', $izin->id) }}" class="row g-1">
                            @csrf
                            <div class="col-6">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="pending" {{ $izin->status=='pending'?'selected':'' }}>Pending</option>
                                    <option value="disetujui" {{ $izin->status=='disetujui'?'selected':'' }}>Disetujui</option>
                                    <option value="ditolak" {{ $izin->status=='ditolak'?'selected':'' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <input type="text" name="catatan_admin"
                                       class="form-control form-control-sm"
                                       placeholder="Catatan admin"
                                       value="{{ $izin->catatan_admin }}">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm w-100 btn-outline-primary mt-1">
                                    💾 Simpan
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        Belum ada pengajuan izin/cuti.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
