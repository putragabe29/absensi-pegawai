<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Absensi KPU</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary: #F47C20;
            --primary-dark: #E65100;
            --accent: #FFB347;
            --bg-light: #f3f4f6;
            --card-light: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
        }

        [data-theme="dark"] {
            --bg-light: #0f172a;
            --card-light: #111827;
            --text-main: #e5e7eb;
            --text-muted: #9ca3af;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at top, #FFD180 0, #FF9F4A 35%, #F47C20 70%, #663C00 100%);
            min-height: 100vh;
            margin: 0;
            color: var(--text-main);
        }

        .app-shell {
            max-width: 1180px;
            margin: 32px auto;
            padding: 0 16px 24px;
        }

        .app-card {
            background: var(--card-light);
            border-radius: 20px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.35);
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(249, 250, 251, 0.1);
        }

        /* Top App Bar */
        .app-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 22px;
            background: linear-gradient(90deg, #F47C20, #FF9F4A, #FFD180);
            color: #fff;
        }

        .app-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .app-brand-logo {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: rgba(255,255,255,0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .app-brand-text {
            line-height: 1.1;
        }

        .app-brand-title {
            font-weight: 700;
            font-size: 18px;
        }

        .app-brand-sub {
            font-size: 12px;
            opacity: 0.9;
        }

        .app-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-chip {
            border-radius: 999px;
            border: none;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-chip-ghost {
            background: rgba(255,255,255,0.16);
            color: #fff;
        }

        .btn-chip-ghost:hover {
            background: rgba(255,255,255,0.28);
        }

        .btn-chip-outline {
            background: #fff;
            color: #F47C20;
        }

        .btn-chip-outline:hover {
            background: #FFF7ED;
        }

        .theme-toggle {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.55);
            background: rgba(15,23,42,0.10);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: pointer;
        }

        .app-body {
            padding: 20px 22px 24px;
            background: var(--card-light);
        }

        .footer {
            text-align: center;
            color: #fff;
            margin-top: 18px;
            font-size: 13px;
            opacity: 0.85;
        }

        [data-theme="dark"] .footer {
            color: #e5e7eb;
        }

        /* Global helpers */
        .page-title {
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-muted);
        }

        .card-soft {
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: radial-gradient(circle at top left, #FFF7ED 0, #ffffff 40%, #ffffff 100%);
            padding: 16px 18px;
            margin-top: 16px;
        }

        [data-theme="dark"] .card-soft {
            background: radial-gradient(circle at top left, #1f2937 0, #111827 55%, #020617 100%);
        }

        .badge-soft {
            border-radius: 999px;
            padding: 2px 12px;
            font-size: 11px;
            font-weight: 500;
            background: rgba(15, 23, 42, 0.03);
            color: var(--text-muted);
        }

        /* Table tweak for dark mode */
        [data-theme="dark"] table.table {
            color: #e5e7eb;
        }
        [data-theme="dark"] .table thead tr {
            background-color: #111827 !important;
        }
        [data-theme="dark"] .table tbody tr {
            background-color: #020617;
        }
        [data-theme="dark"] .table-bordered > :not(caption) > * > * {
            border-color: #1f2937;
        }

        @media (max-width: 768px) {
            .app-shell {
                margin: 20px auto;
                padding: 0 10px 18px;
            }
            .app-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .app-actions {
                align-self: stretch;
                justify-content: space-between;
            }
            .app-body {
                padding: 16px 14px 20px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="app-shell">
    <div class="app-card">
        {{-- TOP APP BAR --}}
        <div class="app-bar">
            <div class="app-brand">
                <div class="app-brand-logo">
                    K
                </div>
                <div class="app-brand-text">
                    <div class="app-brand-title">Absensi KPU</div>
                    <div class="app-brand-sub">Sistem Kehadiran Pegawai</div>
                </div>
            </div>

            <div class="app-actions">
                {{-- Dark / Light Toggle --}}
                <button type="button" id="themeToggle" class="theme-toggle" title="Mode Terang/Gelap">
                    🌙
                </button>

                @auth
                    @if(Auth::user()->role === 'pegawai')
                        <a href="{{ url('/absensi') }}" class="btn-chip btn-chip-ghost">
                            📍 Absensi
                        </a>
                        <a href="{{ url('/izin') }}" class="btn-chip btn-chip-ghost">
                            📝 Izin / Cuti
                        </a>
                        <a href="{{ url('/riwayat') }}" class="btn-chip btn-chip-ghost">
                            🗂 Riwayat
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="btn-chip btn-chip-ghost">
                            📊 Dashboard Admin
                        </a>
                    @endif

                    <a href="{{ route('logout') }}" class="btn-chip btn-chip-outline">
                        🚪 Logout
                    </a>
                @endauth
            </div>
        </div>

        {{-- PAGE BODY --}}
        <div class="app-body">
            @yield('content')
        </div>
    </div>

    <div class="footer">
        © {{ date('Y') }} Komisi Pemilihan Umum · Sistem Absensi Pegawai
    </div>
</div>

<script>
    (function () {
        const root = document.documentElement;
        const stored = localStorage.getItem('theme-mode') || 'light';
        root.setAttribute('data-theme', stored);

        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            const refreshIcon = () => {
                const mode = root.getAttribute('data-theme');
                toggleBtn.textContent = mode === 'dark' ? '☀️' : '🌙';
            };
            refreshIcon();

            toggleBtn.addEventListener('click', () => {
                const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', current);
                localStorage.setItem('theme-mode', current);
                refreshIcon();
            });
        }
    })();

    @if(session('error'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Sesi Berakhir',
    text: '{{ session("error") }}',
    confirmButtonColor: '#F47C20'
});
</script>
@endif

</script>

@stack('scripts')

<script>
    // Cek jika halaman login muncul di dalam WebView / halaman protected
    if (
        window.location.pathname !== '/login' &&
        document.body.innerText.includes('Login')
    ) {
        alert('Sesi Anda telah habis. Silakan login kembali.');
        window.location.href = '/login';
    }
</script>

</body>
</html>
