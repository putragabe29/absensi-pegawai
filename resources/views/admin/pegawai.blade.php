@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-2">
    <div>
        <div class="page-title">Kelola Pegawai</div>
        <div class="page-subtitle">Tambah akun pegawai, ubah password, dan hapus pegawai.</div>
    </div>
</div>

<div class="row g-3">

    {{-- ================= TAMBAH PEGAWAI ================= --}}
    <div class="col-md-4">
        <div class="card-soft h-100">
            <h6 class="fw-semibold mb-3">➕ Tambah Pegawai Baru</h6>

            <form method="POST" action="{{ route('admin.pegawai.store') }}">
                @csrf

                <div class="mb-2">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label class="form-label">Password Awal</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <input type="hidden" name="role" value="pegawai">

                <button class="btn w-100 mt-2 fw-semibold"
                        style="background: linear-gradient(90deg,#F47C20,#F15A24); color:#fff; border:none;">
                    ➕ Tambah Pegawai
                </button>
            </form>
        </div>
    </div>

    {{-- ================= DAFTAR PEGAWAI ================= --}}
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
                            <th>Role</th>
                            <th style="width: 260px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pegawais as $p)
                        <tr>
                            <td>{{ $p->nip }}</td>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->role }}</td>
                            <td class="d-flex gap-1">

                                {{-- 🔑 UPDATE PASSWORD --}}
                                <form method="POST"
                                      action="{{ route('admin.pegawai.updatePassword') }}"
                                      class="d-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="pegawai_id" value="{{ $p->id }}">
                                    <input type="password"
                                           name="password"
                                           class="form-control form-control-sm"
                                           placeholder="Password baru"
                                           required>
                                    <button class="btn btn-sm btn-outline-primary">
                                        🔑
                                    </button>
                                </form>

                                {{-- 🗑️ HAPUS PEGAWAI --}}
                                @if($p->role !== 'admin')
                                <form method="POST"
                                      action="{{ route('admin.pegawai.destroy', $p->id) }}"
                                      onsubmit="return confirm('Yakin hapus pegawai ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        🗑️
                                    </button>
                                </form>
                                @endif

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
