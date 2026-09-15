@extends('layouts.guru')

@section('title', 'Jadwal Pengawasan Ruang')
@section('page_title', 'Jadwal & Penugasan Pengawasan Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Token Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Penugasan Pengawas Ruang</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Silakan klik tombol "Buka Monitoring Ruang" untuk memantau kehadiran dan pengerjaan siswa secara real-time</p>
        </div>
        <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-950 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Token:</span>
            <span class="font-mono text-xl font-black text-emerald-600 dark:text-emerald-400 select-all">{{ $currentToken }}</span>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form action="{{ route('guru.pengawasan.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-80">
            <div class="relative w-full">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari mapel, ruang, atau sesi..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if(request('q'))
                <a href="{{ route('guru.pengawasan.index') }}" class="p-2 text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
        <span class="text-xs text-slate-500 dark:text-slate-400">Total: <strong class="text-slate-800 dark:text-white">{{ $pengawasans->total() }}</strong> Penugasan</span>
    </div>

    <!-- Table Penugasan -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Ujian & Mapel</th>
                        <th class="py-3 px-4">Ruang Ujian</th>
                        <th class="py-3 px-4">Sesi & Jam</th>
                        <th class="py-3 px-4 text-center">Status Jadwal</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($pengawasans as $idx => $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $pengawasans->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $p->jadwal->bankSoal->bank_nama ?? 'Jadwal Ujian' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Mapel: <strong class="text-slate-700 dark:text-slate-300">{{ $p->jadwal->bankSoal->mapel->nama_mapel ?? '-' }}</strong> &bull;
                                    Durasi: {{ $p->jadwal->durasi_ujian ?? '-' }} Menit
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                                    {{ $p->ruang->nama_ruang ?? 'Ruang Ujian' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 dark:text-white">{{ $p->sesi->nama_sesi ?? 'Sesi 1' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $p->sesi->waktu_mulai ?? '' }} - {{ $p->sesi->waktu_selesai ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->jadwal && $p->jadwal->status)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                        BERLANGSUNG
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                        Selesai / Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('guru.pengawasan.monitor', $p->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5 inline-flex" title="Buka Ruang Pengawasan di Tab Baru">
                                    <span>Buka Monitoring</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Anda belum memiliki jadwal penugasan pengawas ruang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengawasans->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $pengawasans->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
