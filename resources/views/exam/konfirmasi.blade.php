<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konfirmasi Tes: {{ $jadwal->bankSoal?->mapel?->nama_mapel ?? 'Ujian' }} | {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</title>
    <link rel="icon" type="image/png" href="{{ $appSetting->logo_kiri_url ?? asset('favicon.png') }}">

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
    </style>
</head>
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 antialiased">

    <div class="max-w-xl w-full space-y-5">

        <!-- Top Header Brand -->
        <div class="text-center space-y-1">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 shadow-xl shadow-indigo-600/30 font-black text-xl text-white mb-2">
                C
            </div>
            <h1 class="text-xl font-black text-white tracking-wide">Konfirmasi Tes Peserta</h1>
            <p class="text-xs text-slate-400">Pastikan seluruh rincian tes di bawah ini telah sesuai sebelum memulai pengerjaan.</p>
        </div>

        <!-- Flash Message Alerts -->
        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center gap-3 shadow-lg">
                <svg class="w-5 h-5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Rincian Ujian -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5">
            
            <!-- Identitas Siswa -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nama Peserta:</span>
                    <strong class="text-white text-sm font-bold">{{ $siswa->nama ?? $user->nama_lengkap ?? $user->username }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">No. Peserta:</span>
                    <strong class="font-mono text-emerald-400 text-sm font-bold">{{ $nomorPeserta }}</strong>
                </div>
            </div>

            <!-- Tabel Detail Tes -->
            <div class="space-y-2.5 text-xs text-slate-300">
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">Mata Pelajaran:</span>
                    <strong class="text-white font-bold">{{ $jadwal->bankSoal?->mapel?->nama_mapel ?? '-' }}</strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">Nama Paket Ujian:</span>
                    <strong class="text-indigo-400 font-semibold">{{ $jadwal->bankSoal?->bank_nama ?? 'Ujian CBT' }}</strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">Alokasi Waktu:</span>
                    <strong class="text-white font-bold">{{ $jadwal->durasi_ujian }} Menit</strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">Jumlah Butir Soal:</span>
                    <strong class="text-white font-bold">{{ $totalSoal }} Butir</strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Ruang & Sesi:</span>
                    <strong class="text-slate-200">{{ $namaRuang }} &bull; {{ $namaSesi }}</strong>
                </div>
            </div>

            <!-- Petunjuk Umum Singkat -->
            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 text-[11px] text-slate-400 space-y-1.5 leading-relaxed">
                <div class="font-bold text-slate-300 uppercase tracking-wider text-[10px]">Petunjuk Pengerjaan:</div>
                <ul class="list-disc list-inside space-y-1">
                    <li>Waktu ujian akan mulai berjalan otomatis saat Anda menekan tombol Mulai Ujian.</li>
                    <li>Jawaban Anda otomatis tersimpan (autosave) setiap kali memilih jawaban.</li>
                    <li>Dilarang membuka tab baru, browser lain, atau menutup layar pengerjaan selama ujian.</li>
                </ul>
            </div>

            <!-- Form Submit & Input Token -->
            <form action="{{ route('exam.konfirmasi.proses', $jadwal->id_jadwal) }}" method="POST" class="space-y-4 pt-2">
                @csrf

                @if($pakaiToken)
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-center">
                            Masukkan Kode Token Ujian
                        </label>
                        <input
                            type="text"
                            name="token"
                            maxlength="6"
                            placeholder="------"
                            autocomplete="off"
                            required
                            class="w-full text-center tracking-[0.5em] font-mono text-2xl font-black px-4 py-3 bg-slate-950 border-2 border-indigo-500/40 rounded-2xl text-white placeholder-slate-600 uppercase focus:outline-none focus:border-indigo-400 shadow-inner"
                        >
                        <p class="text-[11px] text-slate-500 text-center mt-1">Minta 6 digit kode token kepada guru/pengawas yang bertugas di ruang ujian.</p>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    <a href="{{ route('exam.index') }}" class="w-full sm:w-auto px-5 py-3 text-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition border border-slate-700">
                        &larr; Batal
                    </a>
                    <button type="submit" class="w-full flex-1 py-3 px-6 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition shadow-xl shadow-indigo-600/30 text-center uppercase tracking-wider flex items-center justify-center gap-2">
                        <span>Mulai Kerjakan Tes Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

    </div>

</body>
</html>
