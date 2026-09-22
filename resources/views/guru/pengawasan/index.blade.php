@extends('layouts.guru')

@section('title', 'Jadwal Pengawasan Ruang')
@section('page_title', 'Jadwal & Penugasan Pengawasan Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Token Banner -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-black text-slate-900">Daftar Penugasan Pengawas Ruang</h3>
            <p class="text-xs text-slate-500">Silakan klik tombol "Buka Monitoring Ruang" untuk memantau kehadiran dan pengerjaan siswa secara real-time</p>
        </div>
        <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
            <span class="text-xs font-semibold text-slate-600">Token Ujian:</span>
            <span class="font-mono text-xl font-black text-emerald-600 select-all">{{ $currentToken }}</span>
        </div>
    </div>

    <!-- Table Penugasan -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Ujian & Mapel</th>
                        <th class="py-3 px-4">Ruang Ujian</th>
                        <th class="py-3 px-4">Sesi & Jam</th>
                        <th class="py-3 px-4 text-center">Status Jadwal</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pengawasans as $idx => $p)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $p->jadwal->bankSoal->bank_nama ?? 'Jadwal Ujian' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Mapel: <strong class="text-slate-800">{{ $p->jadwal->bankSoal->mapel->nama_mapel ?? '-' }}</strong> &bull;
                                    Durasi: {{ $p->jadwal->durasi_ujian ?? '-' }} Menit
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $p->ruang->nama_ruang ?? 'Ruang Ujian' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900">{{ $p->sesi->nama_sesi ?? 'Sesi 1' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $p->sesi->waktu_mulai ?? '' }} - {{ $p->sesi->waktu_selesai ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->jadwal && $p->jadwal->status)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        BERLANGSUNG
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 text-slate-600">
                                        Selesai / Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('guru.pengawasan.monitor', $p->id_jadwal) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition inline-flex items-center gap-1.5">
                                    <span>Buka Monitoring</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Anda belum memiliki jadwal penugasan pengawas ruang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
