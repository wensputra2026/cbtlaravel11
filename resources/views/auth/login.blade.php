<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Peserta Ujian | {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</title>
    <link rel="icon" type="image/png" href="{{ $appSetting->logo_kiri_url ?? asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            width: 100%;
            max-width: 440px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            color: #f8fafc;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
            border: 1px solid rgba(96, 165, 250, 0.3);
        }
        .brand-title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 6px;
        }
        .brand-subtitle {
            font-size: 14px;
            color: #94a3b8;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .form-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1.5px solid #334155;
            border-radius: 12px;
            padding: 13px 16px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: color 0.2s ease, background-color 0.2s ease;
        }
        .password-toggle:hover {
            color: #f8fafc;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .password-toggle:focus {
            outline: none;
            color: #38bdf8;
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .footer-note {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-top: 28px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 14px; gap: 12px;">
                @if(!empty($appSetting->logo_kiri_url))
                    <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" style="height: 52px; width: auto; object-fit: contain;">
                @endif
                @if(!empty($appSetting->logo_kanan_url) && $appSetting->logo_kanan_url !== $appSetting->logo_kiri_url)
                    <img src="{{ $appSetting->logo_kanan_url }}" alt="Logo Kanan" style="height: 52px; width: auto; object-fit: contain;">
                @endif
            </div>
            <div class="brand-badge">⚡ {{ $appSetting->nama_sekolah_tampil ?? 'SMA Negeri Benlutu' }}</div>
            <h1 class="brand-title">{{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</h1>
            <p class="brand-subtitle">Silakan masukkan akun ujian Anda untuk memulai</p>
        </div>

        @if($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert" style="background: rgba(34, 197, 94, 0.15); border-color: rgba(34, 197, 94, 0.4); color: #86efac;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf
            <input type="hidden" name="device_id" id="deviceId">

            <div class="form-group">
                <label class="form-label" for="username">Username / NISN / NIP</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-input" 
                           placeholder="Contoh: siswa123 atau nisn" value="{{ old('username') }}" 
                           required autofocus autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Masukkan password Anda" required autocomplete="current-password"
                           style="padding-right: 48px;">
                    <button type="button" id="togglePassword" class="password-toggle" title="Tampilkan/Sembunyikan Password" aria-label="Toggle password visibility">
                        <!-- Icon Mata Terbuka -->
                        <svg id="eyeOpenIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width: 20px; height: 20px; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <!-- Icon Mata Tertutup / Silang -->
                        <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width: 20px; height: 20px; display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Masuk
            </button>
        </form>

        <div class="footer-note">
            {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }} High-Concurrency Edition &bull; Laravel 11 + Octane
        </div>
    </div>

    <script>
        // Toggle Password Visibility (Ikon Mata)
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

        // Generate Browser Device Fingerprint untuk Single-Device Lock
        (function() {
            let dId = localStorage.getItem('cbt_device_id');
            if (!dId) {
                dId = 'dev_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now().toString(36);
                localStorage.setItem('cbt_device_id', dId);
            }
            document.getElementById('deviceId').value = dId;
        })();
    </script>
</body>
</html>
