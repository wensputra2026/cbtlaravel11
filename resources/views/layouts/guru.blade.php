<!DOCTYPE html>
<html lang="id" class="h-full" :class="{ 'dark': darkMode }" x-data="{ sidebarOpen: false, darkMode: false }" x-init="
    if (localStorage.getItem('cbt_theme_pref_v2') !== 'set') {
        localStorage.setItem('cbt_theme', 'light');
        localStorage.setItem('cbt_theme_pref_v2', 'set');
    }
    darkMode = localStorage.getItem('cbt_theme') === 'dark';
">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Guru & Pengawas') | {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</title>
    <link rel="icon" type="image/png" href="{{ $appSetting->logo_kiri_url ?? asset('favicon.png') }}">

    <script>
        // Paksa tema default menjadi TERANG (Light Mode)
        if (localStorage.getItem('cbt_theme_pref_v2') !== 'set') {
            localStorage.setItem('cbt_theme', 'light');
            localStorage.setItem('cbt_theme_pref_v2', 'set');
        }
        if (localStorage.getItem('cbt_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Local Vendor Scripts (100% Full Offline Ready) -->
    <script src="{{ asset('assets/vendor/tailwind.min.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/alpine.min.js') }}"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
    @stack('styles')
</head>
@php
    $teacherScope = app(\App\Services\Teacher\TeacherScopeService::class);
    $teacherProfile = $teacherScope->getTeacherProfile();
    $teacherAssignment = $teacherProfile ? $teacherScope->getTeacherAssignment($teacherProfile) : null;
@endphp
<body class="h-full bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 flex overflow-hidden">

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <!-- SIDEBAR GURU -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 shrink-0 shadow-sm"
    >
        <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-200 dark:border-slate-800 shrink-0">
            @if(!empty($appSetting->logo_kiri_url))
                <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
            @else
                <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center font-black text-white text-base shadow-sm">G</div>
            @endif
            <div class="leading-tight truncate">
                <h2 class="font-black text-slate-900 dark:text-white text-sm tracking-wide truncate">{{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</h2>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold truncate">Portal Guru & Pengawas</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <!-- 1. Dashboard -->
            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">1. Dashboard</div>
            <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard Guru</span>
            </a>

            @if($teacherAssignment && $teacherAssignment['is_wali_kelas'] && $teacherAssignment['wali_kelas'])
            <!-- Wali Kelas Section -->
            <div class="pt-4 px-3 pb-1 flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                <span>Wali Kelas ({{ $teacherAssignment['wali_kelas']->nama_kelas }})</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold">WALI</span>
            </div>
            <a href="{{ route('guru.wali.siswa') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.wali.siswa') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Siswa Kelas Bimbingan</span>
            </a>
            <a href="{{ route('guru.wali.struktur') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.wali.struktur') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Struktur Organisasi</span>
            </a>
            <a href="{{ route('guru.wali.catatan') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.wali.catatan') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Catatan Bimbingan</span>
            </a>
            @endif

            <!-- 2. Bank Soal -->
            <div class="pt-4 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">2. Bank Soal</div>
            <a href="{{ route('guru.bank_soal.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.bank_soal.index') || request()->routeIs('guru.bank_soal.show') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Bank Soal Saya</span>
            </a>
            <a href="{{ route('guru.bank_soal.template', 'csv') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Format Soal</span>
            </a>

            <!-- 3. Jadwal & Pengawasan -->
            <div class="pt-4 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">3. Jadwal & Pengawasan</div>
            <a href="{{ route('guru.jadwal.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.jadwal.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Jadwal Ujian Saya</span>
            </a>
            <a href="{{ route('guru.pengawasan.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.pengawasan.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Monitoring Siswa</span>
            </a>
            <a href="{{ route('guru.token.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.token.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>Token Ujian</span>
            </a>

            <!-- 4. Hasil & Penilaian -->
            <div class="pt-4 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">4. Hasil & Penilaian</div>
            <a href="{{ route('guru.hasil.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.hasil.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Hasil Ujian</span>
            </a>
            <a href="{{ route('guru.koreksi.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.koreksi.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Koreksi Soal Esai</span>
            </a>
            <a href="{{ route('guru.analisis.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.analisis.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                <span>Analisis Butir Soal</span>
            </a>

            <!-- 5. Pengaturan Akun -->
            <div class="pt-4 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">5. Pengaturan Akun</div>
            <a href="{{ route('guru.profil') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('guru.profil') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profil & Ganti Password</span>
            </a>
        </nav>

        <div class="p-3 border-t border-slate-200 dark:border-slate-800 shrink-0">
            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50">
                <div class="flex items-center gap-2 truncate">
                    <div class="w-8 h-8 rounded-full bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center font-bold text-xs text-emerald-600 dark:text-emerald-300">
                        {{ strtoupper(substr($teacherProfile->nama_guru ?? Auth::user()->username ?? 'G', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $teacherProfile->nama_guru ?? Auth::user()->first_name ?? Auth::user()->username }}</div>
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold truncate">
                            @if($teacherAssignment && $teacherAssignment['is_wali_kelas'] && $teacherAssignment['wali_kelas'])
                                Wali Kelas {{ $teacherAssignment['wali_kelas']->nama_kelas }}
                            @else
                                Guru Pengampu Mapel
                            @endif
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-lg transition" title="Keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- CONTENT AREA -->
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-100 dark:bg-slate-950 transition-colors">
        <!-- Top Navigation Bar -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 z-30 shrink-0 shadow-sm transition-colors">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base font-bold text-slate-800 dark:text-white">@yield('page_title', 'Portal Guru')</h1>
            </div>

            <div class="flex items-center gap-3">
                @if($teacherAssignment && $teacherAssignment['active_tp'] && $teacherAssignment['active_smt'])
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        <span class="text-slate-400">TP:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $teacherAssignment['active_tp']->tahun }}</span>
                        <span class="text-slate-300 dark:text-slate-600">|</span>
                        <span class="text-slate-400">Smt:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $teacherAssignment['active_smt']->smt }} ({{ $teacherAssignment['active_smt']->nama_smt }})</span>
                    </div>
                @endif

                <!-- Theme Toggle Button (Terang / Gelap) -->
                <button @click="darkMode = !darkMode; localStorage.setItem('cbt_theme', darkMode ? 'dark' : 'light')" 
                        class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition text-xs font-semibold flex items-center gap-1.5 shadow-sm"
                        :title="darkMode ? 'Beralih ke Tema Terang' : 'Beralih ke Tema Gelap'">
                    <span x-show="!darkMode" class="flex items-center gap-1">🌙 <span class="hidden sm:inline">Mode Gelap</span></span>
                    <span x-show="darkMode" class="flex items-center gap-1">☀️ <span class="hidden sm:inline">Mode Terang</span></span>
                </button>

                <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Terkoneksi Server</span>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="mx-6 mt-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center justify-between shadow-sm">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 font-bold">&times;</button>
            </div>
        @endif
        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="mx-6 mt-4 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-300 text-xs font-medium flex items-center justify-between shadow-sm">
                <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 font-bold">&times;</button>
            </div>
        @endif

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 w-full">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
