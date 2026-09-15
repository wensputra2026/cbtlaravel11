<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian: {{ $jadwal->bankSoal?->mapel?->nama_mapel ?? 'Ujian' }} | {{ $appSetting->nama_aplikasi_tampil ?? 'SMANBEN-CBT' }}</title>
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

    <div class="max-w-lg w-full space-y-6 text-center">

        <!-- Checkmark Icon -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-emerald-500/10 border-2 border-emerald-500/40 text-emerald-400 text-3xl shadow-2xl shadow-emerald-950 mx-auto">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>

        <div>
            <h1 class="text-2xl font-black text-white tracking-wide">Ujian Selesai Dikerjakan</h1>
            <p class="text-xs text-slate-400 mt-1">Seluruh lembar jawaban Anda telah berhasil tersimpan dengan aman di server.</p>
        </div>

        <!-- Card Hasil -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-left">
            
            <!-- Identitas Siswa -->
            <div class="pb-4 border-b border-slate-800 flex justify-between items-center text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Peserta:</span>
                    <strong class="text-white text-sm">{{ $siswa->nama ?? $user->nama_lengkap ?? $user->username }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">No. Peserta:</span>
                    <strong class="font-mono text-emerald-400 text-sm">{{ $nomorPeserta }}</strong>
                </div>
            </div>

            <!-- Detail Tes -->
            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between">
                    <span class="text-slate-400">Mata Pelajaran:</span>
                    <strong class="text-white">{{ $jadwal->bankSoal?->mapel?->nama_mapel ?? '-' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Paket Soal:</span>
                    <strong class="text-indigo-400">{{ $jadwal->bankSoal?->bank_nama ?? 'Ujian' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Waktu Penyerahan:</span>
                    <strong class="text-slate-200">{{ $cbtSiswa?->selesai ?? date('Y-m-d H:i:s') }}</strong>
                </div>
            </div>

            <!-- Tampilan Skor Nilai (Jika Diizinkan Sekolah/Jadwal) -->
            @if($tampilkanNilai)
                <div class="pt-4 border-t border-slate-800 text-center space-y-3">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        PEROLEHAN NILAI AKHIR
                    </div>
                    <div class="font-mono text-5xl font-black text-emerald-400 drop-shadow-md">
                        {{ $totalNilai }}
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800">
                            <span class="text-[10px] text-slate-400 block uppercase">Nilai PG:</span>
                            <strong class="font-mono text-white text-sm">{{ $pgNilai }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800">
                            <span class="text-[10px] text-slate-400 block uppercase">Nilai Esai:</span>
                            <strong class="font-mono text-white text-sm">{{ $esaiNilai }}</strong>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-center text-xs text-slate-400 space-y-1">
                    <div class="font-bold text-slate-300">Nilai Dirahasiakan</div>
                    <p class="text-[11px] leading-relaxed">Sesuai kebijakan pelaksanaan ujian, perolehan nilai tidak ditampilkan secara langsung dan akan diumumkan secara resmi oleh guru pengampu.</p>
                </div>
            @endif

            <div class="pt-2">
                <a href="{{ route('exam.index') }}" class="block w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl text-center transition shadow-lg shadow-indigo-600/30 uppercase tracking-wider">
                    &larr; Kembali ke Beranda Peserta
                </a>
            </div>
        </div>

    </div>

</body>
</html>
