@extends('layouts.app')

@section('content')
<style>
  body {
    background: linear-gradient(135deg, #F47C20, #FF9F4A, #FFD180);
    font-family: 'Poppins', sans-serif;
  }

  .izin-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.2);
    padding: 25px 30px;
    margin-top: 30px;
    animation: fadeIn 0.8s ease-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  h4 {
    color: #F47C20;
    font-weight: 600;
    text-align: center;
    margin-bottom: 15px;
  }

  .btn-kpu {
    background: linear-gradient(90deg, #F47C20, #F15A24);
    border: none;
    color: #fff;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-kpu:hover {
    background: #E65100;
    transform: scale(1.03);
  }

  .btn-back {
    background: white;
    border: 2px solid #F47C20;
    color: #F47C20;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
  }

  .btn-back:hover {
    background: #F47C20;
    color: #fff;
  }

  table {
    border-radius: 10px;
    overflow: hidden;
  }

  thead {
    background: #F47C20;
    color: white;
  }

  tr:hover {
    background-color: #fff7f0;
    transition: 0.2s;
  }

  .status-badge {
    font-size: 13px;
    padding: 6px 10px;
    border-radius: 10px;
    font-weight: 500;
  }

  .bg-menunggu {
    background: #fff3e0;
    color: #F57C00;
  }

  .bg-disetujui {
    background: #e8f5e9;
    color: #2e7d32;
  }

  .bg-ditolak {
    background: #ffebee;
    color: #c62828;
  }

  .no-data {
    color: #999;
    text-align: center;
    font-style: italic;
  }

  .top-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
  }
</style>

<div class="col-md-8 mx-auto izin-card">
  <div class="top-buttons">
    <a href="/absensi" class="btn btn-back">⬅ Kembali ke Halaman Absensi</a>
    <a href="/izin/create" class="btn btn-kpu">+ Ajukan Izin / Cuti</a>
  </div>

  <h4>📋 Daftar Pengajuan Izin / Cuti</h4>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered align-middle text-center">
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th>Alasan</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($izins as $i)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>
          <strong>{{ $i->tanggal_mulai }}</strong><br>
          <span class="text-muted">s/d</span><br>
          <strong>{{ $i->tanggal_selesai }}</strong>
        </td>
        <td>{{ $i->jenis }}</td>
        <td class="text-start">{{ $i->alasan }}</td>
        <td>
          @if($i->status == 'Menunggu')
            <span class="status-badge bg-menunggu">{{ $i->status }}</span>
          @elseif($i->status == 'Disetujui')
            <span class="status-badge bg-disetujui">{{ $i->status }}</span>
          @else
            <span class="status-badge bg-ditolak">{{ $i->status }}</span>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="no-data">Belum ada pengajuan izin / cuti.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
