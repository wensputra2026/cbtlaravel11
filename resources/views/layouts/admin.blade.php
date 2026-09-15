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
    <title>@yield('title', 'Admin Panel') | {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</title>
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
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
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
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 flex overflow-hidden">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <!-- ========================================================================= -->
    <!-- SIDEBAR NAVIGASI UTAMA                                                    -->
    <!-- ========================================================================= -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 shrink-0 shadow-sm"
    >
        <!-- Brand Header -->
        <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-200 dark:border-slate-800 shrink-0">
            @if(!empty($appSetting->logo_kiri_url))
                <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
            @else
                <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center font-black text-white text-base shadow-sm">
                    {{ substr($appSetting->nama_aplikasi_tampil ?? 'S', 0, 1) }}
                </div>
            @endif
            <div class="leading-tight truncate">
                <h2 class="font-black text-slate-900 dark:text-white text-sm tracking-wide truncate">{{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $appSetting->nama_sekolah_tampil ?? 'SMA Negeri Benlutu' }}</p>
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            
            <!-- 1. DASHBOARD -->
            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">1. Dashboard</div>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- 2. DATA MASTER MENU & SUB-MENU -->
            <div x-data="{ openMaster: {{ request()->routeIs('admin.master.*') ? 'true' : 'false' }} }" class="pt-2">
                <button 
                    @click="openMaster = !openMaster" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg transition group {{ request()->routeIs('admin.master.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 font-bold border border-brand-200/60 dark:border-brand-800/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.master.*') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="tracking-wide">2. Data Master</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ request()->routeIs('admin.master.*') ? 'bg-brand-200/70 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200' }}">8</span>
                        <svg 
                            :class="openMaster ? 'rotate-90 text-brand-600 dark:text-brand-400' : 'text-slate-400'" 
                            class="w-3.5 h-3.5 transform transition-transform duration-200" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>

                <!-- Sub-menu Items Container -->
                <div 
                    x-show="openMaster" 
                    x-cloak 
                    x-transition:enter="transition ease-out duration-150" 
                    x-transition:enter-start="opacity-0 -translate-y-1" 
                    x-transition:enter-end="opacity-100 translate-y-0" 
                    x-transition:leave="transition ease-in duration-100" 
                    x-transition:leave-start="opacity-100 translate-y-0" 
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-1 ml-3.5 pl-3 border-l-2 border-slate-200 dark:border-slate-800 space-y-0.5"
                >
                    <!-- 1. Tahun Pelajaran & Smt -->
                    <a href="{{ route('admin.master.tp') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.tp') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="truncate">Tahun Pelajaran & Smt</span>
                    </a>

                    <!-- 2. Jurusan & Peminatan -->
                    <a href="{{ route('admin.master.jurusan') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.jurusan') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="truncate">Jurusan & Peminatan</span>
                    </a>

                    <!-- 3. Kelas & Rombel -->
                    <a href="{{ route('admin.master.kelas') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.kelas') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="truncate">Kelas & Rombel</span>
                    </a>

                    <!-- 4. Kenaikan Kelas & Mutasi -->
                    <a href="{{ route('admin.master.kenaikan_kelas') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.kenaikan_kelas*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span class="truncate">Kenaikan Kelas & Mutasi</span>
                    </a>

                    <!-- 5. Mata Pelajaran -->
                    <a href="{{ route('admin.master.mapel') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.mapel') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span class="truncate">Mata Pelajaran</span>
                    </a>

                    <!-- 6. Data Guru & Staf -->
                    <a href="{{ route('admin.master.guru') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.guru') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="truncate">Data Guru & Staf</span>
                    </a>

                    <!-- 7. Data Siswa & Akun -->
                    <a href="{{ route('admin.master.siswa') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.siswa') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="truncate">Data Siswa & Akun</span>
                    </a>

                    <!-- 8. Arsip Alumni Siswa -->
                    <a href="{{ route('admin.master.alumni') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.master.alumni') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        <span class="truncate">Arsip Alumni Siswa</span>
                    </a>
                </div>
            </div>

            <!-- 3. MANAJEMEN CBT MENU & SUB-MENU -->
            <div x-data="{ openCbt: {{ (request()->routeIs('admin.cbt.*') || request()->routeIs('proctor.*')) ? 'true' : 'false' }} }" class="pt-1">
                <button 
                    @click="openCbt = !openCbt" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg transition group {{ (request()->routeIs('admin.cbt.*') || request()->routeIs('proctor.*')) ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 font-bold border border-brand-200/60 dark:border-brand-800/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ (request()->routeIs('admin.cbt.*') || request()->routeIs('proctor.*')) ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="tracking-wide">3. Manajemen CBT</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ (request()->routeIs('admin.cbt.*') || request()->routeIs('proctor.*')) ? 'bg-brand-200/70 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200' }}">11</span>
                        <svg 
                            :class="openCbt ? 'rotate-90 text-brand-600 dark:text-brand-400' : 'text-slate-400'" 
                            class="w-3.5 h-3.5 transform transition-transform duration-200" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>

                <!-- Sub-menu Items Container -->
                <div 
                    x-show="openCbt" 
                    x-cloak 
                    x-transition:enter="transition ease-out duration-150" 
                    x-transition:enter-start="opacity-0 -translate-y-1" 
                    x-transition:enter-end="opacity-100 translate-y-0" 
                    x-transition:leave="transition ease-in duration-100" 
                    x-transition:leave-start="opacity-100 translate-y-0" 
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-1 ml-3.5 pl-3 border-l-2 border-slate-200 dark:border-slate-800 space-y-0.5"
                >
                    <!-- 1. Bank Soal (5 Tipe) -->
                    <a href="{{ route('admin.cbt.bank_soal.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.bank_soal.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="truncate">Bank Soal (5 Tipe)</span>
                    </a>

                    <!-- 2. Jenis Ujian -->
                    <a href="{{ route('admin.cbt.jenis.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.jenis.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span class="truncate">Jenis Ujian</span>
                    </a>

                    <!-- 3. Jadwal Ujian -->
                    <a href="{{ route('admin.cbt.jadwal.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.jadwal.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="truncate">Jadwal Ujian</span>
                    </a>

                    <!-- 4. Sesi & Ruang Ujian -->
                    <a href="{{ route('admin.cbt.sesi_ruang.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.sesi_ruang.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        <span class="truncate">Sesi & Ruang Ujian</span>
                    </a>

                    <!-- 5. Alokasi Sesi Siswa -->
                    <a href="{{ route('admin.cbt.alokasi.sesi') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.alokasi.sesi*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="truncate">Alokasi Sesi Siswa</span>
                    </a>

                    <!-- 6. Alokasi Mapel Pilihan (Fase F) -->
                    <a href="{{ route('admin.cbt.alokasi.pilihan') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.alokasi.pilihan*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span class="truncate">Mapel Pilihan (Fase F)</span>
                    </a>

                    <!-- 7. Nomor Peserta Ujian -->
                    <a href="{{ route('admin.cbt.alokasi.nomor') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.alokasi.nomor*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        <span class="truncate">Nomor Peserta Ujian</span>
                    </a>

                    <!-- 7. Token Ujian Dinamis -->
                    <a href="{{ route('admin.cbt.token.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.token.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span class="truncate">Token Ujian Dinamis</span>
                    </a>

                    <!-- 8. Live Monitoring (Proktor) -->
                    <a href="{{ route('proctor.index') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('proctor.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}" title="Buka di Tab Baru">
                        <div class="flex items-center gap-2.5 truncate">
                            <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="truncate">Live Monitoring (Proktor)</span>
                        </div>
                        <svg class="w-3 h-3 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    <!-- 9. Rekap Nilai & Koreksi -->
                    <a href="{{ route('admin.cbt.nilai.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.nilai.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span class="truncate">Rekap Nilai & Koreksi</span>
                    </a>

                    <!-- 10. Analisis Butir Soal -->
                    <a href="{{ route('admin.cbt.analisis') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.cbt.analisis*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span class="truncate">Analisis Butir Soal</span>
                    </a>
                </div>
            </div>

            <!-- 4. CETAK DOKUMEN MENU & SUB-MENU -->
            <div x-data="{ openCetak: {{ request()->routeIs('print.*') ? 'true' : 'false' }} }" class="pt-1">
                <button 
                    @click="openCetak = !openCetak" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg transition group {{ request()->routeIs('print.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 font-bold border border-brand-200/60 dark:border-brand-800/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('print.*') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span class="tracking-wide">4. Cetak Dokumen</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ request()->routeIs('print.*') ? 'bg-brand-200/70 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200' }}">5</span>
                        <svg 
                            :class="openCetak ? 'rotate-90 text-brand-600 dark:text-brand-400' : 'text-slate-400'" 
                            class="w-3.5 h-3.5 transform transition-transform duration-200" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>

                <!-- Sub-menu Items Container -->
                <div 
                    x-show="openCetak" 
                    x-cloak 
                    x-transition:enter="transition ease-out duration-150" 
                    x-transition:enter-start="opacity-0 -translate-y-1" 
                    x-transition:enter-end="opacity-100 translate-y-0" 
                    x-transition:leave="transition ease-in duration-100" 
                    x-transition:leave-start="opacity-100 translate-y-0" 
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-1 ml-3.5 pl-3 border-l-2 border-slate-200 dark:border-slate-800 space-y-0.5"
                >
                    <!-- 1. Kartu Peserta Barcode -->
                    <a href="{{ route('print.kartu_peserta') }}" target="_blank" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200 transition">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        <span class="truncate">Kartu Peserta Barcode</span>
                    </a>

                    <!-- 2. Daftar Hadir Pengawas -->
                    <a href="{{ route('print.daftar_hadir_pengawas') }}" target="_blank" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200 transition">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span class="truncate">Daftar Hadir Pengawas</span>
                    </a>

                    <!-- 3. Jadwal Pengawas Ruang -->
                    <a href="{{ route('print.jadwal_pengawas') }}" target="_blank" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200 transition">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="truncate">Jadwal Pengawas Ruang</span>
                    </a>

                    <!-- 4. Cetak Kartu Login Massal -->
                    <a href="{{ route('print.kartu_login') }}" target="_blank" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200 transition">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span class="truncate">Cetak Kartu Login Massal</span>
                    </a>

                    <!-- 5. Denah Ruang Ujian -->
                    <a href="{{ route('print.denah_ruang') }}" target="_blank" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200 transition">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"/></svg>
                        <span class="truncate">Denah Ruang Ujian</span>
                    </a>
                </div>
            </div>

            <!-- 5. MANAJEMEN PENGGUNA MENU & SUB-MENU -->
            <div x-data="{ openUsers: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }" class="pt-1">
                <button 
                    @click="openUsers = !openUsers" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg transition group {{ request()->routeIs('admin.users.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 font-bold border border-brand-200/60 dark:border-brand-800/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="tracking-wide">5. Manajemen Pengguna</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ request()->routeIs('admin.users.*') ? 'bg-brand-200/70 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200' }}">4</span>
                        <svg 
                            :class="openUsers ? 'rotate-90 text-brand-600 dark:text-brand-400' : 'text-slate-400'" 
                            class="w-3.5 h-3.5 transform transition-transform duration-200" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>

                <!-- Sub-menu Items Container -->
                <div 
                    x-show="openUsers" 
                    x-cloak 
                    x-transition:enter="transition ease-out duration-150" 
                    x-transition:enter-start="opacity-0 -translate-y-1" 
                    x-transition:enter-end="opacity-100 translate-y-0" 
                    x-transition:leave="transition ease-in duration-100" 
                    x-transition:leave-start="opacity-100 translate-y-0" 
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-1 ml-3.5 pl-3 border-l-2 border-slate-200 dark:border-slate-800 space-y-0.5"
                >
                    <!-- 1. User Admin -->
                    <a href="{{ route('admin.users.admin') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.users.admin*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="truncate">User Admin</span>
                    </a>

                    <!-- 2. User Guru -->
                    <a href="{{ route('admin.users.guru') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.users.guru*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="truncate">User Guru</span>
                    </a>

                    <!-- 3. User Siswa -->
                    <a href="{{ route('admin.users.siswa') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.users.siswa*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="truncate">User Siswa</span>
                    </a>

                    <!-- 4. Log Aktivitas -->
                    <a href="{{ route('admin.users.logs') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.users.logs*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="truncate">Log Aktivitas</span>
                    </a>
                </div>
            </div>

            <!-- 6. PENGATURAN & DB MENU & SUB-MENU -->
            <div x-data="{ openSetting: {{ request()->routeIs('admin.setting.*') ? 'true' : 'false' }} }" class="pt-1">
                <button 
                    @click="openSetting = !openSetting" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg transition group {{ request()->routeIs('admin.setting.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 font-bold border border-brand-200/60 dark:border-brand-800/40' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.setting.*') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="tracking-wide">6. Pengaturan & DB</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ request()->routeIs('admin.setting.*') ? 'bg-brand-200/70 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-200' }}">3</span>
                        <svg 
                            :class="openSetting ? 'rotate-90 text-brand-600 dark:text-brand-400' : 'text-slate-400'" 
                            class="w-3.5 h-3.5 transform transition-transform duration-200" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>

                <!-- Sub-menu Items Container -->
                <div 
                    x-show="openSetting" 
                    x-cloak 
                    x-transition:enter="transition ease-out duration-150" 
                    x-transition:enter-start="opacity-0 -translate-y-1" 
                    x-transition:enter-end="opacity-100 translate-y-0" 
                    x-transition:leave="transition ease-in duration-100" 
                    x-transition:leave-start="opacity-100 translate-y-0" 
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-1 ml-3.5 pl-3 border-l-2 border-slate-200 dark:border-slate-800 space-y-0.5"
                >
                    <!-- 1. Profil Sekolah & Logo -->
                    <a href="{{ route('admin.setting.identitas') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.setting.identitas*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="truncate">Profil Sekolah & Logo</span>
                    </a>

                    <!-- 2. Backup & Restore DB -->
                    <a href="{{ route('admin.setting.database') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.setting.database*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <span class="truncate">Backup & Restore DB</span>
                    </a>

                    <!-- 3. Bersihkan Data & Sesi -->
                    <a href="{{ route('admin.setting.maintenance') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition {{ request()->routeIs('admin.setting.maintenance*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-200' }}">
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <span class="truncate">Bersihkan Data & Sesi</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Footer & Logout -->
        <div class="p-3 border-t border-slate-200 dark:border-slate-800 shrink-0">
            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50">
                <div class="flex items-center gap-2 truncate">
                    <div class="w-8 h-8 rounded-full bg-brand-600/20 border border-brand-500/30 flex items-center justify-center font-bold text-xs text-brand-600 dark:text-brand-300">
                        {{ strtoupper(substr(Auth::user()->username ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ Auth::user()->username }}</div>
                        <div class="text-[10px] text-brand-600 dark:text-brand-400 font-semibold">Administrator</div>
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

    <!-- ========================================================================= -->
    <!-- MAIN CONTENT AREA                                                         -->
    <!-- ========================================================================= -->
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-100 dark:bg-slate-950 transition-colors">
        
        <!-- Top Navbar -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 z-30 shrink-0 shadow-sm transition-colors">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base font-bold text-slate-800 dark:text-white">@yield('page_title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Global Academic Year Selector / Switcher -->
                <form action="{{ route('admin.academic_year.switch') }}" method="POST" class="hidden sm:flex items-center gap-1.5 m-0">
                    @csrf
                    <div class="flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">T.A:</span>
                        <select name="tahun_ajaran_id" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-slate-800 dark:text-white border-none focus:outline-none cursor-pointer">
                            @if(isset($allTahunAjaran))
                                @foreach($allTahunAjaran as $yta)
                                    <option value="{{ $yta->id }}" {{ (isset($selectedTahunAjaran) && $selectedTahunAjaran->id == $yta->id) ? 'selected' : '' }} class="bg-white dark:bg-slate-900 text-slate-800 dark:text-white">
                                        {{ $yta->nama_lengkap ?? "T.P. {$yta->tahun}" }} {{ $yta->is_active ? '★ (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </form>

                <!-- Theme Toggle Button (Terang / Gelap) -->
                <button @click="darkMode = !darkMode; localStorage.setItem('cbt_theme', darkMode ? 'dark' : 'light')" 
                        class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition text-xs font-semibold flex items-center gap-1.5 shadow-sm"
                        :title="darkMode ? 'Beralih ke Tema Terang' : 'Beralih ke Tema Gelap'">
                    <span x-show="!darkMode" class="flex items-center gap-1">🌙</span>
                    <span x-show="darkMode" class="flex items-center gap-1">☀️</span>
                </button>

                <!-- Status Offline Local Ready -->
                <div class="hidden lg:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Offline LAN</span>
                </div>

                <!-- Link Cepat Live Proctor -->
                <a href="{{ route('proctor.index') }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-600/20 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 text-xs font-semibold hover:bg-sky-100 dark:hover:bg-sky-600/30 transition flex items-center gap-1.5" title="Buka Monitoring Ujian di Tab Baru">
                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <span class="hidden sm:inline">Monitoring Ujian</span>
                    <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </header>

        <!-- Archive Mode Warning Banner -->
        @if(isset($selectedTahunAjaran) && isset($activeTahunAjaran) && $selectedTahunAjaran->id !== $activeTahunAjaran->id)
            <div class="mx-4 sm:mx-6 mt-3 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>Anda sedang melihat data arsip untuk <strong>{{ $selectedTahunAjaran->nama_lengkap }}</strong>. Data ujian dan siswa yang tampil sesuai periode arsip ini.</span>
                </div>
                <form action="{{ route('admin.academic_year.switch') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="tahun_ajaran_id" value="">
                    <button type="submit" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-lg text-[10px] transition">
                        Kembali ke Tahun Aktif
                    </button>
                </form>
            </div>
        @endif

        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mx-6 mt-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-medium flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-white font-bold">&times;</button>
            </div>
        @endif
        @if(session('warning'))
            <div class="mx-6 mt-4 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs font-medium flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700 dark:hover:text-white font-bold">&times;</button>
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div class="mx-6 mt-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 dark:hover:text-white font-bold">&times;</button>
            </div>
        @endif

        <!-- Dynamic Body Page Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
