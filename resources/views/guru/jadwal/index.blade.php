@extends('layouts.guru')

@section('title', 'Jadwal Ujian Saya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-2xl shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-wide">Jadwal Ujian Saya</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar tes yang menggunakan paket bank soal Anda & penugasan pengawasan ruang.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.token.index') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>Lihat Token Ujian</span>
            </a>
        </div>
    </div>

    <!-- 1. Jadwal Ujian Bank Soal Saya -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Ujian Menggunakan Bank Soal Anda ({{ count($jadwalSaya) }})
            </h2>
            <a href="{{ route('guru.bank_soal.index') }}" class="text-xs text-emerald-600 hover:underline font-semibold">Kelola Bank Soal &rarr;</a>
        </div>

        @if($jadwalSaya->isEmpty())
            <div class="text-center py-10 text-slate-500 text-xs">
                Belum ada jadwal ujian yang dijadwalkan menggunakan bank soal Anda.
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
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
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($jadwalSaya as $index => $j)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3 font-bold text-slate-900">
                                    {{ $j->jenis?->nama_jenis ?? 'Ujian' }} - {{ $j->bankSoal?->bank_nama ?? 'Bank Soal' }}
                                    <div class="text-[10px] text-slate-500 font-normal">Kode: {{ $j->bankSoal?->bank_kode ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold text-[11px]">
                                        {{ $j->bankSoal?->mapel?->nama_mapel ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-slate-800 font-medium">{{ $j->tgl_mulai ? date('d M Y, H:i', strtotime($j->tgl_mulai)) : '-' }}</div>
                                    <div class="text-[10px] text-slate-400">s/d {{ $j->tgl_selesai ? date('d M Y, H:i', strtotime($j->tgl_selesai)) : '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="font-bold text-slate-900">{{ $j->durasi_ujian }}</span> Menit
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($j->token)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Wajib</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Tanpa</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($j->status)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right space-x-2">
                                    <a href="{{ route('guru.pengawasan.monitor', $j->id_jadwal) }}" class="inline-block px-3 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-lg text-xs font-semibold transition" title="Monitoring Ruang">
                                        Monitor
                                    </a>
                                    <a href="{{ route('guru.hasil.index', ['jadwal_id' => $j->id_jadwal]) }}" class="inline-block px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold transition" title="Rekap Nilai">
                                        Nilai
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 2. Tugas Pengawasan Ruang Ujian -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Tugas Pengawasan Ruang Ujian ({{ count($tugasPengawas) }})
            </h2>
        </div>

        @if($tugasPengawas->isEmpty())
            <div class="text-center py-8 text-slate-500 text-xs">
                Anda belum memiliki alokasi penugasan pengawas ruang saat ini.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tugasPengawas as $tp)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">{{ $tp->jadwal?->bankSoal?->bank_nama ?? 'Ujian' }}</h3>
                                <div class="text-xs text-emerald-700 font-semibold">{{ $tp->jadwal?->bankSoal?->mapel?->nama_mapel ?? '-' }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">Pengawas</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 text-xs space-y-1 text-slate-600">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Ruang Ujian:</span>
                                <strong class="text-slate-900">{{ $tp->ruang?->nama_ruang ?? 'Ruang 01' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Sesi:</span>
                                <strong class="text-slate-900">{{ $tp->sesi?->nama_sesi ?? 'Sesi 1' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Waktu:</span>
                                <span>{{ $tp->jadwal?->tgl_mulai ? date('d M Y, H:i', strtotime($tp->jadwal->tgl_mulai)) : '-' }}</span>
                            </div>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('guru.pengawasan.monitor', $tp->id_jadwal) }}" class="block text-center py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-lg transition shadow-xs">
                                Masuk Monitoring Ruang &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
