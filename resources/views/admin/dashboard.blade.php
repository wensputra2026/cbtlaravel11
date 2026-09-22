@extends('layouts.admin')

@section('title', 'Dashboard Utama')
@section('page_title', 'Dashboard Ringkasan CBT')

@section('content')
<div class="space-y-6">

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Total Siswa</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">👨‍🎓</span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalSiswa) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Terdaftar di sistem</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Total Guru</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">👩‍🏫</span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalGuru) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Guru pengampu</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Rombel Kelas</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">🏫</span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalKelas) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Seluruh tingkatan</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Mata Pelajaran</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600">📚</span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalMapel) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Kurikulum aktif</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition col-span-2 md:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Bank Soal</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600">📝</span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalBank) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Paket soal dibuat</div>
        </div>
    </div>

    <!-- Active Exams & Real-Time Concurrency Monitor -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Jadwal Ujian Aktif Hari Ini (2 Cols) -->
        <div class="lg:col-span-2 bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Jadwal Ujian Aktif</h3>
                        <p class="text-xs text-slate-500">Jadwal yang siap diakses dan dikerjakan oleh siswa</p>
                    </div>
                    <a href="{{ route('admin.cbt.jadwal.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 transition">
                        Kelola Jadwal &rarr;
                    </a>
                </div>

                @if($jadwalAktif->isEmpty())
                    <div class="py-12 text-center text-slate-500 text-xs">
                        Belum ada jadwal ujian berstatus aktif saat ini.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($jadwalAktif as $j)
                            <div class="py-3 flex items-center justify-between">
                                <div class="truncate mr-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                            {{ $j->jenis->kode_jenis ?? 'CBT' }}
                                        </span>
                                        <h4 class="text-xs font-bold text-slate-900 truncate">{{ $j->bankSoal->bank_nama ?? 'Ujian' }}</h4>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Mapel: <span class="text-slate-700 font-medium">{{ $j->bankSoal->mapel->nama_mapel ?? '-' }}</span> &bull; 
                                        Durasi: <span class="text-slate-700 font-medium">{{ $j->durasi_ujian }} Menit</span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <a href="{{ route('proctor.monitor', $j->id_jadwal) }}" class="px-3 py-1 text-xs font-semibold rounded-lg bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition">
                                        Monitor &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Peserta Sedang Ujian: <strong class="text-amber-600">{{ $pesertaAktif }}</strong></span>
                <span>Peserta Telah Selesai: <strong class="text-emerald-600">{{ $pesertaSelesai }}</strong></span>
            </div>
        </div>

        <!-- Server & Concurrency Health Widget -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="mb-4 pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900 mb-0.5">Status Server & Performa</h3>
                    <p class="text-xs text-slate-500">Arsitektur High-Concurrency VPS</p>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Runtime PHP:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $serverInfo['php_version'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Engine Framework:</span>
                        <span class="font-bold text-brand-600">Laravel {{ $serverInfo['laravel_version'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Penggunaan Memori:</span>
                        <span class="font-mono font-bold text-emerald-600">{{ $serverInfo['memory_usage'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Kapasitas Bersamaan:</span>
                        <span class="font-bold text-sky-600">450 Siswa + 50 Guru</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.setting.maintenance') }}" class="w-full block py-2 text-center text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Buka Pemeliharaan Sistem &rarr;
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
