@extends('layouts.guru')

@section('title', 'Koreksi: ' . ($cbtSiswa->siswa->nama ?? 'Siswa'))
@section('page_title', 'Periksa Lembar Jawaban & Input Nilai Esai')

@section('content')
<div class="space-y-6">

    <!-- Header & Student Meta -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('guru.koreksi.peserta', $cbtSiswa->id_jadwal) }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-semibold">
                        &larr; Kembali ke Daftar Peserta
                    </a>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $cbtSiswa->siswa->nama ?? 'Peserta' }}</h3>
                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <span>NISN: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ $cbtSiswa->siswa->nisn ?? '-' }}</strong></span>
                    &bull;
                    <span>Ujian: <strong class="text-slate-700 dark:text-slate-200">{{ $cbtSiswa->jadwal->bankSoal->bank_nama ?? '-' }}</strong></span>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Nilai PG</span>
                    <span class="font-mono text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($nilaiInput['pg_nilai'] ?? 0, 1) }}</span>
                </div>
                <div class="w-px h-8 bg-slate-200 dark:bg-slate-800"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Nilai Esai</span>
                    <span class="font-mono text-xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($nilaiInput['essai_nilai'] ?? 0, 1) }}</span>
                </div>
                <div class="w-px h-8 bg-slate-200 dark:bg-slate-800"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400 block">Total Akhir</span>
                    <span class="font-mono text-2xl font-black text-emerald-600 dark:text-emerald-300">
                        {{ number_format(($nilaiInput['pg_nilai'] ?? 0) + ($nilaiInput['essai_nilai'] ?? 0), 1) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Form Penilaian Esai -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-2 flex items-center gap-2">
            <span>✏️</span> Form Penilaian Jawaban Esai Guru
        </h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Periksa uraian jawaban siswa di bawah, lalu masukkan perolehan nilai esai siswa:</p>

        <form action="{{ route('guru.koreksi.store', $cbtSiswa->id_cbt_siswa) }}" method="POST" class="flex items-end gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Skor / Nilai Esai</label>
                <input type="number" step="0.1" name="nilai_esai" value="{{ $nilaiInput['essai_nilai'] ?? 0 }}" required class="w-44 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 transition">
                Simpan Penilaian Esai
            </button>
        </form>
    </div>

    <!-- Rekaman Jawaban Siswa -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <span>📋</span> Rekaman Jawaban Siswa
        </h4>

        @if(empty($jawabanList))
            <div class="py-8 text-center text-xs text-slate-400">
                Tidak ada data jawaban yang tercatat.
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                @foreach($jawabanList as $num => $ans)
                    <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl">
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold mb-1">Nomor #{{ $num }}</div>
                        <div class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-300 truncate">
                            {{ is_array($ans) ? json_encode($ans) : ($ans ?: '-') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
