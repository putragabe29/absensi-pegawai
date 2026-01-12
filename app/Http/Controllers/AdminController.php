<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\PengaturanKantor;
use App\Models\Izin;
use App\Models\Broadcast;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /* ============================================================
       CONSTRUCTOR
    ============================================================ */
 public function __construct()
{
    $this->middleware('auth');

    $this->middleware(function ($request, $next) {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak');
        }
        return $next($request);
    });
}


    /* ============================================================
       DASHBOARD ADMIN
    ============================================================ */
    public function dashboard()
    {
        $hariIni = date('Y-m-d');

        $totalPegawai = Pegawai::where('role', 'pegawai')->count();

        $hadirMasuk = Absensi::whereDate('tanggal', $hariIni)
            ->where('tipe', 'Masuk')
            ->count();

        $hadirPulang = Absensi::whereDate('tanggal', $hariIni)
            ->where('tipe', 'Pulang')
            ->count();

        $tabelRingkasan = [
            'totalPegawai' => $totalPegawai,
            'hadirMasuk'   => $hadirMasuk,
            'hadirPulang'  => $hadirPulang,
            'tidakHadir'   => max(0, $totalPegawai - $hadirMasuk),
        ];

        // Grafik bulanan
        $bulan = date('m');
        $tahun = date('Y');

        $rekapChart = Absensi::selectRaw('tanggal, COUNT(*) as total')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Rekap harian pegawai
        $pegawai = Pegawai::where('role', 'pegawai')->get();

        $rekap = $pegawai->map(function ($p) {
            $masuk = Absensi::where('pegawai_id', $p->id)
                ->whereDate('tanggal', date('Y-m-d'))
                ->where('tipe', 'Masuk')
                ->first();

            $pulang = Absensi::where('pegawai_id', $p->id)
                ->whereDate('tanggal', date('Y-m-d'))
                ->where('tipe', 'Pulang')
                ->first();

            return [
                'pegawai_id' => $p->id,
                'nip'        => $p->nip,
                'nama'       => $p->nama,
                'masuk'      => $masuk ? '✅ Hadir' : '❌ Belum',
                'pulang'     => $pulang ? '✅ Pulang' : '❌ Belum',
                'id_absensi' => $masuk?->id ?? $pulang?->id ?? null,
            ];
        });

        return view('admin.dashboard', compact(
            'rekap',
            'tabelRingkasan',
            'rekapChart'
        ));
    }

    /* ============================================================
       PENGATURAN LOKASI & JAM KERJA
    ============================================================ */
    public function lokasi()
    {
        $lokasi = PengaturanKantor::first();

        if (!$lokasi) {
            $lokasi = (object) [
                'nama_kantor'        => '',
                'latitude'           => '',
                'longitude'          => '',
                'radius'             => '',
                'jam_masuk_mulai'    => '',
                'jam_masuk_selesai'  => '',
                'jam_pulang_mulai'   => '',
                'jam_pulang_selesai' => '',
            ];
        }

        return view('admin.lokasi', compact('lokasi'));
    }

    public function updateLokasi(Request $request)
    {
        $request->validate([
            'nama_kantor'        => 'required|string|max:150',
            'latitude'           => 'required|numeric',
            'longitude'          => 'required|numeric',
            'radius'             => 'required|numeric|min:10',
            'jam_masuk_mulai'    => 'required',
            'jam_masuk_selesai'  => 'required',
            'jam_pulang_mulai'   => 'required',
            'jam_pulang_selesai' => 'required',
        ]);

        $lokasi = PengaturanKantor::first();

        if (!$lokasi) {
            PengaturanKantor::create($request->only([
                'nama_kantor',
                'latitude',
                'longitude',
                'radius',
                'jam_masuk_mulai',
                'jam_masuk_selesai',
                'jam_pulang_mulai',
                'jam_pulang_selesai',
            ]));
        } else {
            $lokasi->update($request->only([
                'nama_kantor',
                'latitude',
                'longitude',
                'radius',
                'jam_masuk_mulai',
                'jam_masuk_selesai',
                'jam_pulang_mulai',
                'jam_pulang_selesai',
            ]));
        }

        return back()->with('success', '✅ Pengaturan lokasi & jam kerja berhasil diperbarui!');
    }

    /* ============================================================
       IZIN / CUTI
    ============================================================ */
    public function adminIndex()
    {
        $izins = Izin::with('pegawai')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.izin', compact('izins'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $izin = Izin::findOrFail($id);
        $izin->update($request->only([
            'status',
            'catatan_admin'
        ]));

        return back()->with('success', '✅ Status izin berhasil diperbarui!');
    }

    /* ============================================================
       REKAP BULANAN
    ============================================================ */
    public function rekapBulan(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $pegawai_id = $request->pegawai_id ?? null;

        $pegawais = Pegawai::where('role', 'pegawai')->get();

        $absensi = Absensi::with('pegawai')
            ->when($pegawai_id, function ($q) use ($pegawai_id) {
                $q->where('pegawai_id', $pegawai_id);
            })
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->get();

        return view('admin.rekap', compact(
            'pegawais',
            'absensi',
            'bulan',
            'tahun',
            'pegawai_id'
        ));
    }

    public function rekapPDF(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $pegawais = Pegawai::where('role', 'pegawai')->get();
        $absensi = Absensi::with('pegawai')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $pdf = Pdf::loadView(
            'admin.rekap_pdf',
            compact('pegawais', 'absensi', 'bulan', 'tahun')
        )->setPaper('a4', 'landscape');

        return $pdf->download("rekap-absensi-$bulan-$tahun.pdf");
    }

    /* ============================================================
       KEHADIRAN (EDIT / HAPUS)
    ============================================================ */
    public function listAbsensi()
    {
        $absensi = Absensi::with('pegawai')
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc')
            ->get();

        $pegawais = Pegawai::where('role', 'pegawai')->get();

        return view('admin.kehadiran.index', compact('absensi', 'pegawais'));
    }

    public function editAbsensi($id)
    {
        $absen = Absensi::findOrFail($id);
        return view('admin.kehadiran.edit', compact('absen'));
    }

    public function updateAbsensi(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam'     => 'required',
            'status'  => 'required',
            'tipe'    => 'required',
        ]);

        $absen = Absensi::findOrFail($id);
        $absen->update($request->only([
            'tanggal',
            'jam',
            'status',
            'tipe'
        ]));

        return back()->with('success', '✅ Data absensi berhasil diperbarui!');
    }

    public function deleteAbsensi($id)
    {
        Absensi::findOrFail($id)->delete();
        return back()->with('success', '🗑️ Data absensi berhasil dihapus!');
    }

    /* ============================================================
       GRAFIK KEHADIRAN
    ============================================================ */
    public function grafikKehadiran()
    {
        $pegawai = Pegawai::where('role', 'pegawai')->get();
        $pegawaiId = request('pegawai_id', $pegawai->first()->id ?? null);

        $absensi = Absensi::where('pegawai_id', $pegawaiId)
            ->selectRaw('tanggal, COUNT(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return view('admin.grafik', [
            'pegawai'   => $pegawai,
            'pegawaiId' => $pegawaiId,
            'labels'    => $absensi->pluck('tanggal'),
            'data'      => $absensi->pluck('total'),
        ]);
    }

    /* ============================================================
       BROADCAST PESAN
    ============================================================ */
    public function broadcastIndex()
    {
        $broadcasts = Broadcast::orderBy('created_at', 'desc')->get();
        return view('admin.broadcast', compact('broadcasts'));
    }

    public function broadcastStore(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:150',
            'pesan' => 'required|string',
        ]);

        Broadcast::create($request->only(['judul', 'pesan']));

        return back()->with('success', '📢 Pesan broadcast berhasil dikirim!');
    }
}
