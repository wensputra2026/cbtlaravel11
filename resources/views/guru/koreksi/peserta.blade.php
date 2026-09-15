@extends('layouts.guru')

@section('title', 'Peserta Ujian: ' . ($jadwal->bankSoal->bank_nama ?? 'Ujian'))
@section('page_title', 'Daftar Lembar Jawaban Peserta')

@section('content')
<div class="space-y-6">

    <!-- Header & Jadwal Info -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('guru.koreksi.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Jadwal Koreksi
                </a>
            </div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $jadwal->bankSoal->bank_nama ?? 'Ujian' }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Mapel: <span class="text-slate-700 dark:text-slate-200 font-semibold">{{ $jadwal->bankSoal->mapel->nama_mapel ?? '-' }}</span> &bull; 
                Total Peserta: <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $pesertaList->total() }} Siswa</span>
            </p>
        </div>

        <!-- Filter & Search Form -->
        <form action="{{ route('guru.koreksi.peserta', $jadwal->id_jadwal) }}" method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NISN..." class="px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 w-48 sm:w-64">
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Selesai</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Mengerjakan</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Belum Mulai</option>
            </select>
            <button type="submit" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                Cari
            </button>
            @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('guru.koreksi.peserta', $jadwal->id_jadwal) }}" class="px-2.5 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Peserta -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4 text-center">Status Ujian</th>
                        <th class="py-3 px-4 text-center">Nilai PG</th>
                        <th class="py-3 px-4 text-center">Nilai Esai</th>
                        <th class="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-300">Total Nilai</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($pesertaList as $idx => $p)
                        @php
                            $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                            $nilaiPg = (float)($input['pg_nilai'] ?? 0);
                            $nilaiEsai = (float)($input['essai_nilai'] ?? 0);
                            $isKoreksi = !empty($input['dikoreksi']);
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 text-slate-400">{{ $pesertaList->firstItem() + $idx }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $p->siswa->nama ?? 'Siswa' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $p->siswa->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $p->siswa->kelasSiswa->first()?->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($p->status == 2)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                        Selesai
                                    </span>
                                @elseif($p->status == 1)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 animate-pulse">
                                        Mengerjakan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                        Belum Mulai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($nilaiPg, 1) }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                                {{ number_format($nilaiEsai, 1) }}
                                @if($isKoreksi)
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-sans font-normal">&check; Sudah Dikoreksi</span>
                                @else
                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 block font-sans font-normal">Belum Dikoreksi</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-black text-sm text-emerald-600 dark:text-emerald-300">
                                {{ number_format($nilaiPg + $nilaiEsai, 1) }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('guru.koreksi.form', $p->id_cbt_siswa) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-600/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-600 hover:text-white transition">
                                    Buka Koreksi &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada siswa yang mengerjakan jadwal ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pesertaList->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $pesertaList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
