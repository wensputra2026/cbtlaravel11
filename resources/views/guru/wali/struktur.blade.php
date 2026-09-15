@extends('layouts.guru')

@section('title', 'Struktur Organisasi Kelas ' . ($waliKelas->nama_kelas ?? ''))
@section('page_title', 'Struktur Kelas ' . ($waliKelas->nama_kelas ?? ''))

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-teal-700 via-emerald-600 to-cyan-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/20 text-white backdrop-blur-sm border border-white/20">
                        PENGURUS KELAS
                    </span>
                    <span class="text-xs text-teal-100 font-medium">
                        TP {{ $assignment['active_tp']->tahun ?? '-' }} • Smt {{ $assignment['active_smt']->smt ?? '-' }}
                    </span>
                </div>
                <h2 class="text-2xl font-black tracking-tight">Struktur Organisasi {{ $waliKelas->nama_kelas }}</h2>
                <p class="text-sm text-teal-100 mt-1">
                    Kelola susunan kepengurusan kelas dan seksi bidang 7K bimbingan Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('guru.wali.siswa') }}" class="px-4 py-2 bg-white/15 hover:bg-white/25 backdrop-blur-md border border-white/20 rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Daftar Siswa</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Struktur Form Card -->
    <form action="{{ route('guru.wali.struktur.save') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. PIMPINAN INTI KELAS -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-sm">
                    1
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-white">Pimpinan & Administrasi Inti Kelas</h3>
                    <p class="text-xs text-slate-400">Ketua, Wakil, Sekretaris, dan Bendahara kelas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Ketua Kelas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Ketua Kelas
                    </label>
                    <select name="ketua" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id_siswa }}" {{ ($struktur->ketua ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                {{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Wakil Ketua Kelas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                        Wakil Ketua Kelas
                    </label>
                    <select name="wakil_ketua" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id_siswa }}" {{ ($struktur->wakil_ketua ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                {{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sekretaris 1 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Sekretaris 1</label>
                    <select name="sekretaris_1" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id_siswa }}" {{ ($struktur->sekretaris_1 ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                {{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sekretaris 2 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Sekretaris 2</label>
                    <select name="sekretaris_2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id_siswa }}" {{ ($struktur->sekretaris_2 ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                {{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Bendahara 1 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Bendahara 1</label>
                    <select name="bendahara_1" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id_siswa }}" {{ ($struktur->bendahara_1 ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                {{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Bendahara 2 -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Bendahara 2</label>
                    <select name="bendahara_2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswas as $s)
                            <option value="{{ $s->id_siswa }}" {{ ($struktur->bendahara_2 ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                {{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. SEKSI BIDANG KEGIATAN & 7K -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                <div class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950 text-sky-600 dark:text-sky-400 flex items-center justify-center font-black text-sm">
                    2
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-white">Seksi Bidang & 7K (Ketertiban, Kebersihan, Keamanan)</h3>
                    <p class="text-xs text-slate-400">Penanggung jawab kegiatan harian dan tata tertib kelas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $seksiList = [
                        'sie_keagamaan'       => 'Sie Keagamaan',
                        'sie_upacara'         => 'Sie Upacara',
                        'sie_olahraga'        => 'Sie Olahraga',
                        'sie_ekstrakurikuler' => 'Sie Ekstrakurikuler',
                        'sie_keamanan'        => 'Sie Keamanan',
                        'sie_ketertiban'      => 'Sie Ketertiban',
                        'sie_kebersihan'      => 'Sie Kebersihan',
                        'sie_keindahan'       => 'Sie Keindahan',
                        'sie_kesehatan'       => 'Sie Kesehatan / UKS',
                        'sie_kekeluargaan'    => 'Sie Kekeluargaan',
                        'sie_humas'           => 'Sie Humas',
                    ];
                @endphp

                @foreach($seksiList as $field => $label)
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ $label }}</label>
                        <select name="{{ $field }}" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id_siswa }}" {{ ($struktur->{$field} ?? null) == $s->id_siswa ? 'selected' : '' }}>
                                    {{ $s->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Struktur Organisasi</span>
            </button>
        </div>
    </form>

</div>
@endsection
