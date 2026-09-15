@extends('layouts.guru')

@section('title', 'Import Butir Soal - ' . $bank->bank_kode)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-wide">Import Butir Soal Massal</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Paket: <strong class="text-slate-800 dark:text-white">{{ $bank->bank_nama }}</strong> ({{ $bank->bank_kode }}) &bull; {{ $bank->mapel?->nama_mapel ?? '-' }}
            </p>
        </div>
        <div>
            <a href="{{ route('guru.bank_soal.show', $bank->id_bank) }}" class="px-4 py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-white text-xs font-bold rounded-xl transition flex items-center gap-2 border border-slate-200 dark:border-slate-700 shadow-sm">
                &larr; Kembali ke Bank Soal
            </a>
        </div>
    </div>

    <!-- Unduh Template File -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Langkah 1: Unduh Format Template
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Sebelum mengunggah, pastikan butir soal disusun menggunakan template resmi di bawah ini:
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="{{ route('guru.bank_soal.template', 'csv') }}" class="px-4 py-2.5 bg-emerald-50 dark:bg-emerald-600/20 hover:bg-emerald-100 dark:hover:bg-emerald-600/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/40 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh Format Excel (CSV)</span>
            </a>
            <a href="{{ route('guru.bank_soal.template', 'word') }}" class="px-4 py-2.5 bg-sky-50 dark:bg-sky-600/20 hover:bg-sky-100 dark:hover:bg-sky-600/30 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-500/40 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh Panduan Format Word (.doc)</span>
            </a>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
            Langkah 2: Unggah Berkas Soal
        </h2>

        <form action="{{ route('guru.bank_soal.import_process', $bank->id_bank) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Pilih File Soal (.CSV):</label>
                <input type="file" name="file_soal" accept=".csv,.txt" required class="block w-full text-xs text-slate-600 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 dark:file:bg-slate-800 file:text-emerald-600 dark:file:text-emerald-400 hover:file:bg-slate-200 dark:hover:file:bg-slate-700 cursor-pointer bg-slate-50 dark:bg-slate-950/60 p-2 rounded-xl border border-slate-200 dark:border-slate-700">
                <p class="text-[11px] text-slate-500 mt-1">Ukuran berkas maksimal 5 MB. Format wajib sesuai template CSV.</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Mulai Import Soal</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Panduan Kode Kolom -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Keterangan Kolom Jenis Soal:</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-600 dark:text-slate-300">
            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50">
                <strong class="text-emerald-600 dark:text-emerald-400">1:</strong> Pilihan Ganda Biasa (Opsi A - E, Kunci: huruf A/B/C/D/E)
            </div>
            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50">
                <strong class="text-purple-600 dark:text-purple-400">2:</strong> Pilihan Ganda Kompleks (Multi-jawaban checklist)
            </div>
            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50">
                <strong class="text-amber-600 dark:text-amber-400">3:</strong> Menjodohkan (Premis dan Respons)
            </div>
            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50">
                <strong class="text-teal-600 dark:text-teal-400">4:</strong> Isian Singkat (Kunci: kata / frasa jawaban eksak)
            </div>
            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/50 sm:col-span-2">
                <strong class="text-rose-600 dark:text-rose-400">5:</strong> Esai / Uraian (Kunci: panduan rubrik penilaian untuk guru saat koreksi manual)
            </div>
        </div>
    </div>
</div>
@endsection
