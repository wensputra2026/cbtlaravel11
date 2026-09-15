@extends('layouts.guru')

@section('title', 'Koreksi Soal Esai')
@section('page_title', 'Pemeriksaan & Koreksi Jawaban Esai')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Jadwal Ujian yang Memerlukan Koreksi</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Jadwal ujian dari paket bank soal yang Anda susun dan memiliki butir soal esai / uraian</p>
        </div>
        <form action="{{ route('guru.koreksi.index') }}" method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama ujian / kode..." class="px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 w-52">
            <button type="submit" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">Cari</button>
            @if(request()->filled('q'))
                <a href="{{ route('guru.koreksi.index') }}" class="px-2.5 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Jadwal Koreksi -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Ujian & Bank Soal</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4 text-center">Butir Esai</th>
                        <th class="py-3 px-4 text-center">Status Jadwal</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($jadwalList as $idx => $j)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $jadwalList->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $j->bankSoal->bank_nama ?? 'Ujian' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $j->bankSoal->bank_kode ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-white">
                                {{ $j->bankSoal->mapel->nama_mapel ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-50 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30">
                                    {{ $j->bankSoal->tampil_esai ?? 0 }} Butir Esai
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($j->status)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('guru.koreksi.peserta', $j->id_jadwal) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5 inline-flex">
                                    <span>Periksa Lembar Jawaban &rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada jadwal ujian aktif dari bank soal Anda saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwalList->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
