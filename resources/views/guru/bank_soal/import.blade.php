@extends('layouts.guru')

@section('title', 'Import Butir Soal - ' . $bank->bank_kode)

@section('content')
<div class="space-y-6 w-full">
    <!-- Header -->
    <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-wide">Import Butir Soal Massal</h1>
            <p class="text-xs text-slate-500 mt-1">
                Paket: <strong class="text-slate-900">{{ $bank->bank_nama }}</strong> ({{ $bank->bank_kode }}) &bull; {{ $bank->mapel?->nama_mapel ?? '-' }}
            </p>
        </div>
        <div>
            <a href="{{ route('guru.bank_soal.show', $bank->id_bank) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-2 border border-slate-200">
                &larr; Kembali ke Bank Soal
            </a>
        </div>
    </div>

    <!-- Unduh Template File & Form Upload Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Unduh Template File -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Langkah 1: Unduh Format Template
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Sebelum mengunggah, pastikan butir soal disusun menggunakan template resmi agar format pilihan ganda, kunci jawaban, dan bobot terbaca sempurna:
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ route('guru.bank_soal.template', 'excel') }}" class="flex-1 sm:flex-none px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Unduh Format Excel (.xlsx)</span>
                </a>
                <a href="{{ route('guru.bank_soal.template', 'word') }}" class="flex-1 sm:flex-none px-4 py-2.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Unduh Format Word (.docx)</span>
                </a>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                Langkah 2: Unggah Berkas Soal
            </h2>

            <form action="{{ route('guru.bank_soal.import_process', $bank->id_bank) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Pilih File Soal (Excel / Word):</label>
                    <input type="file" name="file_soal" accept=".xlsx,.xls,.docx" required class="block w-full text-xs text-slate-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer bg-slate-50 p-2 rounded-xl border border-slate-200 transition">
                    <p class="text-[11px] text-slate-500 mt-1.5">Ukuran berkas maksimal 10 MB. Mendukung format Excel (.xlsx, .xls) atau Word (.docx).</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Mulai Import Soal Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Panduan Kode Kolom -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Keterangan Kolom Jenis Soal Pada File Template:</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs text-slate-700">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-black text-xs mb-1.5">Jenis 1</span>
                <p class="font-bold text-slate-900">Pilihan Ganda Biasa</p>
                <p class="text-slate-500 text-[11px] mt-0.5">Opsi jawaban A - E. Berikan tanda v pada baris opsi yang benar pada kolom KUNCI.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="inline-block px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 font-black text-xs mb-1.5">Jenis 2</span>
                <p class="font-bold text-slate-900">Pilihan Ganda Kompleks</p>
                <p class="text-slate-500 text-[11px] mt-0.5">Multi jawaban benar. Berikan tanda v pada opsi-opsi yang benar pada kolom KUNCI.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="inline-block px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-black text-xs mb-1.5">Jenis 3</span>
                <p class="font-bold text-slate-900">Menjodohkan</p>
                <p class="text-slate-500 text-[11px] mt-0.5">Premis dan respons. Tentukan kode baris dan kode kolom yang cocok pada kolom KUNCI.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="inline-block px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 font-black text-xs mb-1.5">Jenis 4</span>
                <p class="font-bold text-slate-900">Isian Singkat</p>
                <p class="text-slate-500 text-[11px] mt-0.5">Kunci berupa kata atau frasa teks eksak yang dinilai otomatis oleh sistem.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 sm:col-span-2 lg:col-span-2">
                <span class="inline-block px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-black text-xs mb-1.5">Jenis 5</span>
                <p class="font-bold text-slate-900">Esai / Uraian Bebas</p>
                <p class="text-slate-500 text-[11px] mt-0.5">Siswa menjawab dalam paragraf uraian. Guru memberikan skor manual pada menu Koreksi Ujian.</p>
            </div>
        </div>
    </div>
</div>
@endsection
