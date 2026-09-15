@extends('layouts.admin')

@section('title', 'Dashboard Utama')
@section('page_title', 'Dashboard Ringkasan CBT')

@section('content')
<div class="space-y-6">

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Siswa</span>
                <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">👨‍🎓</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalSiswa) }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Terdaftar di sistem</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Guru</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">👩‍🏫</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalGuru) }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Guru pengampu</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rombel Kelas</span>
                <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">🏫</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalKelas) }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Seluruh tingkatan</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mata Pelajaran</span>
                <span class="p-1.5 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400">📚</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalMapel) }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Kurikulum aktif</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm col-span-2 md:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Bank Soal</span>
                <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">📝</span>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalBank) }}</div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Paket soal dibuat</div>
        </div>
    </div>

    <!-- Active Exams & Real-Time Concurrency Monitor -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Jadwal Ujian Aktif Hari Ini (2 Cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-white">Jadwal Ujian Aktif</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Jadwal yang siap diakses dan dikerjakan oleh siswa</p>
                    </div>
                    <a href="{{ route('admin.cbt.jadwal.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition">
                        Kelola Jadwal &rarr;
                    </a>
                </div>

                @if($jadwalAktif->isEmpty())
                    <div class="py-12 text-center text-slate-400 dark:text-slate-500 text-xs">
                        Belum ada jadwal ujian berstatus aktif saat ini.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($jadwalAktif as $j)
                            <div class="py-3 flex items-center justify-between">
                                <div class="truncate mr-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-500/30">
                                            {{ $j->jenis->kode_jenis ?? 'CBT' }}
                                        </span>
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $j->bankSoal->bank_nama ?? 'Ujian' }}</h4>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        Mapel: <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $j->bankSoal->mapel->nama_mapel ?? '-' }}</span> &bull; 
                                        Durasi: <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $j->durasi_ujian }} Menit</span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <a href="{{ route('proctor.monitor', $j->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1 text-xs font-semibold rounded-lg bg-sky-50 dark:bg-sky-600/20 text-sky-600 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30 hover:bg-sky-100 dark:hover:bg-sky-600/30 transition flex items-center gap-1">
                                        <span>Monitor</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span>Peserta Sedang Ujian: <strong class="text-amber-600 dark:text-amber-400">{{ $pesertaAktif }}</strong></span>
                <span>Peserta Telah Selesai: <strong class="text-emerald-600 dark:text-emerald-400">{{ $pesertaSelesai }}</strong></span>
            </div>
        </div>

        <!-- Server & Concurrency Health Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-slate-800 dark:text-white mb-1">Status Server & Performa</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Arsitektur High-Concurrency VPS</p>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">Runtime PHP:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-white">{{ $serverInfo['php_version'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">Engine Framework:</span>
                        <span class="font-bold text-brand-600 dark:text-brand-400">Laravel {{ $serverInfo['laravel_version'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">Penggunaan Memori:</span>
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $serverInfo['memory_usage'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">Kapasitas Bersamaan:</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400">450 Siswa + 50 Guru</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.setting.maintenance') }}" class="w-full block py-2 text-center text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                    Buka Pemeliharaan Sistem &rarr;
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
