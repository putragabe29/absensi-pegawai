@extends('layouts.empty')

@section('content')
<style>
    :root {
        --primary: #F47C20;
        --primary-dark: #D96516;
        --gold: #FFD180;
        --card-light: #ffffff;
        --card-dark: #2A2A2A;
        --text-light: #333;
        --text-dark: #f1f1f1;
    }

    html, body {
        height: 100%;
        margin: 0;
    }

    /* CENTER PALING AMAN UNTUK WEBVIEW */
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #F47C20, #FF9F4A, #FFD180);
    }

    body.dark {
        background: linear-gradient(135deg, #1E1E1E, #2A2A2A);
        color: var(--text-dark);
    }

    body.light {
        color: var(--text-light);
    }

    .login-wrapper {
        width: 100%;
        max-width: 420px;
        padding: 16px;
    }

    .login-card {
        background: var(--card-light);
        border-radius: 20px;
        padding: 32px 26px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18);
        width: 100%;
        transition: 0.3s;
    }

    body.dark .login-card {
        background: var(--card-dark);
        box-shadow: 0 0 25px rgba(255,255,255,0.06);
    }

    .title {
        font-size: 26px;
        font-weight: 700;
        text-align: center;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .subtitle {
        text-align: center;
        opacity: 0.8;
        margin-bottom: 22px;
    }

    .btn-login {
        width: 100%;
        background: linear-gradient(135deg, #F47C20, #F15A24);
        border: none;
        padding: 12px;
        color: #fff;
        font-weight: 600;
        border-radius: 12px;
        margin-top: 10px;
    }

    /* DARK MODE BUTTON */
    .dark-toggle {
        position: fixed;
        top: 16px;
        right: 16px;
        background: #fff;
        border-radius: 50%;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        z-index: 10;
    }

    body.dark .dark-toggle {
        background: #333;
        color: #fff;
    }
</style>

<div class="dark-toggle" onclick="toggleDarkMode()">🌙</div>

<div class="login-wrapper">
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
                <label class="fw-semibold">NIP</label>
                <input type="text" name="nip" class="form-control form-control-lg" required>
            </div>

            <div class="mb-3">
                <label class="fw-semibold">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>

            <button type="submit" class="btn-login">
                🔐 Masuk
            </button>
        </form>

    </div>
</div>

<script>
    function toggleDarkMode() {
        let mode = localStorage.getItem("theme") || "light";
        mode = mode === "light" ? "dark" : "light";
        document.body.className = mode;
        localStorage.setItem("theme", mode);
    }

    document.body.className = localStorage.getItem("theme") || "light";
</script>
@endsection
