@extends('layouts.guru')

@section('title', 'Rekapitulasi Hasil Ujian')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-wide">Hasil & Rekapitulasi Nilai</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perolehan skor nilai ujian siswa untuk paket bank soal yang Anda kelola.</p>
        </div>
        @if($selectedJadwal)
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.hasil.export', $selectedJadwal->id_jadwal) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-md shadow-emerald-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ekspor Nilai (CSV/Excel)</span>
                </a>
                <a href="{{ route('guru.koreksi.peserta', $selectedJadwal->id_jadwal) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-md shadow-amber-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Koreksi Esai</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Filter Jadwal Ujian -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <form action="{{ route('guru.hasil.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <label for="jadwal_id" class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider shrink-0">Pilih Jadwal Ujian:</label>
            <select name="jadwal_id" id="jadwal_id" onchange="this.form.submit()" class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                <option value="">-- Silakan Pilih Jadwal Ujian --</option>
                @foreach($jadwalList as $j)
                    <option value="{{ $j->id_jadwal }}" {{ $jadwalId == $j->id_jadwal ? 'selected' : '' }}>
                        {{ $j->bankSoal?->mapel?->nama_mapel ?? 'Mapel' }} &bull; {{ $j->bankSoal?->bank_nama ?? 'Bank Soal' }} ({{ $j->tgl_mulai ? date('d/m/Y', strtotime($j->tgl_mulai)) : '-' }})
                    </option>
                @endforeach
            </select>
            <noscript>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">Tampilkan</button>
            </noscript>
        </form>
    </div>

    @if($selectedJadwal)
        <!-- Statistik Nilai -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Peserta</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nilai Tertinggi</div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['tertinggi'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nilai Terendah</div>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $stats['terendah'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-Rata</div>
                <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ $stats['rata_rata'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tuntas (&ge; 75)</div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['tuntas'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Belum Tuntas</div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $stats['belum_tuntas'] }}</div>
            </div>
        </div>

        <!-- Tabel Perolehan Nilai -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Daftar Nilai Siswa ({{ $pesertaList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pesertaList->total() : count($pesertaList) }} Siswa)
                </h2>
                
                <form action="{{ route('guru.hasil.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="jadwal_id" value="{{ $jadwalId }}">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari siswa / NISN..." class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 w-44 sm:w-56">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">Cari</button>
                    @if(request()->filled('q'))
                        <a href="{{ route('guru.hasil.index', ['jadwal_id' => $jadwalId]) }}" class="px-2 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">Reset</a>
                    @endif
                </form>
            </div>

            @if(empty($pesertaList) || (is_countable($pesertaList) && count($pesertaList) === 0))
                <div class="text-center py-10 text-slate-400 text-xs">
                    Belum ada rekaman jawaban atau peserta yang mengikuti jadwal ujian ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3 px-3">No</th>
                                <th class="py-3 px-3">No Peserta</th>
                                <th class="py-3 px-3">Nama Siswa</th>
                                <th class="py-3 px-3">Kelas</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-center">Waktu Selesai</th>
                                <th class="py-3 px-3 text-right">Skor PG</th>
                                <th class="py-3 px-3 text-right">Skor Esai</th>
                                <th class="py-3 px-3 text-right font-black text-slate-900 dark:text-white">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium text-slate-600 dark:text-slate-300">
                            @foreach($pesertaList as $idx => $p)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-3 text-slate-400">
                                        {{ $pesertaList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pesertaList->firstItem() + $idx : $idx + 1 }}
                                    </td>
                                    <td class="py-3 px-3 font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                        {{ $p->siswa?->nomorPeserta?->nomor_peserta ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-slate-800 dark:text-white">
                                        {{ $p->siswa?->nama ?? '-' }}
                                        <div class="text-[10px] text-slate-400 font-mono">NISN: {{ $p->siswa?->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            {{ $p->siswa?->kelasSiswa?->first()?->kelas?->nama_kelas ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if($p->status == 2)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">Selesai</span>
                                        @elseif($p->status == 1)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">Mengerjakan</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">Belum</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center text-slate-500 dark:text-slate-400">
                                        {{ $p->selesai ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono">{{ $p->nilai_pg }}</td>
                                    <td class="py-3 px-3 text-right font-mono">{{ $p->nilai_esai }}</td>
                                    <td class="py-3 px-3 text-right">
                                        <span class="font-mono font-black text-sm {{ $p->skor_total >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $p->skor_total }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($pesertaList instanceof \Illuminate\Pagination\LengthAwarePaginator && $pesertaList->hasPages())
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                        {{ $pesertaList->links() }}
                    </div>
                @endif
            @endif
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-400 shadow-sm">
            <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Pilih salah satu jadwal ujian di atas untuk menampilkan rekapitulasi nilai dan statistik siswa.</p>
        </div>
    @endif
</div>
@endsection
