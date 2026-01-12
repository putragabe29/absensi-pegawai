@extends('layouts.app')

@section('content')
@php
    // Mengubah nomor bulan menjadi nama bulan (dalam bahasa lokal, misal: Januari, Februari, dst)
    $bulanNama = \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F');

    // Tanggal pertama di bulan & tahun yang dipilih (format: YYYY-MM-01)
    $firstDay = sprintf('%04d-%02d-01', $tahun, $bulan);

    // Menentukan hari pertama: 1 = Senin, 7 = Minggu
    $startWeekday = date('N', strtotime($firstDay));
@endphp

<style>
  /* Card utama kalender */
  .calendar-card {
    background:#fff;
    border-radius:18px;
    box-shadow:0 5px 25px rgba(0,0,0,0.2);
    padding:25px;
    margin-top:20px;
  }
  /* Judul kalender */
  .calendar-title {
    font-size:24px;
    font-weight:700;
    color:#F47C20;
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:15px;
  }
  /* Grid kalender 7 kolom (Senin–Minggu) */
  .calendar-grid {
    display:grid;
    grid-template-columns: repeat(7, 1fr);
    gap:8px;
  }
  /* Header nama hari */
  .day-header {
    text-align:center;
    font-weight:600;
    font-size:13px;
    color:#555;
  }
  /* Box tanggal per hari */
  .day-box {
    min-height:70px;
    border-radius:12px;
    padding:6px 8px;
    font-size:12px;
    position:relative;
    background:#fafafa;
    border:1px solid #eee;
  }
  .day-box .tanggal {
    font-weight:600;
    font-size:13px;
  }
  /* Warna jika hadir lengkap */
  .day-box.hadir {
    background:#e8f5e9;
    border-color:#a5d6a7;
  }
  /* Warna jika hadir sebagian */
  .day-box.sebagian {
    background:#fff3e0;
    border-color:#ffcc80;
  }
  /* Warna jika tidak hadir */
  .day-box.tidak {
    background:#ffebee;
    border-color:#ef9a9a;
  }
  /* Legend penjelasan status */
  .legend span {
    margin-right:12px;
    font-size:13px;
  }
</style>

<div class="calendar-card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="calendar-title">
      📅 Kalender Kehadiran
    </div>

    {{-- Form filter bulan & tahun --}}
    <form method="GET" class="d-flex gap-2">
      {{-- Pilih bulan --}}
      <select name="bulan" class="form-select form-select-sm">
        @for($m=1;$m<=12;$m++)
          <option value="{{ sprintf('%02d',$m) }}" {{ $m == $bulan ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create(null,$m,1)->translatedFormat('F') }}
          </option>
        @endfor
      </select>

      {{-- Pilih tahun (range: 2 tahun ke belakang, 1 tahun ke depan) --}}
      <select name="tahun" class="form-select form-select-sm">
        @for($t = date('Y')-2; $t <= date('Y')+1; $t++)
          <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
        @endfor
      </select>

      {{-- Tombol tampilkan --}}
      <button class="btn btn-sm btn-primary">Tampilkan</button>
    </form>
  </div>

  {{-- Keterangan legend status --}}
  <div class="legend mb-3">
    <span>✅ Hadir lengkap</span>
    <span>🟡 Hadir sebagian</span>
    <span>❌ Tidak hadir</span>
  </div>

  {{-- Grid kalender --}}
  <div class="calendar-grid">
    {{-- Header nama hari --}}
    <div class="day-header">Sen</div>
    <div class="day-header">Sel</div>
    <div class="day-header">Rab</div>
    <div class="day-header">Kam</div>
    <div class="day-header">Jum</div>
    <div class="day-header">Sab</div>
    <div class="day-header">Min</div>

    {{-- Kotak kosong sebelum tanggal 1 (untuk alignment sesuai hari) --}}
    @for($i=1; $i < $startWeekday; $i++)
      <div></div>
    @endfor

    {{-- Isi hari berdasarkan data $kalender dari controller --}}
    @foreach($kalender as $hari)
      @php
        // Ambil angka tanggal saja dari 'YYYY-MM-DD'
        $tgl = \Carbon\Carbon::parse($hari['tanggal'])->day;

        // Tentukan kelas CSS berdasarkan badge
        $cls = 'tidak'; // default: tidak hadir
        if ($hari['badge'] === '✅') $cls = 'hadir';
        elseif ($hari['badge'] === '🟡') $cls = 'sebagian';
      @endphp

      <div class="day-box {{ $cls }}">
        {{-- Tanggal --}}
        <div class="tanggal">{{ $tgl }}</div>
        {{-- Status kehadiran singkat --}}
        <div class="mt-1">
          {{ $hari['badge'] }} {{ $hari['status'] }}
        </div>
      </div>
    @endforeach
  </div>

  {{-- Tombol kembali ke halaman riwayat tabel --}}
  <div class="mt-3">
    <button onclick="window.location.href='{{ route('pegawai.riwayat') }}'" class="btn btn-sm btn-outline-secondary">
      ⬅ Kembali ke Riwayat
    </button>
  </div>
</div>
@endsection
