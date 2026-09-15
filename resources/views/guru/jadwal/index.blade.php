@extends('layouts.guru')

@section('title', 'Jadwal Ujian Saya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-wide">Jadwal Ujian Saya</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar tes yang menggunakan paket bank soal Anda & penugasan pengawasan ruang.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.token.index') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-md shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>Lihat Token Ujian</span>
            </a>
        </div>
    </div>

    <!-- 1. Jadwal Ujian Bank Soal Saya -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 gap-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Ujian Menggunakan Bank Soal Anda ({{ $jadwalSaya instanceof \Illuminate\Pagination\LengthAwarePaginator ? $jadwalSaya->total() : count($jadwalSaya) }})
            </h2>
            <div class="flex items-center gap-2">
                <form action="{{ route('guru.jadwal.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama ujian / kode..." class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 w-44 sm:w-52">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">Cari</button>
                    @if(request()->filled('q'))
                        <a href="{{ route('guru.jadwal.index') }}" class="px-2 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">Reset</a>
                    @endif
                </form>
                <a href="{{ route('guru.bank_soal.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline shrink-0">Kelola Bank Soal &rarr;</a>
            </div>
        </div>

        @if($jadwalSaya->isEmpty())
            <div class="text-center py-10 text-slate-400 text-xs">
                Belum ada jadwal ujian yang dijadwalkan menggunakan bank soal Anda.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-3">No</th>
                            <th class="py-3 px-3">Nama Ujian</th>
                            <th class="py-3 px-3">Mata Pelajaran</th>
                            <th class="py-3 px-3">Waktu Pelaksanaan</th>
                            <th class="py-3 px-3 text-center">Durasi</th>
                            <th class="py-3 px-3 text-center">Token</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium text-slate-600 dark:text-slate-300">
                        @foreach($jadwalSaya as $index => $j)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 text-slate-400">
                                    {{ $jadwalSaya instanceof \Illuminate\Pagination\LengthAwarePaginator ? $jadwalSaya->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-800 dark:text-white">
                                    {{ $j->jenis?->nama_jenis ?? 'Ujian' }} - {{ $j->bankSoal?->bank_nama ?? 'Bank Soal' }}
                                    <div class="text-[10px] text-slate-400 font-normal">Kode: {{ $j->bankSoal?->bank_kode ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 font-semibold text-[11px]">
                                        {{ $j->bankSoal?->mapel?->nama_mapel ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-700 dark:text-slate-300 font-mono text-[11px]">
                                    {{ $j->tgl_mulai ? date('d/m/Y H:i', strtotime($j->tgl_mulai)) : '-' }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-800 dark:text-white">
                                    {{ $j->durasi_menit ?? 60 }}m
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 font-mono text-xs font-bold rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                        {{ $j->token ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($j->status)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right space-x-2">
                                    <a href="{{ route('guru.pengawasan.monitor', $j->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-1 bg-sky-50 hover:bg-sky-100 dark:bg-sky-600/20 dark:hover:bg-sky-600/40 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 rounded-lg text-xs font-semibold transition" title="Monitoring Ruang di Tab Baru">
                                        <span>Monitor</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <a href="{{ route('guru.hasil.index', ['jadwal_id' => $j->id_jadwal]) }}" class="inline-block px-3 py-1 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-600/20 dark:hover:bg-emerald-600/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 rounded-lg text-xs font-semibold transition" title="Rekap Nilai">
                                        Nilai
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($jadwalSaya instanceof \Illuminate\Pagination\LengthAwarePaginator && $jadwalSaya->hasPages())
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                    {{ $jadwalSaya->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- 2. Tugas Pengawasan Ruang Ujian -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Tugas Pengawasan Ruang Ujian ({{ count($tugasPengawas) }})
            </h2>
        </div>

        @if($tugasPengawas->isEmpty())
            <div class="text-center py-8 text-slate-400 text-xs">
                Anda belum memiliki alokasi penugasan pengawas ruang saat ini.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tugasPengawas as $tp)
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white text-sm">{{ $tp->jadwal?->bankSoal?->bank_nama ?? 'Ujian' }}</h3>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">{{ $tp->jadwal?->bankSoal?->mapel?->nama_mapel ?? '-' }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30">Pengawas</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700/40 text-xs space-y-1 text-slate-600 dark:text-slate-300">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Ruang Ujian:</span>
                                <strong class="text-slate-800 dark:text-white">{{ $tp->ruang?->nama_ruang ?? 'Ruang 01' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Sesi:</span>
                                <strong class="text-slate-800 dark:text-white">{{ $tp->sesi?->nama_sesi ?? 'Sesi 1' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Waktu:</span>
                                <span>{{ $tp->jadwal?->tgl_mulai ? date('d M Y, H:i', strtotime($tp->jadwal->tgl_mulai)) : '-' }}</span>
                            </div>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('guru.pengawasan.monitor', $tp->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="block text-center py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-lg transition shadow-sm flex items-center justify-center gap-1.5" title="Buka Monitoring di Tab Baru">
                                <span>Masuk Monitoring Ruang</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
