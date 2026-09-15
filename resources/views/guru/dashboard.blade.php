@extends('layouts.guru')

@section('title', 'Dashboard Guru & Pengawas')
@section('page_title', 'Dashboard Guru')

@section('content')
<div class="space-y-6 w-full">

    <!-- Welcome & Live Token Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 dark:from-emerald-950/80 dark:via-slate-900 dark:to-slate-900 text-white rounded-3xl p-6 shadow-lg flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <div class="w-16 h-16 rounded-2xl bg-white/20 dark:bg-emerald-900/60 border-2 border-white/30 text-white font-black text-2xl flex items-center justify-center shadow-md shrink-0">
                {{ strtoupper(substr($guru?->nama_guru ?? Auth::user()->username ?? 'G', 0, 1)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    @if($assignment['is_wali_kelas'] && $assignment['wali_kelas'])
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-400 text-emerald-950 shadow-sm">
                            Wali Kelas {{ $assignment['wali_kelas']->nama_kelas }}
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/20">
                        Guru Pengampu Mapel
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-white/10 text-emerald-100">
                        TP {{ $assignment['active_tp']->tahun ?? '-' }} (Smt {{ $assignment['active_smt']->smt ?? '-' }})
                    </span>
                </div>
                <h3 class="text-2xl font-black text-white tracking-tight">{{ $guru?->nama_guru ?? Auth::user()->username }}</h3>
                <p class="text-xs text-emerald-100 dark:text-slate-400 font-mono mt-0.5">
                    NIP: {{ $guru?->nip ?? '-' }} &bull; Username: {{ Auth::user()->username }}
                </p>
            </div>
        </div>

        <!-- Token Box for Proctoring -->
        <div class="bg-white/15 dark:bg-slate-950/80 backdrop-blur-md border border-white/20 dark:border-emerald-500/30 px-6 py-3.5 rounded-2xl text-center shadow-lg shrink-0 relative z-10 min-w-[200px]">
            <span class="text-[10px] uppercase font-bold text-emerald-100 dark:text-slate-400 block tracking-wider">Token Ujian Aktif</span>
            <div class="font-mono text-3xl font-black tracking-widest text-white dark:text-emerald-300 py-0.5 select-all">
                {{ $currentToken }}
            </div>
            <span class="text-[10px] text-emerald-100/90 dark:text-slate-500">Berlaku untuk seluruh ruang ujian</span>
        </div>
    </div>

    @if($assignment['is_wali_kelas'] && $assignment['wali_kelas'])
    <!-- Wali Kelas Quick Access Card -->
    <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-cyan-500/10 border border-emerald-500/30 dark:border-emerald-500/20 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950 px-2 py-0.5 rounded">TUGAS WALI KELAS</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">• {{ $assignment['wali_kelas_siswa_count'] }} Siswa Terdaftar</span>
                    </div>
                    <h4 class="text-lg font-black text-slate-900 dark:text-white mt-0.5">Kelas {{ $assignment['wali_kelas']->nama_kelas }}</h4>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('guru.wali.siswa') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Daftar Siswa</span>
                </a>
                <a href="{{ route('guru.wali.struktur') }}" class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Struktur Kelas</span>
                </a>
                <a href="{{ route('guru.wali.catatan') }}" class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Catatan Bimbingan</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtered Quick Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- 1. Mapel Diampu -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mapel Diampu</span>
                <span class="p-1.5 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 font-bold">📚</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ count($assignment['assigned_mapels']) }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Mata pelajaran aktif</div>
        </div>

        <!-- 2. Rombel Diampu -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kelas / Rombel</span>
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold">🏫</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ count($assignment['assigned_classes']) }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Rombongan belajar</div>
        </div>

        <!-- 3. Total Siswa Diajar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Siswa Diajar</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">👥</span>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($assignment['total_siswa_diampu']) }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Siswa riil terdaftar</div>
        </div>

        <!-- 4. Bank Soal Saya -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Bank Soal Saya</span>
                <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold">📝</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ count($myBanks) }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Paket bank soal dibuat</div>
        </div>

        <!-- 5. Tugas Pengawasan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pengawasan</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold">👁️</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ count($myPengawasan) }}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Jadwal & sesi ruang</div>
        </div>
    </div>

    <!-- Section Penugasan Mata Pelajaran & Kelas -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">📖</span>
                <div>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white">Mata Pelajaran & Kelas Bimbingan Anda</h4>
                    <p class="text-[11px] text-slate-400">Penugasan resmi berdasarkan SK Pembagian Tugas Semester Aktif</p>
                </div>
            </div>
            <a href="{{ route('guru.bank_soal.index') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                Kelola Bank Soal &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($assignment['assigned_mapels'] as $mapel)
                @php
                    $detail = $assignment['mapel_details'][$mapel->id_mapel] ?? null;
                    $targetKelasIds = $detail['kelas_ids'] ?? [];
                    $mapelClasses = $assignment['assigned_classes']->whereIn('id_kelas', $targetKelasIds);
                @endphp
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-emerald-500/40 transition">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                {{ $mapel->kode ?? '-' }}
                            </span>
                            <h5 class="text-sm font-black text-slate-900 dark:text-white mt-1.5">{{ $mapel->nama_mapel }}</h5>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2 py-1 rounded-lg">
                            {{ count($targetKelasIds) }} Kelas
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Daftar Kelas Diampu:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($mapelClasses as $c)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $c->nama_kelas }}
                                </span>
                            @empty
                                <span class="text-[11px] text-slate-400 italic">Belum ada alokasi rombel spesifik.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-8 text-center text-xs text-slate-400">
                    Belum ada penugasan mata pelajaran diatur oleh Administrator untuk akun Anda pada semester aktif ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2 Column Section: Jadwal Pengawasan & Bank Soal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Jadwal Pengawasan Ruang -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">📋</span>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white">Penugasan Pengawasan Anda</h4>
                </div>
                <a href="{{ route('guru.pengawasan.index') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                    Semua Penugasan &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($myPengawasan as $pengawas)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-white text-xs">
                                {{ $pengawas->jadwal->bankSoal->bank_nama ?? 'Ujian' }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Ruang: <strong class="text-slate-700 dark:text-slate-300">{{ $pengawas->ruang->nama_ruang ?? '-' }}</strong> &bull; 
                                Sesi: <strong class="text-slate-700 dark:text-slate-300">{{ $pengawas->sesi->nama_sesi ?? '-' }}</strong>
                            </div>
                        </div>
                        <a href="{{ route('guru.pengawasan.monitor', $pengawas->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-600 hover:text-white transition flex items-center gap-1.5" title="Buka Ruang Monitor di Tab Baru">
                            <span>Masuk Ruang Monitor</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada penugasan ruang pengawasan ujian untuk Anda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Bank Soal Saya -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold">📝</span>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white">Paket Bank Soal Terakhir</h4>
                </div>
                <a href="{{ route('guru.bank_soal.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    Kelola Soal &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($myBanks->take(5) as $bank)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-white text-xs">{{ $bank->bank_nama }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Mapel: <strong class="text-slate-700 dark:text-slate-300">{{ $bank->mapel->nama_mapel ?? '-' }}</strong> &bull; 
                                Kode: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $bank->bank_kode }}</strong>
                            </div>
                        </div>
                        <a href="{{ route('guru.bank_soal.show', $bank->id_bank) }}" class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            Edit Soal
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Anda belum membuat paket bank soal.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
