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

  .form-label {
    font-weight: 500;
  }

  .form-control:focus, .form-select:focus {
    border-color: #F47C20;
    box-shadow: 0 0 0 0.2rem rgba(244, 124, 32, 0.25);
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

  .note-box {
    background: #fff3e0;
    border-left: 5px solid #F47C20;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
    color: #555;
    margin-bottom: 15px;
  }

  .back-link {
    text-decoration: none;
    color: #F47C20;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    margin-top: 10px;
  }

  .back-link:hover {
    text-decoration: underline;
  }
</style>

<div class="col-md-6 mx-auto izin-card">
  <h4>📝 Form Pengajuan Izin / Cuti</h4>
  <p class="text-muted text-center">Isi form berikut untuk mengajukan izin, cuti, atau sakit.</p>

  <div class="note-box">
    📅 Pastikan tanggal izin tidak bentrok dengan jadwal kerja penting. Pengajuan akan diverifikasi oleh admin KPU.
  </div>

  <form method="POST" action="{{ route('izin.store') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Tanggal Mulai</label>
      <input type="date" name="tanggal_mulai" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Tanggal Selesai</label>
      <input type="date" name="tanggal_selesai" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Izin</label>
      <select name="jenis" class="form-select" required>
        <option value="Cuti">Cuti</option>
        <option value="Izin">Izin</option>
        <option value="Sakit">Sakit</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Alasan</label>
      <textarea name="alasan" class="form-control" rows="3" required placeholder="Tuliskan alasan izin atau cuti..."></textarea>
    </div>

    <button class="btn btn-kpu w-100 py-2">💾 Kirim Pengajuan</button>

    <a href="/izin" class="back-link">⬅ Kembali ke daftar izin</a>
  </form>
</div>
@endsection
