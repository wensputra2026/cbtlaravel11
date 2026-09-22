@extends('layouts.guru')

@section('title', 'Koreksi Soal Esai')
@section('page_title', 'Pemeriksaan & Koreksi Jawaban Esai')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-xs">
        <h3 class="text-base font-black text-slate-900">Daftar Jadwal Ujian yang Memerlukan Koreksi</h3>
        <p class="text-xs text-slate-500 mt-0.5">Jadwal ujian dari paket bank soal yang Anda susun dan memiliki butir soal esai / uraian</p>
    </div>

    <!-- Table Jadwal Koreksi -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Ujian & Bank Soal</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4 text-center">Butir Esai</th>
                        <th class="py-3 px-4 text-center">Status Jadwal</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($jadwalList as $idx => $j)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $j->bankSoal->bank_nama ?? 'Ujian' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $j->bankSoal->bank_kode ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $j->bankSoal->mapel->nama_mapel ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $j->bankSoal->tampil_esai ?? 0 }} Butir Esai
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($j->status)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 text-slate-600">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('guru.koreksi.peserta', $j->id_jadwal) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition inline-flex items-center gap-1.5">
                                    <span>Periksa Lembar Jawaban &rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Tidak ada jadwal ujian aktif dari bank soal Anda saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
