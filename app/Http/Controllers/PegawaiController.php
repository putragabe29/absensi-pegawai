<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('admin.pegawai', compact('pegawais'));
    }

    // =============================
    // TAMBAH PEGAWAI
    // =============================
    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:pegawais,nip',
            'nama' => 'required',
            'password' => 'required|min:4',
            'role' => 'required|in:admin,pegawai'
        ]);

        Pegawai::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return back()->with('success', 'Pegawai berhasil ditambahkan');
    }

    // =============================
    // UPDATE PASSWORD
    // =============================
    public function updatePassword(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'password' => 'required|min:4'
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);
        $pegawai->password = Hash::make($request->password);
        $pegawai->save();

        return back()->with('success', 'Password berhasil diubah');
    }

    // =============================
    // HAPUS PEGAWAI
    // =============================
    public function destroy($id)
    {
        Pegawai::findOrFail($id)->delete();
        return back()->with('success', 'Pegawai berhasil dihapus');
    }
}
