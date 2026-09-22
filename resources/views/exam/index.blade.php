<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Beranda Peserta CBT | {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</title>
    <link rel="icon" type="image/png" href="{{ $appSetting->favicon_url ?? asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ $appSetting->favicon_url ?? asset('favicon.png') }}">

    <script>
        document.documentElement.classList.remove('dark');
        localStorage.setItem('cbt_theme', 'light');
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
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="min-h-full bg-slate-50 text-slate-800 flex flex-col antialiased selection:bg-brand-500 selection:text-white">

    <!-- Top Navbar -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-3">
            @if(!empty($appSetting->logo_kiri_url))
                <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
            @else
                <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center font-black text-white text-base">C</div>
            @endif
            <div class="leading-tight">
                <h1 class="font-black text-slate-900 text-sm tracking-wide">{{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</h1>
                <p class="text-[11px] text-indigo-600 font-semibold truncate">{{ $appSetting->nama_sekolah_tampil ?? 'SMAN 1 BENGKULU' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden sm:block text-right leading-tight">
                <div class="text-xs font-bold text-slate-800">{{ $siswa->nama ?? $user->nama_lengkap ?? $user->username }}</div>
                <div class="text-[11px] text-slate-500 font-mono">{{ $nomorPeserta }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Flash Message Alerts -->
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-700 text-xs font-semibold flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- 1. KARTU IDENTITAS PESERTA (STANDAR GARUDA CBT)                       -->
        <!-- ===================================================================== -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10">
                <!-- Foto Siswa -->
                <div class="relative shrink-0">
                    <div class="w-24 h-28 sm:w-28 sm:h-32 rounded-2xl bg-slate-50 border-2 border-indigo-200 p-1 shadow-sm overflow-hidden flex items-center justify-center">
                        @if($fotoSiswa)
                            <img src="{{ $fotoSiswa }}" alt="Foto Peserta" class="w-full h-full object-cover rounded-xl" onerror="this.style.display='none'; document.getElementById('defaultAvatarSiswa').style.display='flex';">
                        @endif
                        <div id="defaultAvatarSiswa" class="w-full h-full bg-slate-100 rounded-xl flex flex-col items-center justify-center text-slate-400" style="{{ $fotoSiswa ? 'display: none;' : 'display: flex;' }}">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[10px] font-bold mt-1 text-slate-400">SISWA</span>
                        </div>
                    </div>
                    <div class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-md bg-emerald-600 text-white font-black text-[10px] uppercase tracking-wider shadow-sm">
                        Aktif
                    </div>
                </div>

                <!-- Informasi Peserta -->
                <div class="flex-1 text-center md:text-left space-y-3 w-full">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Kartu Identitas Peserta Ujian</div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-wide mt-0.5">
                            {{ $siswa->nama ?? $user->nama_lengkap ?? $user->username }}
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-slate-100 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Nomor Peserta:</span>
                            <strong class="font-mono text-emerald-600 text-sm font-bold">{{ $nomorPeserta }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">NIS / NISN:</span>
                            <strong class="font-mono text-slate-800 text-xs font-semibold">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Kelas & Rombel:</span>
                            <strong class="text-slate-800 text-xs font-semibold">{{ $namaKelas }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Ruang / Sesi:</span>
                            <strong class="text-indigo-600 text-xs font-semibold">{{ $namaRuang }} &bull; {{ $namaSesi }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 2. DAFTAR JADWAL UJIAN HARI INI                                       -->
        <!-- ===================================================================== -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    Jadwal Ujian Tersedia Hari Ini
                </h3>
                <span class="text-xs text-slate-500 font-semibold">{{ count($jadwals) }} Ujian Terdaftar</span>
            </div>

            @if(empty($jadwals) || count($jadwals) === 0)
                <div class="bg-white border border-slate-200/90 rounded-3xl p-12 text-center shadow-sm space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-2xl">📋</div>
                    <h4 class="text-sm font-bold text-slate-800">Tidak Ada Jadwal Ujian Aktif</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Saat ini tidak ada sesi ujian yang dijadwalkan untuk kelas atau rombel Anda. Silakan hubungi proktor atau guru pengawas ruang jika tes seharusnya sudah dimulai.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($jadwals as $j)
                        <div class="bg-white border border-slate-200/90 hover:border-indigo-300 rounded-2xl p-5 shadow-sm flex flex-col justify-between transition-all duration-200 hover:-translate-y-1">
                            <div>
                                <!-- Status Badge -->
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    @if($j['status_exam'] === 'selesai')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            &check; Selesai
                                        </span>
                                    @elseif($j['status_exam'] === 'sedang')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            Sedang Dikerjakan
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Siap Dikerjakan
                                        </span>
                                    @endif

                                    @if($j['is_token'])
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Wajib Token
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                            Tanpa Token
                                        </span>
                                    @endif
                                </div>

                                <!-- Nama Ujian & Mapel -->
                                <h4 class="font-bold text-slate-900 text-base leading-snug line-clamp-2">
                                    {{ $j['nama_ujian'] }}
                                </h4>
                                <div class="text-xs text-indigo-600 font-semibold mt-1">
                                    {{ $j['mapel'] }}
                                </div>

                                <!-- Meta Info -->
                                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                                    <div>
                                        <span class="text-[10px] uppercase tracking-wider block text-slate-400">Alokasi Waktu:</span>
                                        <strong class="text-slate-800 font-bold">{{ $j['durasi_menit'] }} Menit</strong>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase tracking-wider block text-slate-400">Total Soal:</span>
                                        <strong class="text-slate-800 font-bold">{{ $j['total_soal'] }} Butir</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="mt-5 pt-3 border-t border-slate-100">
                                @if($j['status_exam'] === 'selesai')
                                    <a href="{{ route('exam.hasil', $j['id_jadwal']) }}" class="block w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl text-center transition border border-slate-200">
                                        Lihat Hasil Tes &rarr;
                                    </a>
                                @elseif($j['status_exam'] === 'sedang')
                                    <a href="{{ route('cbt.ujian', $j['id_jadwal']) }}" class="block w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl text-center transition shadow-md shadow-amber-600/20">
                                        Lanjutkan Pengerjaan &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('exam.konfirmasi', $j['id_jadwal']) }}" class="block w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl text-center transition shadow-md shadow-indigo-600/20">
                                        Ikuti Ujian Sekarang &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <footer class="py-5 text-center text-xs text-slate-400 border-t border-slate-200 mt-auto bg-white">
        {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }} &bull; SMAN 1 BENGKULU
    </footer>

</body>
</html>
