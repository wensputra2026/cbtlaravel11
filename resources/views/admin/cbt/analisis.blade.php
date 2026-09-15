@extends('layouts.admin')

@section('title', 'Analisis Butir Soal')
@section('page_title', 'Analisis Butir Soal & Daya Pembeda CBT')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Selector -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white">Analisis Kualitas Butir Soal</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tingkat kesukaran, persentase jawaban benar, dan statistik sebaran nilai peserta</p>
            </div>
        </div>

        <form action="{{ route('admin.cbt.analisis') }}" method="GET" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <select name="jadwal_id" onchange="this.form.submit()" class="w-full sm:w-96 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                <option value="">-- Pilih Jadwal Pelaksanaan Ujian --</option>
                @foreach($jadwalList as $jl)
                    <option value="{{ $jl->id_jadwal }}" {{ $jadwalId == $jl->id_jadwal ? 'selected' : '' }}>
                        {{ $jl->bankSoal->bank_nama ?? 'Ujian' }} ({{ $jl->bankSoal->mapel->nama_mapel ?? '-' }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if(!$selectedJadwal)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl mx-auto mb-3">📈</div>
            <h4 class="font-bold text-slate-800 dark:text-white text-sm">Pilih Jadwal Ujian</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Pilih jadwal pelaksanaan ujian pada dropdown di atas untuk menampilkan analisis daya pembeda butir soal.</p>
        </div>
    @else
        <!-- Stats Summary Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Rata-rata Nilai</span>
                <div class="text-2xl font-black text-brand-600 dark:text-brand-400">{{ $rekapStat['nilai_rata'] }}</div>
                <div class="text-[11px] text-slate-400 mt-1">KKM Standar: 75</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Nilai Tertinggi</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $rekapStat['nilai_tertinggi'] }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Skor Maksimal</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Nilai Terendah</span>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $rekapStat['nilai_terendah'] }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Skor Minimal</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Ketuntasan (≥ 75)</span>
                <div class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $rekapStat['tuntas'] }} / {{ $rekapStat['total_peserta'] }}</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ $rekapStat['total_peserta'] > 0 ? round(($rekapStat['tuntas'] / $rekapStat['total_peserta']) * 100) : 0 }}% Tuntas</div>
            </div>
        </div>

        <!-- Table Analisis Butir Soal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <h4 class="font-bold text-sm text-slate-800 dark:text-white mb-4">Tabel Tingkat Kesukaran Butir Soal</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4 w-12">No</th>
                            <th class="py-3 px-4">Tipe Soal</th>
                            <th class="py-3 px-4">Kunci Jawaban</th>
                            <th class="py-3 px-4 text-center">Menjawab</th>
                            <th class="py-3 px-4 text-center">Benar</th>
                            <th class="py-3 px-4 text-center">% Benar</th>
                            <th class="py-3 px-4 text-center">Kategori Kesukaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($analisisSoal as $soal)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">#{{ $soal['nomor'] }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $soal['jenis'] == 1 ? 'Pilihan Ganda' : 'Esai/Lainnya' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">{{ $soal['kunci'] }}</td>
                                <td class="py-3.5 px-4 text-center">{{ $soal['peserta'] }} Siswa</td>
                                <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">{{ $soal['benar'] }}</td>
                                <td class="py-3.5 px-4 text-center font-bold">{{ $soal['persen_benar'] }}%</td>
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $badge = match($soal['tingkat']) {
                                            'Mudah' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30',
                                            'Sedang' => 'bg-amber-500/10 text-amber-600 border-amber-500/30',
                                            default => 'bg-rose-500/10 text-rose-600 border-rose-500/30',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full border {{ $badge }}">
                                        {{ $soal['tingkat'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada butir soal pada bank soal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
