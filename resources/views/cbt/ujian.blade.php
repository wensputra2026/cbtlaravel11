<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $namaUjian }} - {{ $namaMapel }} | {{ $appSetting->nama_aplikasi_tampil }}</title>

    <!-- Tailwind CSS (Offline Local Vendor) -->
    <script src="{{ asset('assets/vendor/tailwind.min.js') }}"></script>
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

    <!-- Alpine.js 3.x (Offline Local Vendor, defer) -->
    <script defer src="{{ asset('assets/vendor/alpine.min.js') }}"></script>

    <!-- KaTeX Native LaTeX Formula Rendering (100% Offline Local Assets) -->
    <link rel="stylesheet" href="{{ asset('vendor/katex/katex.min.css') }}">
    <script src="{{ asset('vendor/katex/katex.min.js') }}"></script>
    <script src="{{ asset('vendor/katex/auto-render.min.js') }}"></script>
    <script src="{{ asset('vendor/katex/contrib/mhchem.min.js') }}"></script>

    <style>
        /* Anti-Cheat: Mencegah seleksi teks dan klik kanan */
        body {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Styling scrollbar halus */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.5);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.8);
        }

        /* Render Konten HTML Soal & Opsi */
        .soal-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: inline-block;
            cursor: zoom-in;
        }
        .soal-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        .soal-content th, .soal-content td {
            border: 1px solid #475569;
            padding: 0.5rem 0.75rem;
        }
        .soal-content p {
            margin-bottom: 0.5rem;
            line-height: 1.65;
        }

        /* Animasi Berkedip Timer Kritis (< 5 menit) */
        @keyframes timerBlink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.03); }
        }
        .animate-timer-critical {
            animation: timerBlink 1s infinite ease-in-out;
        }
    </style>
</head>

<body
    class="h-full bg-slate-950 text-slate-100 flex flex-col antialiased select-none overflow-hidden"
    x-data="cbtExam({
        jadwalId: {{ $jadwalId }},
        siswaId: {{ $siswaId }},
        apiBase: '{{ $apiBase }}',
        token: '{{ $token }}',
        pakaiToken: {{ $pakaiToken ? 'true' : 'false' }},
        csrfToken: '{{ csrf_token() }}',
        authToken: '{{ $authToken ?? '' }}',
        deviceToken: '{{ $deviceToken ?? '' }}',
        durasiDetik: {{ $durasiMenit * 60 }}
    })"
    x-init="initExam()"
    @contextmenu.prevent="handleContextMenu($event)"
    @selectstart.prevent
    @keydown.window="handleGlobalKeydown($event)"
