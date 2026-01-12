@extends('layouts.empty') {{-- Layout polos tanpa navbar --}}

@section('content')
<style>
    :root {
        --primary: #F47C20;
        --primary-dark: #D96516;
        --gold: #FFD180;
        --bg-light: #fff;
        --bg-dark: #1E1E1E;
        --text-light: #333;
        --text-dark: #f1f1f1;
        --card-light: #ffffff;
        --card-dark: #2A2A2A;
    }

    /* Auto dark mode */
    body.dark {
        background: linear-gradient(135deg, #1E1E1E, #2A2A2A);
        color: var(--text-dark);
    }

    body.light {
        background: linear-gradient(135deg, #F47C20, #FF9F4A, #FFD180);
        color: var(--text-light);
    }

    .login-card {
        background: var(--card-light);
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        max-width: 420px;
        width: 90%;
        margin: 60px auto;
        transition: 0.3s;
    }

    body.dark .login-card {
        background: var(--card-dark);
        box-shadow: 0 0 25px rgba(255,255,255,0.06);
    }

    .title {
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        margin-bottom: 5px;
        color: var(--primary);
    }

    .subtitle {
        text-align: center;
        opacity: 0.8;
        margin-bottom: 25px;
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
        transition: 0.2s;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    /* Toggle dark mode */
    .dark-toggle {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #fff;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    body.dark .dark-toggle {
        background: #333;
        color: #fff;
    }

</style>

<div class="dark-toggle" onclick="toggleDarkMode()">
    🌙
</div>

<div class="login-card">
    <div class="title">Aplikasi Absensi</div>
    <div class="subtitle">KPU Kabupaten — Login Pegawai</div>

    {{-- ALERT --}}
    @if(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
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
            🔐 Masuk Sekarang
        </button>
    </form>
</div>

<script>
    function toggleDarkMode() {
        let mode = localStorage.getItem("theme") || "light";
        mode = mode === "light" ? "dark" : "light";
        document.body.className = mode;
        localStorage.setItem("theme", mode);
    }

    // Load theme
    document.body.className = localStorage.getItem("theme") || "light";
</script>

@endsection
