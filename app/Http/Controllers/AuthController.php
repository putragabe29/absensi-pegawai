<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // LOGIN PAGE
    public function loginPage()
    {
        return view('auth.login');
    }

    // LOGIN WEB
    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required'
        ]);

        $pegawai = Pegawai::where('nip', $request->nip)->first();

        if (!$pegawai || !Hash::check($request->password, $pegawai->password)) {
            return back()->with('error', 'NIP atau password salah.');
        }

        // 🔥 LOGIN + REGENERATE SESSION (WAJIB)
        Auth::login($pegawai);
        $request->session()->regenerate();

        // 🔥 REDIRECT ABSOLUTE & AMAN
        if ($pegawai->role === 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        if ($pegawai->role === 'pegawai') {
            return redirect()->to('/absensi');
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Role tidak dikenali.');
    }

    // LOGIN API (ANDROID – JSON)
    public function apiLogin(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required'
        ]);

        $pegawai = Pegawai::where('nip', $request->nip)->first();

        if (!$pegawai || !Hash::check($request->password, $pegawai->password)) {
            return response()->json([
                'success' => false,
                'message' => 'NIP atau password salah'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'id' => $pegawai->id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'role' => $pegawai->role,
            ]
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
    public function webviewLogin(Request $request)
{
    $nip = $request->query('nip');
    $token = $request->query('token');

    if (!$nip || !$token) {
        abort(403, 'Token tidak valid');
    }

    $pegawai = Pegawai::where('nip', $nip)->first();

    if (!$pegawai) {
        abort(403, 'User tidak ditemukan');
    }

    // token sederhana (bisa kamu upgrade ke JWT nanti)
    if (!hash_equals(sha1($pegawai->password), $token)) {
        abort(403, 'Token salah');
    }

    Auth::login($pegawai);
    $request->session()->regenerate();

    return redirect()->to(
        $pegawai->role === 'admin'
            ? '/admin/dashboard'
            : '/absensi'
    );
}

}