>

    <!-- ================================================================= -->
    <!-- 1. TOP NAVIGATION & STATUS BAR                                    -->
    <!-- ================================================================= -->
    <header class="h-16 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-4 sm:px-6 z-30 shrink-0">
        
        <!-- Sisi Kiri: Identitas Ujian & Peserta -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Avatar Siswa -->
            <div class="relative">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-violet-500 p-0.5 shadow-md shadow-brand-500/20">
                    @if($foto)
                        <img src="{{ $foto }}" alt="{{ $namaSiswa }}" class="w-full h-full object-cover rounded-[10px]">
                    @else
                        <div class="w-full h-full bg-slate-800 rounded-[10px] flex items-center justify-center font-bold text-sm text-brand-300">
                            {{ strtoupper(substr($namaSiswa, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-slate-900"></div>
            </div>

            <!-- Teks Nama & Info Ujian -->
            <div class="leading-tight">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-sm sm:text-base text-white tracking-wide truncate max-w-[150px] sm:max-w-xs md:max-w-sm">
                        {{ $namaSiswa }}
                    </h2>
                    <span class="hidden md:inline-block px-2 py-0.5 text-[11px] font-semibold bg-slate-800 text-slate-300 border border-slate-700 rounded-md">
                        {{ $kelas }} &bull; {{ $ruang }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 truncate max-w-[200px] sm:max-w-xs">
                    <span class="text-brand-400 font-medium">{{ $namaMapel }}</span> &bull; {{ $namaUjian }}
                </p>
            </div>
        </div>

        <!-- Sisi Kanan: Autosave Status, Font Resizer, Timer, Fullscreen & Mobile Drawer -->
        <div class="flex items-center gap-2 sm:gap-4">
            
            <!-- Autosave Sync Indicator (Asinkron Non-Blocking) -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/80 border border-slate-700/60 text-xs shadow-inner">
                <template x-if="saveStatus === 'saving'">
                    <span class="flex items-center gap-1.5 text-amber-400 font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        <span>Menyimpan...</span>
                    </span>
                </template>
                <template x-if="saveStatus === 'synced'">
                    <span class="flex items-center gap-1.5 text-emerald-400 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Tersimpan (Synced)</span>
                    </span>
                </template>
                <template x-if="saveStatus === 'offline'">
                    <span class="flex items-center gap-1.5 text-rose-400 font-medium" title="Koneksi terputus, jawaban dicadangkan di memori perangkat lokal">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                        <span>Cadangan Offline</span>
                    </span>
                </template>
            </div>

            <!-- Font Resizer Controller (Kecil, Normal, Besar) -->
            <div class="hidden md:flex items-center bg-slate-800/90 rounded-lg p-0.5 border border-slate-700">
                <button
                    type="button"
                    @click="setFontSize('small')"
                    :class="fontSize === 'small' ? 'bg-brand-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                    class="px-2.5 py-1 text-xs rounded transition"
                    title="Ukuran Teks Kecil"
                >A-</button>
                <button
                    type="button"
                    @click="setFontSize('normal')"
                    :class="fontSize === 'normal' ? 'bg-brand-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                    class="px-2.5 py-1 text-xs rounded transition"
                    title="Ukuran Teks Normal"
                >A</button>
                <button
                    type="button"
                    @click="setFontSize('large')"
                    :class="fontSize === 'large' ? 'bg-brand-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                    class="px-2.5 py-1 text-xs rounded transition"
                    title="Ukuran Teks Besar"
                >A+</button>
            </div>

            <!-- Server Synchronized Timer (Blinking merah bila sisa waktu < 5 menit) -->
            <div
                class="flex items-center gap-2 px-3 sm:px-4 py-1.5 rounded-xl border transition-all"
                :class="sisaDetik < 300
                    ? 'bg-rose-950/90 border-rose-500 text-rose-400 animate-timer-critical shadow-lg shadow-rose-900/50'
                    : 'bg-slate-900 border-slate-700 text-amber-400 shadow-inner'"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-mono font-bold text-sm sm:text-base tracking-wider" x-text="formatTimer(sisaDetik)">
                    00:00:00
                </span>
            </div>

            <!-- Fullscreen Enforcement Button -->
            <button
                type="button"
                @click="toggleFullscreen()"
                class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition"
                :title="isFullscreen ? 'Keluar Layar Penuh' : 'Mode Layar Penuh (Full Screen)'"
            >
                <svg x-show="!isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <svg x-show="isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Toggle Drawer Nomor Soal Mobile / Tablet -->
            <button
                type="button"
                @click="showDrawerMobile = !showDrawerMobile"
                class="lg:hidden p-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold transition shadow-md shadow-brand-600/30"
                title="Buka Daftar Nomor Soal"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </header>

    <!-- ================================================================= -->
    <!-- 2. MAIN WORKSPACE (SOAL AREA + SIDEBAR NAVIGASI)                   -->
    <!-- ================================================================= -->
    <div class="flex-1 flex overflow-hidden relative">

        <!-- ============================================================= -->
        <!-- A. AREA UTAMA PENGERJAAN SOAL (KIRI / TENGAH)                 -->
        <!-- ============================================================= -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-950 relative">

            <!-- Loading Spinner State -->
            <div x-show="isLoading" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-950/90 backdrop-blur-sm">
                <div class="w-14 h-14 border-4 border-brand-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <h3 class="text-lg font-bold text-white tracking-wide">Memuat Paket Ujian...</h3>
                <p class="text-xs text-slate-400 mt-1">Mengambil butir soal dan menyinkronkan token keamanan</p>
            </div>

            <!-- Error Fatal State (Misal Ujian Belum Dibuka / Nonaktif) -->
            <div x-show="errorMessage && !isLoading" class="absolute inset-0 z-20 flex flex-col items-center justify-center p-6 text-center bg-slate-950">
                <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-500 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2" x-text="errorMessage">Terjadi Kesalahan</h3>
                <p class="text-sm text-slate-400 max-w-md mb-6">Hubungi proktor atau pengawas ruangan jika Anda mengalami kendala saat memulai sesi ujian.</p>
                <a href="{{ route('exam.index') }}" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl text-sm transition">
                    Kembali ke Beranda Ujian
                </a>
            </div>

            <!-- Header Butir Soal: Nomor Soal, Badge Tipe & Bobot -->
            <div x-show="!isLoading && !errorMessage && currentSoal" class="bg-slate-900/60 border-b border-slate-800/80 px-6 py-3 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600/20 text-brand-300 font-bold text-sm border border-brand-500/30">
                        <span x-text="currentIndex + 1">1</span>
                    </span>
                    <div>
                        <span class="text-xs text-slate-400 font-medium">Nomor Soal</span>
                        <h4 class="text-sm font-bold text-white">
                            Soal <span x-text="currentIndex + 1">1</span> dari <span x-text="soalList.length">0</span>
                        </h4>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Badge Tipe Soal -->
                    <span
                        class="px-2.5 py-1 text-xs font-semibold rounded-md border"
                        :class="getBadgeTipeSoal(currentSoal?.jenis_soal).class"
                        x-text="getBadgeTipeSoal(currentSoal?.jenis_soal).label"
                    >
                        Pilihan Ganda
                    </span>
                    <!-- Bobot Soal -->
                    <span class="hidden sm:inline-block px-2.5 py-1 text-xs font-medium bg-slate-800 text-slate-300 rounded-md border border-slate-700">
                        Bobot: <span x-text="currentSoal?.bobot || '1.0'">1.0</span>
                    </span>
                </div>
            </div>

            <!-- Konten Lembar Soal (Scrollable Container Zero-Latency) -->
            <div
                id="exam-content-area"
                x-show="!isLoading && !errorMessage && currentSoal"
                class="flex-1 overflow-y-auto px-6 sm:px-10 py-6 space-y-6"
                :class="{
                    'text-sm': fontSize === 'small',
                    'text-base': fontSize === 'normal',
                    'text-lg sm:text-xl': fontSize === 'large'
                }"
            >
                <!-- Card Pertanyaan Soal -->
                <div class="bg-slate-900/80 border border-slate-800/90 rounded-2xl p-6 shadow-xl backdrop-blur-sm">
                    <!-- HTML Pertanyaan -->
                    <div
                        class="soal-content text-slate-100 font-normal leading-relaxed break-words"
                        x-html="currentSoal?.pertanyaan || currentSoal?.soal || '<p class=\'text-slate-500 italic\'>Konten soal belum tersedia.</p>'"
                    ></div>

                    <!-- Lampiran Media / Gambar Tambahan -->
                    <template x-if="currentSoal?.media">
                        <div class="mt-4 pt-4 border-t border-slate-800">
                            <span class="text-xs text-slate-400 block mb-2 font-medium">Lampiran Gambar:</span>
                            <img
                                :src="'{{ asset('uploads/bank_soal') }}/' + currentSoal.media"
                                @error="$event.target.src = '{{ asset('uploads') }}/' + currentSoal.media"
                                alt="Gambar Soal"
                                class="max-h-96 rounded-xl border border-slate-700 shadow-md object-contain cursor-zoom-in"
                                @click="openLightbox($event.target.src)"
                            >
                        </div>
                    </template>
                </div>

                <!-- ========================================================= -->
                <!-- FORM JAWABAN (DUKUNGAN 5 TIPE SOAL LENGKAP)                -->
                <!-- ========================================================= -->
                <div class="space-y-4">
                    
                    <!-- ----------------------------------------------------- -->
                    <!-- TIPE 1: PILIHAN GANDA BIASA (PG)                      -->
                    <!-- ----------------------------------------------------- -->
                    <template x-if="currentSoal && (currentSoal.jenis_soal == 1 || currentSoal.jenis == 1 || !currentSoal.jenis_soal)">
                        <div class="space-y-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Pilihan Jawaban (Pilih Satu):</span>
                            <div class="grid grid-cols-1 gap-2.5">
                                <template x-for="(opsiItem, idx) in getFormattedOptions(currentSoal)" :key="opsiItem.label">
                                    <label
                                        @click="selectOpsi(opsiItem.label)"
                                        class="flex items-start gap-4 p-4 rounded-xl border transition-all cursor-pointer group"
                                        :class="currentJawaban === opsiItem.label
                                            ? 'bg-brand-600/15 border-brand-500 text-white shadow-md shadow-brand-500/10 ring-1 ring-brand-500'
                                            : 'bg-slate-900/60 border-slate-800 text-slate-300 hover:bg-slate-800/80 hover:border-slate-700'"
                                    >
                                        <!-- Bulatan Huruf Opsi (A, B, C, D, E) -->
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 transition-colors"
                                            :class="currentJawaban === opsiItem.label
                                                ? 'bg-brand-600 text-white shadow-sm'
                                                : 'bg-slate-800 text-slate-300 group-hover:bg-slate-700 group-hover:text-white'"
                                            x-text="opsiItem.label"
                                        ></div>

                                        <!-- Teks Opsi Jawaban (Render HTML) -->
                                        <div class="flex-1 pt-1 text-sm sm:text-base leading-snug break-words soal-content" x-html="opsiItem.content"></div>

                                        <!-- Radio Checkmark Indicator -->
                                        <div class="pt-1.5 shrink-0">
                                            <div
                                                class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                                :class="currentJawaban === opsiItem.label ? 'border-brand-500 bg-brand-600' : 'border-slate-700 bg-slate-800'"
                                            >
                                                <div x-show="currentJawaban === opsiItem.label" class="w-2 h-2 rounded-full bg-white"></div>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Tombol Hapus Pilihan -->
                            <div x-show="currentJawaban" class="pt-1 text-right">
                                <button
                                    type="button"
                                    @click="clearJawaban()"
                                    class="text-xs text-rose-400 hover:text-rose-300 font-medium underline transition"
                                >
                                    Hapus Pilihan Jawaban
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- ----------------------------------------------------- -->
                    <!-- TIPE 2: PILIHAN GANDA KOMPLEKS (MULTI-CHECKBOX)       -->
                    <!-- ----------------------------------------------------- -->
                    <template x-if="currentSoal && (currentSoal.jenis_soal == 2 || currentSoal.jenis == 2)">
                        <div class="space-y-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Pilihan Jawaban (Dapat Memilih Lebih dari Satu):</span>
                            <div class="grid grid-cols-1 gap-2.5">
                                <template x-for="(opsiItem, idx) in getFormattedOptions(currentSoal)" :key="opsiItem.label">
                                    <label
                                        @click="toggleOpsiKompleks(opsiItem.label)"
                                        class="flex items-start gap-4 p-4 rounded-xl border transition-all cursor-pointer group"
                                        :class="isOpsiKompleksSelected(opsiItem.label)
                                            ? 'bg-brand-600/15 border-brand-500 text-white shadow-md ring-1 ring-brand-500'
                                            : 'bg-slate-900/60 border-slate-800 text-slate-300 hover:bg-slate-800/80 hover:border-slate-700'"
                                    >
                                        <!-- Square Checkbox Huruf -->
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 transition-colors"
                                            :class="isOpsiKompleksSelected(opsiItem.label)
                                                ? 'bg-brand-600 text-white'
                                                : 'bg-slate-800 text-slate-300 group-hover:bg-slate-700'"
                                            x-text="opsiItem.label"
                                        ></div>

                                        <div class="flex-1 pt-1 text-sm sm:text-base leading-snug break-words soal-content" x-html="opsiItem.content"></div>

                                        <div class="pt-1.5 shrink-0">
                                            <div
                                                class="w-5 h-5 rounded-md border flex items-center justify-center transition"
                                                :class="isOpsiKompleksSelected(opsiItem.label) ? 'border-brand-500 bg-brand-600 text-white' : 'border-slate-700 bg-slate-800'"
                                            >
                                                <svg x-show="isOpsiKompleksSelected(opsiItem.label)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- ----------------------------------------------------- -->
                    <!-- TIPE 3: MENJODOHKAN (MATCHING PAIR INTERFACE)          -->
                    <!-- ----------------------------------------------------- -->
                    <template x-if="currentSoal && (currentSoal.jenis_soal == 3 || currentSoal.jenis == 3)">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Pasangkan Pernyataan Kiri dengan Pilihan Kanan:</span>
                                <span class="text-xs text-brand-400 font-mono" x-text="Object.keys(currentJawaban || {}).length + ' Pasangan Dipilih'"></span>
                            </div>
                            
                            <div class="space-y-3">
                                <template x-for="(baris, bIndex) in getMenjodohkanRows()" :key="baris.id">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 rounded-xl bg-slate-950/70 border border-slate-800 transition hover:border-slate-700">
                                        <div class="flex-1 text-sm sm:text-base text-slate-200 font-medium leading-snug">
                                            <span class="inline-block px-2 py-0.5 mr-2 rounded bg-slate-800 text-brand-400 text-xs font-bold" x-text="'#' + (bIndex + 1)"></span>
                                            <span x-html="baris.premis"></span>
                                        </div>
                                        
                                        <div class="w-full sm:w-72 shrink-0">
                                            <select
                                                @change="setMenjodohkanValue(baris.id, $event.target.value)"
                                                class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-brand-500 transition shadow-inner"
                                            >
                                                <option value="">-- Pilih Pasangan --</option>
                                                <template x-for="targetOpsi in getMenjodohkanTargets()" :key="targetOpsi.key">
                                                    <option
                                                        :value="targetOpsi.key"
                                                        :selected="getMenjodohkanSelected(baris.id) === targetOpsi.key"
                                                        x-text="targetOpsi.label"
                                                    ></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- ----------------------------------------------------- -->
                    <!-- TIPE 4: ISIAN SINGKAT                                 -->
                    <!-- ----------------------------------------------------- -->
                    <template x-if="currentSoal && (currentSoal.jenis_soal == 4 || currentSoal.jenis == 4)">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-3 shadow-lg">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Tuliskan Jawaban Singkat:
                            </label>
                            <input
                                type="text"
                                :value="currentJawaban || ''"
                                @input="updateIsianSingkatDebounced($event.target.value)"
                                placeholder="Ketikkan jawaban Anda di sini..."
                                class="w-full px-4 py-3.5 bg-slate-950 border border-slate-700 focus:border-brand-500 rounded-xl text-white placeholder-slate-500 text-sm sm:text-base focus:outline-none transition shadow-inner font-medium"
                            >
                            <span class="text-[11px] text-slate-500 block">Jawaban akan otomatis disimpan ke server saat Anda berhenti mengetik.</span>
                        </div>
                    </template>

                    <!-- ----------------------------------------------------- -->
                    <!-- TIPE 5: ESAI / URAIAN (DENGAN AUTO-HEIGHT & PREVIEW)   -->
                    <!-- ----------------------------------------------------- -->
                    <template x-if="currentSoal && (currentSoal.jenis_soal == 5 || currentSoal.jenis == 5)">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-lg">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Lembar Jawaban Esai / Uraian:
                                </label>

                                <!-- Tab Switcher: Mode Tulis vs Mode Pratinjau -->
                                <div class="flex items-center bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs">
                                    <button
                                        type="button"
                                        @click="activeEsaiTab = 'write'"
                                        class="px-3 py-1 rounded-lg font-medium transition"
                                        :class="activeEsaiTab === 'write' ? 'bg-brand-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                                    >
                                        Tulis Jawaban
                                    </button>
                                    <button
                                        type="button"
                                        @click="activeEsaiTab = 'preview'"
                                        class="px-3 py-1 rounded-lg font-medium transition"
                                        :class="activeEsaiTab === 'preview' ? 'bg-brand-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                                    >
                                        Pratinjau Format
                                    </button>
                                </div>
                            </div>

                            <!-- Editor Textarea dengan Auto-Height -->
                            <div x-show="activeEsaiTab === 'write'">
                                <textarea
                                    x-ref="esaiTextarea"
                                    rows="6"
                                    :value="currentJawaban || ''"
                                    @input="autoGrowTextarea($event.target); updateEsaiDebounced($event.target.value)"
                                    placeholder="Tuliskan uraian jawaban Anda secara lengkap di sini..."
                                    class="w-full p-4 bg-slate-950 border border-slate-700 focus:border-brand-500 rounded-xl text-white placeholder-slate-500 text-sm sm:text-base focus:outline-none transition leading-relaxed shadow-inner resize-y min-h-[160px]"
                                ></textarea>
                            </div>

                            <!-- Tab Pratinjau Format -->
                            <div x-show="activeEsaiTab === 'preview'" class="p-4 bg-slate-950 rounded-xl border border-slate-800 min-h-[160px] text-slate-200 text-sm sm:text-base leading-relaxed whitespace-pre-wrap break-words">
                                <template x-if="currentJawaban && currentJawaban.trim().length > 0">
                                    <div x-text="currentJawaban"></div>
                                </template>
                                <template x-if="!currentJawaban || currentJawaban.trim().length === 0">
                                    <span class="text-slate-500 italic">Belum ada teks jawaban yang dituliskan.</span>
                                </template>
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                                <span>Gunakan penjelasan yang runtut dan sistematis.</span>
                                <span class="font-mono">
                                    <span x-text="(currentJawaban || '').length">0</span> karakter
                                </span>
                            </div>
                        </div>
                    </template>

                </div>
            </div>

            <!-- ============================================================= -->
            <!-- ACTION BAR BAWAH: PREV, RAGU-RAGU, NEXT, FINISH               -->
            <!-- ============================================================= -->
            <footer x-show="!isLoading && !errorMessage" class="bg-slate-900/95 border-t border-slate-800 px-6 py-3.5 flex items-center justify-between gap-3 shrink-0 z-10">
                <!-- Tombol Sebelumnya -->
                <button
                    type="button"
                    @click="prevSoal()"
                    :disabled="currentIndex === 0"
                    class="px-4 sm:px-5 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white font-semibold text-xs sm:text-sm disabled:opacity-40 disabled:pointer-events-none transition flex items-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </button>

                <!-- Tombol Ragu-Ragu (Khas CBT Garuda) -->
                <button
                    type="button"
                    @click="toggleRagu()"
                    class="px-4 sm:px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-sm"
                    :class="isRagu
                        ? 'bg-amber-500 text-slate-950 ring-2 ring-amber-400 shadow-amber-500/30'
                        : 'bg-slate-800 hover:bg-slate-750 text-amber-400 border border-amber-500/40 hover:border-amber-400'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="isRagu ? 'Ragu-Ragu (Aktif)' : 'Ragu-Ragu'">Ragu-Ragu</span>
                </button>

                <!-- Tombol Berikutnya / Selesai Ujian -->
                <template x-if="currentIndex < soalList.length - 1">
                    <button
                        type="button"
                        @click="nextSoal()"
                        class="px-5 sm:px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-brand-600/20"
                    >
                        <span>Berikutnya</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </template>

                <template x-if="currentIndex === soalList.length - 1">
                    <button
                        type="button"
                        @click="openConfirmFinishModal()"
                        class="px-5 sm:px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-emerald-600/30"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Selesai Ujian</span>
                    </button>
                </template>
            </footer>
        </main>

        <!-- ============================================================= -->
        <!-- B. SIDEBAR DAFTAR NOMOR SOAL (DESKTOP & DRAWER TABLET)        -->
        <!-- ============================================================= -->
        <aside
            class="lg:w-80 w-72 bg-slate-900 border-l border-slate-800 flex flex-col shrink-0 z-20 fixed lg:relative inset-y-0 right-0 transform transition-transform duration-300 ease-in-out shadow-2xl"
            :class="showDrawerMobile ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
        >
            <!-- Header Sidebar -->
            <div class="h-16 px-5 border-b border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <h3 class="font-bold text-sm text-white">Daftar Nomor Soal</h3>
                </div>

                <button
                    type="button"
                    @click="showDrawerMobile = false"
                    class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Ringkasan Status Jawaban -->
            <div class="p-4 bg-slate-950/60 border-b border-slate-800 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800">
                    <span class="block text-emerald-400 font-extrabold text-base" x-text="answerStats.answered">0</span>
                    <span class="text-[10px] text-slate-400">Dijawab</span>
                </div>
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800">
                    <span class="block text-amber-400 font-extrabold text-base" x-text="answerStats.ragu">0</span>
                    <span class="text-[10px] text-slate-400">Ragu</span>
                </div>
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800">
                    <span class="block text-slate-400 font-extrabold text-base" x-text="answerStats.unanswered">0</span>
                    <span class="text-[10px] text-slate-400">Kosong</span>
                </div>
            </div>

            <!-- Grid Nomor Soal (Zero-Latency Navigation Pointer Shift) -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-5 gap-2.5">
                    <template x-for="(soalItem, sIdx) in soalList" :key="soalItem.id">
                        <button
                            type="button"
                            @click="goToSoal(sIdx); showDrawerMobile = false;"
                            class="h-11 rounded-xl font-bold text-xs sm:text-sm flex flex-col items-center justify-center transition-all relative"
                            :class="getSoalButtonClass(sIdx, soalItem.id)"
                        >
                            <span x-text="sIdx + 1">1</span>
                            <!-- Label Jawaban Singkat jika PG -->
                            <span
                                x-show="getOpsiLabelPreview(soalItem.id)"
                                class="text-[9px] font-extrabold -mt-0.5 opacity-90"
                                x-text="getOpsiLabelPreview(soalItem.id)"
                            ></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Petunjuk Legend Warna -->
            <div class="px-4 py-2 border-t border-slate-800 bg-slate-950/40 grid grid-cols-2 gap-1.5 text-[11px] text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-emerald-600 inline-block"></span>
                    <span>Sudah Dijawab</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-amber-500 inline-block"></span>
                    <span>Ragu-Ragu</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-slate-800 border border-slate-700 inline-block"></span>
                    <span>Belum Dijawab</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-blue-600/40 border-2 border-blue-400 inline-block"></span>
                    <span>Soal Aktif</span>
                </div>
            </div>

            <!-- Tombol Selesai Ujian di Sidebar Bawah -->
            <div class="p-4 border-t border-slate-800 bg-slate-900 shrink-0">
                <button
                    type="button"
                    @click="openConfirmFinishModal()"
                    class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Kumpulkan Lembar Ujian</span>
                </button>
            </div>
        </aside>

        <!-- Backdrop Mobile Drawer -->
        <div
            x-show="showDrawerMobile"
            @click="showDrawerMobile = false"
            class="lg:hidden fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-10 transition-opacity"
        ></div>
    </div>

    <!-- ================================================================= -->
    <!-- 3. MODAL PERINGATAN PELANGGARAN KECURANGAN (FULL ANTI-CHEAT TRAP)  -->
    <!-- ================================================================= -->
    <div
        x-show="showViolationModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-red-950/95 backdrop-blur-md"
        style="display: none;"
    >
        <div class="bg-slate-900 border-2 border-rose-600 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl text-center relative overflow-hidden">
            <!-- Red Glow Background -->
            <div class="absolute -top-24 -left-24 w-48 h-48 bg-rose-600/30 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-rose-600/30 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Ikon Peringatan Keras -->
            <div class="w-20 h-20 rounded-2xl bg-rose-500/20 text-rose-500 border border-rose-500/30 flex items-center justify-center mx-auto mb-5 animate-bounce shadow-lg shadow-rose-900/40">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h3 class="text-2xl font-black text-white mb-2 tracking-wide uppercase">
                Peringatan Pelanggaran!
            </h3>
            
            <p class="text-sm text-rose-300 font-medium mb-4" x-text="violationModalMsg">
                Terdeteksi aktivitas mencurigakan / keluar dari jendela ujian!
            </p>

            <!-- Box Counter Pelanggaran: Peringatan ke-X dari 3! -->
            <div class="bg-slate-950/80 border border-rose-500/40 rounded-2xl p-4 mb-6 shadow-inner">
                <span class="text-xs text-slate-400 block mb-1">Status Pelanggaran Sisi Klien:</span>
                <div class="text-xl sm:text-2xl font-black text-rose-500">
                    Peringatan ke-<span x-text="violationCount">1</span> dari <span x-text="maxViolations">3</span>!
                </div>
                <p class="text-xs text-amber-300 font-medium mt-2">
                    Sisa toleransi: <span class="font-bold underline" x-text="Math.max(0, maxViolations - violationCount)">2</span> kali lagi sebelum lembar ujian Anda otomatis dibatalkan dan akun dikunci!
                </p>
            </div>

            <!-- Tombol Tidak Bisa Ditutup Kecuali Menekan "Saya Mengerti" -->
            <button
                type="button"
                @click="dismissViolationModal()"
                class="w-full py-3.5 px-6 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm tracking-wide shadow-lg shadow-rose-600/40 transition"
            >
                Saya Mengerti & Kembali ke Ujian
            </button>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 4. MODAL SCREEN TERMINASI PERMANEN (AUTO-LOCK KARENA PELANGGARAN) -->
    <!-- ================================================================= -->
    <div
        x-show="isTerminated"
        class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/95 backdrop-blur-lg"
        style="display: none;"
    >
        <div class="max-w-md w-full bg-slate-900 border-2 border-red-700 rounded-3xl p-8 text-center shadow-2xl">
            <div class="w-20 h-20 bg-red-600/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 border border-red-500">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-white mb-2 uppercase tracking-wide">Ujian Anda Dihentikan karena Pelanggaran!</h2>
            <p class="text-sm text-red-300 font-medium mb-6 leading-relaxed" x-text="terminationMessage">
                Sesi ujian Anda telah dibatalkan otomatis oleh sistem karena akumulasi pelanggaran keluar dari jendela ujian.
            </p>
            <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-xs text-slate-400 mb-6">
                Catatan pelanggaran dan riwayat waktu telah dikirim ke server proktor. Mengalihkan ke halaman keluar dalam <span class="font-bold text-amber-400" x-text="terminationCountdown">5</span> detik...
            </div>
            <a
                href="{{ route('login') }}"
                class="w-full block py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm transition"
            >
                Keluar Sekarang
            </a>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 5. MODAL KONFIRMASI SELESAI UJIAN                                 -->
    <!-- ================================================================= -->
    <div
        x-show="showConfirmFinishModal"
        x-transition
        class="fixed inset-0 z-40 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm"
        style="display: none;"
    >
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="text-xl font-bold text-white mb-2">Konfirmasi Selesai Ujian</h3>
            <p class="text-xs text-slate-400 mb-5">Pastikan Anda telah memeriksa kembali seluruh jawaban sebelum mengirim lembar ujian ke server.</p>

            <!-- Ringkasan Statistik Soal -->
            <div class="grid grid-cols-3 gap-2.5 mb-5 text-center">
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                    <span class="text-xl font-extrabold text-emerald-400 block" x-text="answerStats.answered">0</span>
                    <span class="text-[11px] text-slate-400 font-medium">Sudah Dijawab</span>
                </div>
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                    <span class="text-xl font-extrabold text-amber-400 block" x-text="answerStats.ragu">0</span>
                    <span class="text-[11px] text-slate-400 font-medium">Masih Ragu</span>
                </div>
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                    <span class="text-xl font-extrabold text-slate-400 block" x-text="answerStats.unanswered">0</span>
                    <span class="text-[11px] text-slate-400 font-medium">Belum Dijawab</span>
                </div>
            </div>

            <!-- Warning jika masih ada ragu atau belum dijawab -->
            <template x-if="answerStats.ragu > 0 || answerStats.unanswered > 0">
                <div class="p-3.5 mb-6 rounded-xl bg-amber-950/60 border border-amber-500/40 text-amber-300 text-xs text-left leading-relaxed flex items-start gap-2.5">
                    <svg class="w-5 h-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>
                        Peringatan: Masih terdapat <strong><span x-text="answerStats.unanswered">0</span> butir soal belum dijawab</strong> dan <strong><span x-text="answerStats.ragu">0</span> butir soal bertanda ragu-ragu</strong>.
                    </span>
                </div>
            </template>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="showConfirmFinishModal = false"
                    :disabled="isSubmitting"
                    class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs sm:text-sm transition"
                >
                    Periksa Kembali
                </button>
                <button
                    type="button"
                    @click="submitFinalExam(false)"
                    :disabled="isSubmitting"
                    class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2"
                >
                    <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isSubmitting ? 'Mengirimkan...' : 'Ya, Selesai Sekarang'">Ya, Selesai Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 6. MODAL INPUT TOKEN UJIAN (JIKA PAKAI TOKEN)                     -->
    <!-- ================================================================= -->
    <div
        x-show="showTokenModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md"
        style="display: none;"
    >
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-center">
            <div class="w-16 h-16 rounded-2xl bg-brand-500/20 text-brand-400 border border-brand-500/30 flex items-center justify-center mx-auto mb-4 text-2xl">
                🔑
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Masukkan Token Ujian</h3>
            <p class="text-xs text-slate-400 mb-6">Ujian ini memerlukan token autentikasi aktif. Silakan mintalah token 6 digit kepada pengawas/proktor ruangan Anda.</p>
            
            <input
                type="text"
                x-model="tokenInput"
                maxlength="8"
                placeholder="TOKEN"
                class="w-full text-center text-2xl font-mono font-black uppercase tracking-[0.5em] py-3.5 bg-slate-950 border border-brand-500/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-brand-500 mb-6 shadow-inner"
            >

            <button
                type="button"
                @click="submitTokenAndStart()"
                class="w-full py-3.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm tracking-wide shadow-lg shadow-brand-600/30 transition"
            >
                Mulai Kerjakan Ujian
            </button>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 7. MODAL SUKSES PENGUMPULAN UJIAN                                 -->
    <!-- ================================================================= -->
    <div
        x-show="examFinished"
        class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/95 backdrop-blur-lg"
        style="display: none;"
    >
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-2xl">
            <div class="w-20 h-20 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-5 border border-emerald-500/30 shadow-lg shadow-emerald-500/20">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-black text-white mb-2">Ujian Berhasil Dikumpulkan!</h2>
            <p class="text-sm text-slate-400 mb-6">
                Seluruh lembar jawaban Anda telah tersimpan secara aman di database server.
            </p>

            <template x-if="finishResult && finishResult.tampil_hasil">
                <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 mb-6 shadow-inner">
                    <span class="text-xs text-slate-400 block mb-1">Skor Akhir Anda:</span>
                    <span class="text-4xl font-black text-brand-400" x-text="finishResult.total_nilai || '0'">0</span>
                </div>
            </template>

            <a
                href="{{ route('exam.index') }}"
                class="w-full block py-3.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm tracking-wide shadow-lg shadow-brand-600/30 transition"
            >
                Kembali ke Beranda Ujian
            </a>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 8. LIGHTBOX IMAGE MODAL (ZOOM GAMBAR SOAL)                        -->
    <!-- ================================================================= -->
    <div
        x-show="lightboxSrc"
        @click="lightboxSrc = null"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md cursor-zoom-out"
        style="display: none;"
    >
        <img :src="lightboxSrc" alt="Preview Gambar" class="max-w-full max-h-full rounded-xl shadow-2xl object-contain">
    </div>

    <!-- ================================================================= -->
    <!-- 9. CORE ALPINE.JS EXAM ENGINE JAVASCRIPT (ZERO-LATENCY + ANTI-CHEAT) -->
    <!-- ================================================================= -->
    <script>
        function renderMathInPage() {
            if (window.renderMathInElement) {
                const el = document.getElementById('exam-content-area') || document.body;
                window.renderMathInElement(el, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '$', right: '$', display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true }
                    ],
                    ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option'],
                    throwOnError: false
                });
            }
        }
        window.renderMathInPage = renderMathInPage;

        function cbtExam(config) {
            return {
                jadwalId: config.jadwalId,
                siswaId: config.siswaId,
                apiBase: config.apiBase,
                token: config.token || '',
                pakaiToken: Boolean(config.pakaiToken),
                csrfToken: config.csrfToken,
                authToken: config.authToken || '',
                deviceToken: config.deviceToken || '',
                sisaDetik: config.durasiDetik || 5400,

                // State Paket Ujian & Pengerjaan
                isLoading: true,
                errorMessage: '',
                soalList: [],
                currentIndex: 0,
                jawabanUser: {}, // { [soalId]: { jawaban: ..., ragu: bool, updated_at: ... } }

                // Tampilan & UI State
                fontSize: 'normal',
                saveStatus: 'synced', // 'saving' | 'synced' | 'offline'
                showDrawerMobile: false,
                isFullscreen: false,
                lightboxSrc: null,
                activeEsaiTab: 'write', // 'write' | 'preview'

                // Timer & Heartbeat Intervals
                timerRunning: false,
                timerInterval: null,
                heartbeatInterval: null,

                // Token Modal State
                showTokenModal: false,
                tokenInput: config.token || '',

                // Full Anti-Cheat Trap State
                violationCount: 0,
                maxViolations: 3,
                showViolationModal: false,
                violationModalMsg: '',
                isTerminated: false,
                terminationMessage: '',
                terminationCountdown: 5,
                terminationTimer: null,

                // Modal Konfirmasi Selesai Ujian
                showConfirmFinishModal: false,
                isSubmitting: false,
                examFinished: false,
                finishResult: null,

                // Debounce Timers untuk Autosave Isian & Esai
                debounceTimers: {},

                // Kunci Cadangan Offline (Local Storage)
                get backupStorageKey() {
                    return `cbt_local_backup_${this.jadwalId}_${this.siswaId}`;
                },

                // -------------------------------------------------------------
                // 1. INISIALISASI MESIN UJIAN (initExam)
                // -------------------------------------------------------------
                async initExam() {
                    // Pulihkan cadangan offline dari localStorage terlebih dahulu
                    this.restoreLocalBackup();

                    // Minta paket ujian dari endpoint server
                    await this.fetchExamPackage();

                    // Pasang perangkap proteksi sisi klien (Anti-Cheat)
                    this.setupAntiCheatTraps();

                    // Pasang pemantau konektivitas jaringan (Online / Offline)
                    this.setupNetworkStatusListener();

                    // Watch perpindahan butir soal untuk merender formula LaTeX KaTeX seketika (Zero-Latency)
                    this.$watch('currentIndex', () => {
                        this.renderMath();
                    });
                },

                // Alias untuk kompatibilitas
                initEngine() {
                    return this.initExam();
                },

                // Panggil endpoint POST /api/cbt/start/{jadwalId}
                async fetchExamPackage() {
                    this.isLoading = true;
                    this.errorMessage = '';

                    try {
                        const url = `${this.apiBase}/start/${this.jadwalId}`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Exam-Token': this.token || '',
                                'Authorization': this.authToken ? `Bearer ${this.authToken}` : '',
                                'X-Auth-Token': this.authToken || '',
                                'X-Device-Token': this.deviceToken || '',
                            },
                            body: JSON.stringify({ token: this.token })
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            if (response.status === 422 || (data.message && data.message.toLowerCase().includes('token'))) {
                                this.showTokenModal = true;
                                this.isLoading = false;
                                return;
                            }

                            if (data.terminated || response.status === 403) {
                                this.triggerPermanentTermination(data.message || 'Ujian dihentikan karena pelanggaran!');
                                this.isLoading = false;
                                return;
                            }

                            this.errorMessage = data.message || 'Gagal memuat paket ujian.';
                            this.isLoading = false;
                            return;
                        }

                        // Simpan paket soal ke memori browser
                        this.soalList = data.soal || [];
                        this.currentIndex = 0;

                        if (typeof data.sisa_detik === 'number' && data.sisa_detik > 0) {
                            this.sisaDetik = data.sisa_detik;
                        }

                        if (typeof data.violations === 'number') {
                            this.violationCount = data.violations;
                        }
                        if (typeof data.max_violations === 'number') {
                            this.maxViolations = data.max_violations;
                        }

                        // Pulihkan jawaban yang tersimpan di server
                        if (data.jawaban_tersimpan && typeof data.jawaban_tersimpan === 'object') {
                            for (const [sId, ansObj] of Object.entries(data.jawaban_tersimpan)) {
                                this.jawabanUser[sId] = {
                                    jawaban: ansObj.jawaban ?? null,
                                    ragu: Boolean(ansObj.ragu),
                                    updated_at: ansObj.updated_at ?? Math.floor(Date.now() / 1000)
                                };
                            }
                        }

                        // Perbarui cadangan lokal dengan data resmi dari server
                        this.saveLocalBackup();

                        // Jalankan countdown timer dan heartbeat 30 detik
                        this.startCountdownTimer();
                        this.startHeartbeatSync();

                        this.showTokenModal = false;
                        this.isLoading = false;
                        this.renderMath();

                    } catch (err) {
                        console.error('Fetch Exam Error:', err);
                        this.errorMessage = 'Terjadi gangguan jaringan saat memuat lembar ujian. Periksa koneksi internet Anda.';
                        this.isLoading = false;
                    }
                },

                submitTokenAndStart() {
                    if (!this.tokenInput || this.tokenInput.trim().length < 3) {
                        alert('Silakan masukkan token ujian dengan benar.');
                        return;
                    }
                    this.token = this.tokenInput.trim().toUpperCase();
                    this.fetchExamPackage();
                },

                // -------------------------------------------------------------
                // 2. GETTER PROPERTI SOAL AKTIF & FORMAT NORMALISASI OPSI
                // -------------------------------------------------------------
                get currentSoal() {
                    if (!this.soalList || this.soalList.length === 0) return null;
                    return this.soalList[this.currentIndex] || null;
                },

                get currentJawaban() {
                    if (!this.currentSoal) return null;
                    return this.jawabanUser[this.currentSoal.id]?.jawaban ?? null;
                },

                get isRagu() {
                    if (!this.currentSoal) return false;
                    return Boolean(this.jawabanUser[this.currentSoal.id]?.ragu);
                },

                // Normalisasi opsi PG / PGK agar selalu berformat [{ label: 'A', content: '...' }, ...]
                getFormattedOptions(soal) {
                    if (!soal || !soal.opsi) return [];
                    const result = [];

                    if (Array.isArray(soal.opsi)) {
                        soal.opsi.forEach(item => {
                            if (typeof item === 'object' && item !== null) {
                                result.push({
                                    label: item.label || '',
                                    content: item.content || item.teks || item.text || ''
                                });
                            } else {
                                result.push({ label: '', content: String(item) });
                            }
                        });
                    } else if (typeof soal.opsi === 'object') {
                        Object.keys(soal.opsi).forEach(key => {
                            const val = soal.opsi[key];
                            result.push({
                                label: key,
                                content: (typeof val === 'object' && val !== null) ? (val.content || val.teks || JSON.stringify(val)) : String(val || '')
                            });
                        });
                    }

                    return result;
                },

                // -------------------------------------------------------------
                // 3. NAVIGASI ZERO-LATENCY (0 ms Pointer Shift) & FONT RESIZER
                // -------------------------------------------------------------
                goToSoal(index) {
                    if (index >= 0 && index < this.soalList.length) {
                        this.currentIndex = index;
                        this.renderMath();
                    }
                },

                nextSoal() {
                    if (this.currentIndex < this.soalList.length - 1) {
                        this.currentIndex++;
                        this.renderMath();
                    }
                },

                prevSoal() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.renderMath();
                    }
                },

                // Perender Formula Matematika & Eksakta KaTeX (100% Offline)
                renderMath() {
                    this.$nextTick(() => {
                        renderMathInPage();
                    });
                },

                setFontSize(size) {
                    this.fontSize = size;
                },

                openLightbox(src) {
                    this.lightboxSrc = src;
                },

                // -------------------------------------------------------------
                // 4. PENANGANAN INPUT 5 TIPE SOAL
                // -------------------------------------------------------------
                // Tipe 1: PG Biasa
                selectOpsi(opsiLabel) {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    const prevRagu = this.isRagu;

                    this.jawabanUser[sId] = {
                        jawaban: opsiLabel,
                        ragu: prevRagu,
                        updated_at: Math.floor(Date.now() / 1000)
                    };

                    this.dispatchAutosave(sId);
                },

                clearJawaban() {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    const prevRagu = this.isRagu;

                    this.jawabanUser[sId] = {
                        jawaban: null,
                        ragu: prevRagu,
                        updated_at: Math.floor(Date.now() / 1000)
                    };

                    this.dispatchAutosave(sId);
                },

                // Tipe 2: PG Kompleks (Checklist Multi Opsi)
                isOpsiKompleksSelected(label) {
                    const ans = this.currentJawaban;
                    if (!Array.isArray(ans)) return false;
                    return ans.includes(label);
                },

                toggleOpsiKompleks(label) {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    let ans = Array.isArray(this.currentJawaban) ? [...this.currentJawaban] : [];

                    if (ans.includes(label)) {
                        ans = ans.filter(x => x !== label);
                    } else {
                        ans.push(label);
                        ans.sort();
                    }

                    this.jawabanUser[sId] = {
                        jawaban: ans,
                        ragu: this.isRagu,
                        updated_at: Math.floor(Date.now() / 1000)
                    };

                    this.dispatchAutosave(sId);
                },

                // Tipe 3: Menjodohkan
                getMenjodohkanRows() {
                    if (!this.currentSoal) return [];
                    const rows = [];
                    const opsi = this.currentSoal.opsi;

                    if (opsi && typeof opsi === 'object') {
                        if (opsi.left && typeof opsi.left === 'object') {
                            Object.keys(opsi.left).forEach(k => {
                                rows.push({ id: k, premis: opsi.left[k] });
                            });
                            return rows;
                        }
                    }

                    // Fallback opsi terformat
                    const formatted = this.getFormattedOptions(this.currentSoal);
                    formatted.forEach((o, i) => {
                        rows.push({
                            id: (i + 1).toString(),
                            premis: o.content || `Pernyataan ${i + 1}`
                        });
                    });
                    return rows;
                },

                getMenjodohkanTargets() {
                    if (!this.currentSoal) return [];
                    const targets = [];
                    const opsi = this.currentSoal.opsi;

                    if (opsi && typeof opsi === 'object') {
                        if (opsi.right && typeof opsi.right === 'object') {
                            Object.keys(opsi.right).forEach(k => {
                                targets.push({ key: k, label: `${k}. ${opsi.right[k]}` });
                            });
                            return targets;
                        }
                    }

                    const formatted = this.getFormattedOptions(this.currentSoal);
                    formatted.forEach((o, i) => {
                        const lbl = o.label || String.fromCharCode(65 + i);
                        const cleanText = o.content ? o.content.replace(/<[^>]*>?/gm, '').substring(0, 40) : '';
                        targets.push({
                            key: lbl,
                            label: `${lbl}. ${cleanText}`
                        });
                    });
                    return targets;
                },

                getMenjodohkanSelected(rowId) {
                    const ans = this.currentJawaban;
                    if (!ans || typeof ans !== 'object') return '';
                    return ans[rowId] || '';
                },

                setMenjodohkanValue(rowId, targetKey) {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    let ans = (this.currentJawaban && typeof this.currentJawaban === 'object' && !Array.isArray(this.currentJawaban))
                        ? { ...this.currentJawaban }
                        : {};

                    if (targetKey) {
                        ans[rowId] = targetKey;
                    } else {
                        delete ans[rowId];
                    }

                    this.jawabanUser[sId] = {
                        jawaban: ans,
                        ragu: this.isRagu,
                        updated_at: Math.floor(Date.now() / 1000)
                    };

                    this.dispatchAutosave(sId);
                },

                // Tipe 4: Isian Singkat
                updateIsianSingkatDebounced(val) {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    this.jawabanUser[sId] = {
                        jawaban: val,
                        ragu: this.isRagu,
                        updated_at: Math.floor(Date.now() / 1000)
                    };
                    this.saveLocalBackup();

                    clearTimeout(this.debounceTimers[sId]);
                    this.debounceTimers[sId] = setTimeout(() => {
                        this.dispatchAutosave(sId);
                    }, 400);
                },

                // Tipe 5: Esai dengan Auto-Height & Preview
                autoGrowTextarea(textarea) {
                    if (!textarea) return;
                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight + 4) + 'px';
                },

                updateEsaiDebounced(val) {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    this.jawabanUser[sId] = {
                        jawaban: val,
                        ragu: this.isRagu,
                        updated_at: Math.floor(Date.now() / 1000)
                    };
                    this.saveLocalBackup();

                    clearTimeout(this.debounceTimers[sId]);
                    this.debounceTimers[sId] = setTimeout(() => {
                        this.dispatchAutosave(sId);
                    }, 600);
                },

                // Tombol Ragu-Ragu
                toggleRagu() {
                    if (!this.currentSoal) return;
                    const sId = this.currentSoal.id;
                    const newRagu = !this.isRagu;

                    if (!this.jawabanUser[sId]) {
                        this.jawabanUser[sId] = { jawaban: null, ragu: newRagu, updated_at: Math.floor(Date.now() / 1000) };
                    } else {
                        this.jawabanUser[sId].ragu = newRagu;
                        this.jawabanUser[sId].updated_at = Math.floor(Date.now() / 1000);
                    }

                    this.dispatchAutosave(sId);
                },

                // -------------------------------------------------------------
                // 5. AUTOSAVE ASINKRON NON-BLOCKING & OFFLINE LOCALSTORAGE
                // -------------------------------------------------------------
                async dispatchAutosave(soalId) {
                    this.saveStatus = 'saving';
                    this.saveLocalBackup();

                    const item = this.jawabanUser[soalId] || { jawaban: null, ragu: false };

                    try {
                        const url = `${this.apiBase}/autosave/${this.jadwalId}`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Authorization': this.authToken ? `Bearer ${this.authToken}` : '',
                                'X-Auth-Token': this.authToken || '',
                                'X-Device-Token': this.deviceToken || '',
                            },
                            body: JSON.stringify({
                                soal_id: soalId,
                                jawaban: item.jawaban,
                                ragu: item.ragu
                            })
                        });

                        const resData = await response.json();

                        if (response.ok && resData.success) {
                            this.saveStatus = 'synced';
                        } else {
                            if (resData.terminated || response.status === 403) {
                                this.triggerPermanentTermination(resData.message || 'Ujian telah dihentikan karena pelanggaran.');
                            }
                            this.saveStatus = 'offline';
                        }
                    } catch (e) {
                        // Jika koneksi Wi-Fi/LAN terputus, tetap simpan di memori lokal
                        this.saveStatus = 'offline';
                    }
                },

                saveLocalBackup() {
                    try {
                        localStorage.setItem(this.backupStorageKey, JSON.stringify(this.jawabanUser));
                    } catch (e) {}
                },

                restoreLocalBackup() {
                    try {
                        const raw = localStorage.getItem(this.backupStorageKey);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            if (parsed && typeof parsed === 'object') {
                                this.jawabanUser = parsed;
                            }
                        }
                    } catch (e) {}
                },

                setupNetworkStatusListener() {
                    window.addEventListener('online', () => {
                        this.saveStatus = 'synced';
                        // Re-sinkronkan jawaban aktif ke server saat koneksi pulih
                        if (this.currentSoal) {
                            this.dispatchAutosave(this.currentSoal.id);
                        }
                    });
                    window.addEventListener('offline', () => {
                        this.saveStatus = 'offline';
                    });
                },

                // -------------------------------------------------------------
                // 6. STATISTIK JAWABAN & INDIKATOR WARNA GRID PALETTE
                // -------------------------------------------------------------
                get answerStats() {
                    let answered = 0;
                    let ragu = 0;
                    let unanswered = 0;

                    this.soalList.forEach(s => {
                        const entry = this.jawabanUser[s.id];
                        const hasAns = entry && entry.jawaban !== null && entry.jawaban !== '' && (!Array.isArray(entry.jawaban) || entry.jawaban.length > 0) && (typeof entry.jawaban !== 'object' || Object.keys(entry.jawaban).length > 0);
                        const isR = entry && Boolean(entry.ragu);

                        if (isR) {
                            ragu++;
                        } else if (hasAns) {
                            answered++;
                        } else {
                            unanswered++;
                        }
                    });

                    return { answered, ragu, unanswered };
                },

                // Indikator Warna Nomor Soal:
                // - Biru / Border Tebal: Soal Aktif
                // - Kuning: Ragu-ragu
                // - Hijau: Sudah dijawab
                // - Abu-abu: Belum dijawab
                getSoalButtonClass(index, soalId) {
                    const isActive = (this.currentIndex === index);
                    const entry = this.jawabanUser[soalId];
                    const hasAns = entry && entry.jawaban !== null && entry.jawaban !== '' && (!Array.isArray(entry.jawaban) || entry.jawaban.length > 0) && (typeof entry.jawaban !== 'object' || Object.keys(entry.jawaban).length > 0);
                    const isR = entry && Boolean(entry.ragu);

                    let base = 'transition shadow-sm ';

                    if (isActive) {
                        base += 'ring-2 ring-blue-400 ring-offset-2 ring-offset-slate-950 scale-105 z-10 border-2 border-blue-400 text-blue-200 ';
                    }

                    if (isR) {
                        return base + 'bg-amber-500 text-slate-950 font-bold shadow-amber-500/20';
                    }
                    if (hasAns) {
                        return base + 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20';
                    }
                    return base + 'bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700';
                },

                getOpsiLabelPreview(soalId) {
                    const entry = this.jawabanUser[soalId];
                    if (!entry || !entry.jawaban) return '';
                    if (typeof entry.jawaban === 'string' && entry.jawaban.length <= 2) {
                        return entry.jawaban;
                    }
                    if (Array.isArray(entry.jawaban)) {
                        return entry.jawaban.join('');
                    }
                    return '';
                },

                getBadgeTipeSoal(jenis) {
                    switch (parseInt(jenis)) {
                        case 1:
                            return { label: 'Pilihan Ganda', class: 'bg-brand-500/20 text-brand-300 border-brand-500/30' };
                        case 2:
                            return { label: 'PG Kompleks', class: 'bg-violet-500/20 text-violet-300 border-violet-500/30' };
                        case 3:
                            return { label: 'Menjodohkan', class: 'bg-sky-500/20 text-sky-300 border-sky-500/30' };
                        case 4:
                            return { label: 'Isian Singkat', class: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' };
                        case 5:
                            return { label: 'Uraian / Esai', class: 'bg-amber-500/20 text-amber-300 border-amber-500/30' };
                        default:
                            return { label: 'Pilihan Ganda', class: 'bg-brand-500/20 text-brand-300 border-brand-500/30' };
                    }
                },

                // -------------------------------------------------------------
                // 7. SINKRONISASI TIMER & HEARTBEAT
                // -------------------------------------------------------------
                startCountdownTimer() {
                    if (this.timerRunning) return;
                    this.timerRunning = true;

                    this.timerInterval = setInterval(() => {
                        if (this.sisaDetik > 0) {
                            this.sisaDetik--;
                        } else {
                            this.sisaDetik = 0;
                            clearInterval(this.timerInterval);
                            this.forceFinishTimeOut();
                        }
                    }, 1000);
                },

                startHeartbeatSync() {
                    // Drift correction heartbeat setiap 30 detik
                    this.heartbeatInterval = setInterval(async () => {
                        if (this.examFinished || this.isTerminated) return;
                        try {
                            const url = `${this.apiBase}/sync-timer/${this.jadwalId}`;
                            const res = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Authorization': this.authToken ? `Bearer ${this.authToken}` : '',
                                    'X-Auth-Token': this.authToken || '',
                                    'X-Device-Token': this.deviceToken || '',
                                }
                            });
                            const data = await res.json();
                            if (res.ok && data.success) {
                                if (typeof data.sisa_detik === 'number') {
                                    // Koreksi perbedaan waktu jika selisih drift > 3 detik
                                    if (Math.abs(this.sisaDetik - data.sisa_detik) > 3) {
                                        this.sisaDetik = data.sisa_detik;
                                    }
                                }
                            } else if (data.terminated || res.status === 403) {
                                this.triggerPermanentTermination(data.message || 'Ujian dihentikan karena pelanggaran.');
                            }
                        } catch (e) {}
                    }, 30000);
                },

                formatTimer(seconds) {
                    const s = Math.max(0, parseInt(seconds) || 0);
                    const h = Math.floor(s / 3600);
                    const m = Math.floor((s % 3600) / 60);
                    const sec = s % 60;
                    return [h, m, sec].map(v => v < 10 ? '0' + v : v).join(':');
                },

                forceFinishTimeOut() {
                    alert('Waktu ujian telah habis! Sistem secara otomatis mengumpulkan lembar jawaban Anda.');
                    this.submitFinalExam(true);
                },

                // -------------------------------------------------------------
                // 8. FULL ANTI-CHEAT TRAPS & PROTECTION SISTEM
                // -------------------------------------------------------------
                setupAntiCheatTraps() {
                    // 1. Deteksi Pergantian Tab (Visibility Change)
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden && !this.examFinished && !this.isTerminated) {
                            this.recordCheatViolation('tab_switch', 'Terdeteksi berpindah tab browser / aplikasi diminimalkan!');
                        }
                    });

                    // 2. Deteksi Kehilangan Fokus Kursor / Window Blur (Split Screen, Aplikasi Lain)
                    window.addEventListener('blur', () => {
                        if (!this.examFinished && !this.isTerminated && !this.showViolationModal) {
                            this.recordCheatViolation('window_blur', 'Fokus kursor keluar dari jendela ujian (membuka aplikasi lain atau floating window)!');
                        }
                    });

                    // 3. Full-Screen Auto Enforcement & Exit Detection
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = Boolean(document.fullscreenElement);
                        if (!this.isFullscreen && !this.examFinished && !this.isTerminated) {
                            this.recordCheatViolation('fullscreen_exit', 'Anda keluar dari mode layar penuh (Full Screen Mode)!');
                        }
                    });
                },

                handleContextMenu(event) {
                    event.preventDefault();
                    return false;
                },

                handleGlobalKeydown(e) {
                    // Izinkan pengetikan normal di input text dan textarea
                    const isInputTarget = ['INPUT', 'TEXTAREA'].includes(e.target?.tagName);

                    // 1. Blokir F12 (DevTools)
                    if (e.key === 'F12') {
                        e.preventDefault();
                        this.recordCheatViolation('dev_tools', 'Membuka Developer Tools dilarang selama ujian!');
                        return false;
                    }

                    // 2. Blokir Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C (DevTools Inspect)
                    if ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i', 'j', 'c'].includes(e.key.toLowerCase())) {
                        e.preventDefault();
                        this.recordCheatViolation('dev_tools', 'Membuka Developer Tools / Inspect Element dilarang!');
                        return false;
                    }

                    // 3. Blokir Ctrl+U (View Source)
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'u') {
                        e.preventDefault();
                        return false;
                    }

                    // 4. Blokir Cetak Halaman: Ctrl+P
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
                        e.preventDefault();
                        return false;
                    }

                    // 5. Blokir Copy, Cut, Paste di luar editor jika diperlukan
                    if (!isInputTarget && (e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 'a'].includes(e.key.toLowerCase())) {
                        e.preventDefault();
                        return false;
                    }
                },

                async recordCheatViolation(type, message) {
                    if (this.examFinished || this.isTerminated) return;

                    this.violationModalMsg = message;
                    this.showViolationModal = true;

                    try {
                        const url = `${this.apiBase}/violation/${this.jadwalId}`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Authorization': this.authToken ? `Bearer ${this.authToken}` : '',
                                'X-Auth-Token': this.authToken || '',
                                'X-Device-Token': this.deviceToken || '',
                            },
                            body: JSON.stringify({ violation_type: type })
                        });

                        const data = await response.json();

                        if (data) {
                            this.violationCount = data.violations || (this.violationCount + 1);
                            this.maxViolations = data.max_violations || 3;

                            if (data.terminated || response.status === 403) {
                                this.triggerPermanentTermination(data.message || 'Ujian dihentikan permanen karena akumulasi pelanggaran.');
                            }
                        }
                    } catch (e) {
                        this.violationCount++;
                    }
                },

                dismissViolationModal() {
                    this.showViolationModal = false;
                    // Berusaha kembali ke fullscreen jika didukung
                    if (!document.fullscreenElement) {
                        this.toggleFullscreen(true);
                    }
                },

                triggerPermanentTermination(message) {
                    this.showViolationModal = false;
                    this.isTerminated = true;
                    this.terminationMessage = message;

                    clearInterval(this.timerInterval);
                    clearInterval(this.heartbeatInterval);

                    // Countdown 5 detik lalu redirect ke halaman login
                    this.terminationCountdown = 5;
                    this.terminationTimer = setInterval(() => {
                        this.terminationCountdown--;
                        if (this.terminationCountdown <= 0) {
                            clearInterval(this.terminationTimer);
                            window.location.href = '{{ route('login') }}';
                        }
                    }, 1000);
                },

                toggleFullscreen(forceEnter = false) {
                    if (forceEnter || !document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else if (document.fullscreenElement) {
                        document.exitFullscreen().catch(() => {});
                    }
                },

                // -------------------------------------------------------------
                // 9. SUBMIT FINAL EXAM (PENGUMPULAN LEMBAR JAWABAN)
                // -------------------------------------------------------------
                openConfirmFinishModal() {
                    this.showConfirmFinishModal = true;
                },

                async submitFinalExam(isForce = false) {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;

                    clearInterval(this.timerInterval);
                    clearInterval(this.heartbeatInterval);

                    try {
                        const url = `${this.apiBase}/finish/${this.jadwalId}`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Authorization': this.authToken ? `Bearer ${this.authToken}` : '',
                                'X-Auth-Token': this.authToken || '',
                                'X-Device-Token': this.deviceToken || '',
                            },
                            body: JSON.stringify({ force: isForce })
                        });

                        const data = await response.json();

                        this.showConfirmFinishModal = false;
                        this.isSubmitting = false;

                        if (response.ok && data.success) {
                            this.finishResult = data;
                            this.examFinished = true;
                            // Bersihkan backup offline saat ujian selesai
                            try { localStorage.removeItem(this.backupStorageKey); } catch (e) {}
                        } else {
                            alert(data.message || 'Gagal mengirimkan jawaban. Silakan coba kembali.');
                        }
                    } catch (err) {
                        this.isSubmitting = false;
                        alert('Koneksi terganggu saat mengirim jawaban. Jawaban Anda tetap tersimpan di memori perangkat. Silakan coba tekan tombol Selesai kembali.');
                    }
                }
            };
        }

        // Backward compatibility alias
        window.cbtExamEngine = cbtExam;
    </script>
</body>
</html>
