@extends('layouts.guru')

@section('title', 'Peserta Ujian: ' . ($jadwal->bankSoal->bank_nama ?? 'Ujian'))
@section('page_title', 'Daftar Lembar Jawaban Peserta')

@section('content')
<div class="space-y-6">

    <!-- Header & Jadwal Info -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('guru.koreksi.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 flex items-center gap-1 font-semibold">
                &larr; Kembali ke Daftar Jadwal Koreksi
            </a>
        </div>
        <h3 class="text-lg font-black text-slate-900">{{ $jadwal->bankSoal->bank_nama ?? 'Ujian' }}</h3>
        <p class="text-xs text-slate-500 mt-0.5">
            Mapel: <span class="text-slate-800 font-bold">{{ $jadwal->bankSoal->mapel->nama_mapel ?? '-' }}</span> &bull; 
            Total Peserta: <span class="text-emerald-600 font-bold">{{ count($pesertaList) }} Siswa</span>
        </p>
    </div>

    <!-- Table Peserta -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4 text-center">Status Ujian</th>
                        <th class="py-3 px-4 text-center">Nilai PG</th>
                        <th class="py-3 px-4 text-center">Nilai Esai</th>
                        <th class="py-3 px-4 text-center font-bold text-slate-900">Total Nilai</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pesertaList as $idx => $p)
                        @php
                            $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                            $nilaiPg = (float)($input['pg_nilai'] ?? 0);
                            $nilaiEsai = (float)($input['essai_nilai'] ?? 0);
                            $isKoreksi = !empty($input['dikoreksi']);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $p->siswa->nama ?? 'Siswa' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $p->siswa->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $p->siswa->kelasSiswa->first()?->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($p->status == 2)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @elseif($p->status == 1)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                        Mengerjakan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 text-slate-600">
                                        Belum Mulai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-600">
                                {{ number_format($nilaiPg, 1) }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-rose-600">
                                {{ number_format($nilaiEsai, 1) }}
                                @if($isKoreksi)
                                    <span class="text-[10px] text-emerald-600 block font-sans font-normal">&check; Sudah Dikoreksi</span>
                                @else
                                    <span class="text-[10px] text-amber-600 block font-sans font-normal">Belum Dikoreksi</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-black text-sm text-slate-900">
                                {{ number_format($nilaiPg + $nilaiEsai, 1) }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('guru.koreksi.form', $p->id_cbt_siswa) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white transition">
                                    Buka Koreksi &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                Belum ada siswa yang mengerjakan jadwal ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
