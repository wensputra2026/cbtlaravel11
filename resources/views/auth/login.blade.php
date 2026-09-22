<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login CBT | {{ $setting->nama_aplikasi ?? 'CBT-SMANBEN' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $logo_app = !empty($setting->logo_kiri) && file_exists(public_path($setting->logo_kiri)) 
            ? asset($setting->logo_kiri) 
            : asset('favicon.png');
        $logo_kanan = !empty($setting->logo_kanan) && file_exists(public_path($setting->logo_kanan)) 
            ? asset($setting->logo_kanan) 
            : null;
    @endphp
    <link rel="icon" type="image/png" href="{{ $appSetting->favicon_url ?? $logo_app }}">
    <link rel="shortcut icon" href="{{ $appSetting->favicon_url ?? $logo_app }}" type="image/x-icon">

    <!-- Fonts & Styling -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1e293b;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            position: relative;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }
        .brand-logos img {
            height: 52px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06));
        }
        .brand-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        .brand-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
        }
        .alert-box {
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 500;
            margin-bottom: 20px;
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-input {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px 12px 42px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-input:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: color 0.2s, background-color 0.2s;
        }
        .password-toggle:hover {
            color: #1e293b;
            background: #e2e8f0;
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 13px;
            font-size: 14.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .footer-note {
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }
        .security-tag {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: #059669;
            font-weight: 600;
            margin-top: 14px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Brand Header -->
        <div class="brand-header">
            <div class="brand-logos">
                @if(!empty($setting->logo_kiri_url))
                    <img src="{{ $setting->logo_kiri_url }}" alt="Logo">
                @elseif(!empty($logo_app))
                    <img src="{{ $logo_app }}" alt="Logo">
                @endif
                @if(!empty($setting->logo_kanan_url) && $setting->logo_kanan_url !== ($setting->logo_kiri_url ?? ''))
                    <img src="{{ $setting->logo_kanan_url }}" alt="Logo Kanan">
                @endif
            </div>
            
            <div class="brand-badge">⚡ {{ $setting->sekolah ?? 'SMA Negeri Benlutu' }}</div>
            <h1 class="brand-title">{{ $setting->nama_aplikasi ?? 'CBT-SMANBEN' }}</h1>
            <p class="brand-subtitle">Masukkan akun peserta ujian, guru, atau administrator</p>
        </div>

        <!-- Flash & Error Messages -->
        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="alert-box alert-danger">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') ?? ($errors->first() ?? '') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="alert-box alert-success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf
            <input type="hidden" name="device_id" id="deviceId">

            <div class="form-group">
                <label class="form-label" for="username">Username / NISN / NIP</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <input type="text" id="username" name="username" class="form-input" 
                           placeholder="Contoh: siswa123 atau nisn" value="{{ old('username') }}" 
                           required autofocus autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Masukkan password Anda" required autocomplete="current-password"
                           style="padding-right: 48px;">
                    <button type="button" id="togglePassword" class="password-toggle" title="Tampilkan/Sembunyikan Password" aria-label="Toggle password">
                        <svg id="eyeOpenIcon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg id="eyeSlashIcon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                <span>Masuk Sekarang</span>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>

        <div class="security-tag">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Single-Device Lock & Anti-Cheat Aktif</span>
        </div>

        <div class="footer-note">
            {{ $setting->nama_aplikasi ?? 'CBT-SMANBEN' }} &bull; SMA Negeri Benlutu &bull; Ujian Berbasis Komputer
        </div>
    </div>

    <script>
        // Password Eye Toggle
        (function() {
            const toggleBtn = document.getElementById('togglePassword');
            const pwdInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpenIcon');
            const eyeSlash = document.getElementById('eyeSlashIcon');

            if (toggleBtn && pwdInput) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (pwdInput.type === 'password') {
                        pwdInput.type = 'text';
                        eyeOpen.style.display = 'none';
                        eyeSlash.style.display = 'block';
                    } else {
                        pwdInput.type = 'password';
                        eyeOpen.style.display = 'block';
                        eyeSlash.style.display = 'none';
                    }
                });
            }
        })();

        // Browser Device Fingerprint untuk Single-Device Lock
        (function() {
            let dId = localStorage.getItem('cbt_device_id');
            if (!dId) {
                dId = 'dev_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now().toString(36);
                localStorage.setItem('cbt_device_id', dId);
            }
            document.getElementById('deviceId').value = dId;
        })();

        // Disable button on submit to avoid duplicate clicks
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span>Memeriksa Akun...</span>';
        });
    </script>
</body>
</html>
