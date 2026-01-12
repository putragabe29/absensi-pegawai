<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Izin;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    // Halaman daftar izin pegawai
    public function index()
    {
        $izins = Izin::where('pegawai_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('izin.index', compact('izins'));
    }

    // Form pengajuan izin
    public function create()
    {
        return view('izin.create');
    }

    // Simpan pengajuan izin
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis' => 'required',
            'alasan' => 'required|string|max:255',
        ]);

        Izin::create([
            'pegawai_id' => Auth::id(),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jenis' => $request->jenis,
            'alasan' => $request->alasan,
        ]);

        return redirect('/izin')->with('success', '✅ Pengajuan izin berhasil dikirim.');
    }

    // Halaman admin melihat & memproses izin
    public function adminIndex()
    {
        $izins = Izin::with('pegawai')->orderBy('created_at', 'desc')->get();
        return view('admin.izin', compact('izins'));
    }

    // Admin menyetujui / menolak izin
    public function updateStatus(Request $request, $id)
    {
        $izin = Izin::findOrFail($id);
        $izin->update([
            'status' => $request->status,
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', '✅ Status izin diperbarui!');
    }
}
