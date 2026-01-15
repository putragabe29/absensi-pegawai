@extends('layouts.empty')

@section('content')
<style>
:root {
    --primary: #F47C20;
    --primary-dark: #D96516;
    --bg-light: #fff;
    --bg-dark: #1E1E1E;
    --card-light: #ffffff;
    --card-dark: #2A2A2A;
}

/* ===== BACKGROUND ===== */
body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #F47C20, #FF9F4A, #FFD180);
}

body.dark {
    background: linear-gradient(135deg, #1E1E1E, #2A2A2A);
}

/* ===== LOGIN CARD ===== */
.login-card {
    background: var(--card-light);
    border-radius: 22px;
    padding: 32px;
    width: 92%;
    max-width: 420px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.18);
}

body.dark .login-card {
    background: var(--card-dark);
}

/* ===== TITLE ===== */
.title {
    font-size: 28px;
    font-weight: 700;
    text-align: center;
    color: var(--primary);
}

.subtitle {
    text-align: center;
    opacity: 0.8;
    margin-bottom: 28px;
}

/* ===== INPUT ===== */
label {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

.form-control-lg {
    font-size: 18px;
    padding: 14px 16px;
    border-radius: 14px;
}

/* ===== PASSWORD TOGGLE ===== */
.password-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    cursor: pointer;
    opacity: 0.6;
}

/* ===== BUTTON ===== */
.btn-login {
    width: 100%;
    background: linear-gradient(135deg, #F47C20, #F15A24);
    border: none;
    padding: 14px;
    font-size: 17px;
    font-weight: 600;
    border-radius: 14px;
    margin-top: 16px;
    color: #fff;
}

.btn-login:active {
    transform: scale(0.98);
}

/* ===== DARK TOGGLE ===== */
.dark-toggle {
    position: fixed;
    top: 18px;
    right: 18px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0,0,0,0.2);
}

body.dark .dark-toggle {
    background: #333;
    color: #fff;
}
</style>

<div class="dark-toggle" onclick="toggleDarkMode()">🌙</div>

<div class="login-card">
    <div class="title">Aplikasi Absensi</div>
    <div class="subtitle">KPU Kabupaten — Login Pegawai</div>

    @if(session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label>NIP</label>
            <input type="text"
                   name="nip"
                   class="form-control form-control-lg"
                   inputmode="numeric"
                   autocomplete="off"
                   required>
        </div>

        <div class="mb-3 password-wrapper">
            <label>Password</label>
            <input type="password"
                   name="password"
                   id="password"
                   class="form-control form-control-lg"
                   required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>

        <button type="submit" class="btn-login">
            🔐 Masuk
        </button>
    </form>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function toggleDarkMode() {
    let mode = localStorage.getItem('theme') || 'light';
    mode = mode === 'light' ? 'dark' : 'light';
    document.body.className = mode;
    localStorage.setItem('theme', mode);
}

document.body.className = localStorage.getItem('theme') || 'light';
</script>
@endsection
