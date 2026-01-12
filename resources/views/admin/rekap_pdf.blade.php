<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekap Absensi Bulanan - KPU LABUHAN BATU SELATAN</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      margin: 30px;
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: center;
      border-bottom: 2px solid #F47C20;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
    .header img {
      width: 70px;
      height: auto;
      margin-right: 15px;
    }
    .header-text {
      text-align: center;
      line-height: 1.4;
    }
    .header-text h2 {
      margin: 0;
      font-size: 16px;
      color: #000;
    }
    .header-text h3 {
      margin: 0;
      font-size: 14px;
      color: #F47C20;
      font-weight: bold;
    }
    .sub-title {
      text-align: center;
      font-size: 13px;
      margin-top: 8px;
      color: #555;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #999;
      padding: 6px;
      text-align: center;
    }
    th {
      background-color: #ffe0b2;
      color: #4E342E;
      font-weight: bold;
    }
    tr:nth-child(even) {
      background-color: #fffaf5;
    }
    .hadir {
      color: green;
      font-weight: bold;
    }
    .tidakhadir {
      color: red;
      font-weight: bold;
    }
    .footer {
      margin-top: 60px;
      text-align: right;
      font-size: 13px;
    }
    .footer p {
      margin: 3px 0;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <img src="{{ public_path('images/logo_kpu.png') }}" alt="Logo KPU">
    <div class="header-text">
      <h2>KOMISI PEMILIHAN UMUM</h2>
      <h3>KABUPATEN LABUHAN BATU SELATAN</h3>
    </div>
  </div>

  <div class="sub-title">
    📅 Rekapitulasi Absensi Bulanan Pegawai<br>
    Bulan: <b>{{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</b>
  </div>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pegawai</th>
        <th>NIP</th>
        <th>Tanggal</th>
        <th>Masuk</th>
        <th>Pulang</th>
        <th>Hadir</th>
        <th>Izin</th>
        <th>Cuti</th>
        <th>Sakit</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rekap as $index => $r)
      <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $r['nama'] }}</td>
        <td>{{ $r['nip'] }}</td>
        <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d-m-Y') }}</td>
        <td>{{ $r['masuk'] }}</td>
        <td>{{ $r['pulang'] }}</td>
        <td class="{{ $r['hadir'] == '✅' ? 'hadir' : 'tidakhadir' }}">
          {{ $r['hadir'] == '✅' ? '✅ Hadir' : '❌ Tidak Lengkap' }}
        </td>
        <td>
          @php
            $izin = $izins->first(fn($izin) =>
                $izin->pegawai_id == $r['pegawai_id'] &&
                $izin->jenis == 'Izin' &&
                $izin->status == 'Disetujui' &&
                $r['tanggal'] >= $izin->tanggal_mulai &&
                $r['tanggal'] <= $izin->tanggal_selesai
            );
          @endphp
          {{ $izin ? '✅' : '-' }}
        </td>
        <td>
          @php
            $cuti = $izins->first(fn($izin) =>
                $izin->pegawai_id == $r['pegawai_id'] &&
                $izin->jenis == 'Cuti' &&
                $izin->status == 'Disetujui' &&
                $r['tanggal'] >= $izin->tanggal_mulai &&
                $r['tanggal'] <= $izin->tanggal_selesai
            );
          @endphp
          {{ $cuti ? '✅' : '-' }}
        </td>
        <td>
          @php
            $sakit = $izins->first(fn($izin) =>
                $izin->pegawai_id == $r['pegawai_id'] &&
                $izin->jenis == 'Sakit' &&
                $izin->status == 'Disetujui' &&
                $r['tanggal'] >= $izin->tanggal_mulai &&
                $r['tanggal'] <= $izin->tanggal_selesai
            );
          @endphp
          {{ $sakit ? '✅' : '-' }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    <p>Labuhan Batu Selatan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
    <p><b>Kepala Sekretariat KPU Kabupaten Labuhan Batu Selatan</b></p>
    <br><br><br>
    <p><b><u>__________________________</u></b></p>
  </div>

</body>
</html>
