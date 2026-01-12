<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::all();
        return view('admin.pegawai', compact('pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'nip' => 'required|string|max:50|unique:pegawais,nip',
            'password' => 'required|string|min:4',
            'role' => 'required'
        ]);

        Pegawai::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'password' => Hash::make($request->password), // ✅ Enkripsi password
            'role' => $request->role
        ]);

        return back()->with('success', '✅ Pegawai berhasil ditambahkan!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required',
            'password' => 'required|min:4'
        ]);

        Pegawai::where('id', $request->pegawai_id)->update([
            'password' => Hash::make($request->password) // ✅ Enkripsi password
        ]);

        return back()->with('success', '🔒 Password berhasil diubah!');
    }
}
