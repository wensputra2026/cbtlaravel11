@extends('layouts.admin')

@section('title', 'Tahun Pelajaran & Semester')
@section('page_title', 'Tahun Pelajaran & Semester Aktif')

@section('content')
<div class="space-y-6" x-data="{ openModalTp: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Konfigurasi Periode Akademik</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pengaturan tahun pelajaran dan semester aktif yang menjadi rujukan ujian CBT saat ini</p>
        </div>
        <div>
            <button @click="openModalTp = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Tahun Pelajaran</span>
            </button>
        </div>
    </div>

    <!-- Grid: Tahun Pelajaran (Left) & Semester (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. Tahun Pelajaran -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-400">📅</span>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Tahun Pelajaran</h4>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($tpList) }} Data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">No</th>
                            <th class="py-2.5 px-3">Tahun Pelajaran</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($tpList as $idx => $tp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-3 font-bold {{ $tp->active ? 'text-brand-600 dark:text-brand-300' : 'text-slate-800 dark:text-white' }}">
                                    {{ $tp->tahun }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($tp->active)
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                            AKTIF
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if(!$tp->active)
                                        <form action="{{ route('admin.master.tp.set_active', $tp->id_tp) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-[11px] font-semibold rounded-lg bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30 hover:bg-brand-600 hover:text-white transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">&check; Sedang Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data Tahun Pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Semester -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400">⏱️</span>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Semester Berjalan</h4>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400">Ganjil / Genap</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">No</th>
                            <th class="py-2.5 px-3">Nama Semester</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($smtList as $idx => $smt)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-3 font-bold {{ $smt->active ? 'text-amber-600 dark:text-amber-300' : 'text-slate-800 dark:text-white' }}">
                                    {{ $smt->nama_smt ?? ($smt->smt == 1 ? 'Semester Ganjil' : 'Semester Genap') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($smt->active)
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                            AKTIF
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if(!$smt->active)
                                        <form action="{{ route('admin.master.smt.set_active', $smt->id_smt) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-[11px] font-semibold rounded-lg bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 hover:bg-amber-500 hover:text-white transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">&check; Sedang Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data Semester.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Tambah TP -->
    <div x-show="openModalTp" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalTp = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tambah Tahun Pelajaran</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Contoh format penulisan: 2024/2025 atau 2025/2026</p>
            
            <form action="{{ route('admin.master.tp.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahun Pelajaran</label>
                    <input type="text" name="tahun" placeholder="2025/2026" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalTp = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
