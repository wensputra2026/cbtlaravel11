@extends('layouts.guru')

@section('title', 'Analisis Butir Soal')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-wide">Analisis Butir Soal</h1>
            <p class="text-xs text-slate-500 mt-1">Tingkat kesukaran, daya pembeda, dan statistik butir soal per paket ujian.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.hasil.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-2 border border-slate-200">
                &larr; Rekap Nilai
            </a>
        </div>
    </div>

    <!-- Filter Jadwal Ujian -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <form action="{{ route('guru.analisis.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <label for="jadwal_id" class="text-xs font-bold text-slate-700 uppercase tracking-wider shrink-0">Pilih Paket Ujian:</label>
            <select name="jadwal_id" id="jadwal_id" onchange="this.form.submit()" class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-emerald-500">
                <option value="">-- Pilih Paket Ujian untuk Dianalisis --</option>
                @foreach($jadwalList as $j)
                    <option value="{{ $j->id_jadwal }}" {{ $jadwalId == $j->id_jadwal ? 'selected' : '' }}>
                        {{ $j->bankSoal?->mapel?->nama_mapel ?? 'Mapel' }} &bull; {{ $j->bankSoal?->bank_nama ?? 'Bank Soal' }} ({{ $j->tgl_mulai ? date('d/m/Y', strtotime($j->tgl_mulai)) : '-' }})
                    </option>
                @endforeach
            </select>
            <noscript>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">Analisis</button>
            </noscript>
        </form>
    </div>

    @if($selectedJadwal)
        <!-- Ringkasan Statistik -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Responden</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $rekapStat['total_peserta'] }} Siswa</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nilai Rata-Rata</div>
                <div class="text-2xl font-black text-sky-600 mt-1">{{ $rekapStat['nilai_rata'] }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tuntas KKM (&ge; 75)</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $rekapStat['tuntas'] }} Siswa</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Belum Tuntas</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $rekapStat['belum_tuntas'] }} Siswa</div>
            </div>
        </div>

        <!-- Tabel Analisis Butir Soal -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Sebaran Tingkat Kesukaran Butir Soal
            </h2>

            @if(empty($analisisSoal) || count($analisisSoal) === 0)
                <div class="text-center py-10 text-slate-400 text-xs">
                    Belum ada butir soal atau rekaman respons siswa untuk dianalisis.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                                <th class="py-3 px-3 text-center">No</th>
                                <th class="py-3 px-3">Tipe Soal</th>
                                <th class="py-3 px-3 text-center">Kunci</th>
                                <th class="py-3 px-3 text-center">Jml Penjawab</th>
                                <th class="py-3 px-3 text-center">Jml Benar</th>
                                <th class="py-3 px-3 text-center">Daya Serap (%)</th>
                                <th class="py-3 px-3 text-center">Tingkat Kesukaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($analisisSoal as $item)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-3 text-center font-bold text-slate-900">{{ $item['nomor'] }}</td>
                                    <td class="py-3 px-3">
                                        @if($item['jenis'] == 1)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Pilihan Ganda</span>
                                        @elseif($item['jenis'] == 2)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">PG Kompleks</span>
                                        @elseif($item['jenis'] == 3)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menjodohkan</span>
                                        @elseif($item['jenis'] == 4)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Isian Singkat</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Esai / Uraian</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono font-bold text-amber-700">
                                        {{ is_string($item['kunci']) ? substr($item['kunci'], 0, 15) : '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono">{{ $item['peserta'] }}</td>
                                    <td class="py-3 px-3 text-center font-mono text-emerald-600 font-bold">{{ $item['benar'] }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $item['persen_benar'] }}%"></div>
                                            </div>
                                            <span class="font-mono text-[11px]">{{ $item['persen_benar'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if($item['tingkat'] === 'Mudah')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Mudah</span>
                                        @elseif($item['tingkat'] === 'Sedang')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Sedang</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Sukar</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500 shadow-sm">
            <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            <p class="text-sm font-semibold text-slate-700">Pilih salah satu jadwal ujian di atas untuk melihat analisis butir soal.</p>
        </div>
    @endif
</div>
@endsection
