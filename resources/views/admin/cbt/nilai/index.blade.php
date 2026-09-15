@extends('layouts.admin')

@section('title', 'Rekapitulasi Nilai & Hasil Ujian')
@section('page_title', 'Rekap Nilai & Analisis Hasil Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Jadwal Selector -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Rekapitulasi Nilai Peserta Ujian</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih jadwal ujian untuk melihat skor otomatis PG, koreksi esai, dan ekspor CSV</p>
            </div>
            @if($selectedJadwal)
                <div>
                    <a href="{{ route('admin.cbt.nilai.export', $selectedJadwal->id_jadwal) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Ekspor Nilai (CSV/Excel)</span>
                    </a>
                </div>
            @endif
        </div>

        <form action="{{ route('admin.cbt.nilai.index') }}" method="GET" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-full sm:w-64">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Periode Tahun Ajaran:</label>
                    <select name="tahun_ajaran_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @if(isset($allYears))
                            @foreach($allYears as $y)
                                <option value="{{ $y->id }}" {{ (isset($selectedYear) && $selectedYear->id == $y->id) ? 'selected' : '' }}>
                                    {{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }} {{ $y->is_active ? '★' : '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="w-full sm:w-80">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Paket Jadwal Ujian:</label>
                    <select name="jadwal_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">-- Pilih Jadwal Pelaksanaan Ujian --</option>
                        @foreach($jadwalList as $jl)
                            <option value="{{ $jl->id_jadwal }}" {{ $jadwalId == $jl->id_jadwal ? 'selected' : '' }}>
                                {{ $jl->bankSoal->bank_nama ?? 'Ujian' }} ({{ $jl->bankSoal->mapel->nama_mapel ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    @if(!$selectedJadwal)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl mx-auto mb-3">📊</div>
            <h4 class="font-bold text-slate-800 dark:text-white text-sm">Pilih Jadwal Ujian</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Silakan pilih jadwal pelaksanaan ujian pada dropdown di atas untuk menampilkan daftar nilai siswa.</p>
        </div>
    @else
        <!-- Results Table Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4 gap-3">
                <div>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white">{{ $selectedJadwal->bankSoal->bank_nama ?? 'Hasil Ujian' }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Peserta: <strong class="text-slate-800 dark:text-white">{{ $pesertaList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pesertaList->total() : count($pesertaList) }} Siswa</strong></p>
                </div>
                <form action="{{ route('admin.cbt.nilai.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="jadwal_id" value="{{ $jadwalId }}">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NISN..." class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500 w-44 sm:w-56">
                    <button type="submit" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">Cari</button>
                    @if(request()->filled('q'))
                        <a href="{{ route('admin.cbt.nilai.index', ['jadwal_id' => $jadwalId]) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4 w-12">No</th>
                            <th class="py-3 px-4">Nama Peserta</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Nilai PG</th>
                            <th class="py-3 px-4 text-center">Nilai Esai</th>
                            <th class="py-3 px-4 text-center font-bold text-brand-600 dark:text-brand-300">Nilai Akhir</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($pesertaList as $idx => $p)
                            @php
                                $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                                $nilaiPg = (float)($input['pg_nilai'] ?? 0);
                                $nilaiEsai = (float)($input['essai_nilai'] ?? 0);
                                $nilaiTotal = $nilaiPg + $nilaiEsai;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 text-slate-400">
                                    {{ $pesertaList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pesertaList->firstItem() + $idx : $idx + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $p->siswa->nama ?? 'Peserta' }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $p->siswa->nisn ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
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
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-black text-sm text-brand-600 dark:text-brand-300">
                                    {{ number_format($nilaiTotal, 1) }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.cbt.nilai.jawaban', $p->id_cbt_siswa) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30 hover:bg-brand-600 hover:text-white transition">
                                        Lembar Jawaban & Koreksi &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">Belum ada siswa yang mengerjakan jadwal ujian ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pesertaList instanceof \Illuminate\Pagination\LengthAwarePaginator && $pesertaList->hasPages())
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                    {{ $pesertaList->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
