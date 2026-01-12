@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-2">
    <div>
        <div class="page-title">Kelola Pegawai</div>
        <div class="page-subtitle">Tambah akun pegawai dan atur password.</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card-soft h-100">
            <h6 class="fw-semibold mb-2">Tambah Pegawai Baru</h6>
            <form method="POST" action="{{ route('admin.pegawai.store') }}" class="row g-2">
                @csrf
                <div class="col-12">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-12 mt-1">
                    <button class="btn w-100 py-2 fw-semibold"
                            style="background: linear-gradient(90deg,#F47C20,#F15A24); border:none; color:#fff;">
                        ➕ Tambah Pegawai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Daftar Pegawai</span>
                <span class="badge-soft">Total: {{ $pegawais->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pegawais as $p)
                        <tr>
                            <td>{{ $p->nip }}</td>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->username }}</td>
                            <td>{{ $p->role }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.pegawai.updatePassword') }}"
                                      class="d-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="pegawai_id" value="{{ $p->id }}">
                                    <input type="password" name="password" class="form-control form-control-sm"
                                           placeholder="Password baru" required>
                                    <button class="btn btn-sm btn-outline-primary">
                                        🔑
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
