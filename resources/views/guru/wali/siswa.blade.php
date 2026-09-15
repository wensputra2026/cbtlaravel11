@extends('layouts.guru')

@section('title', 'Data Siswa Kelas ' . ($waliKelas->nama_kelas ?? ''))
@section('page_title', 'Siswa Kelas ' . ($waliKelas->nama_kelas ?? ''))

@section('content')
<div class="space-y-6">

    <!-- Header Banner & Info Card -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/20 text-white backdrop-blur-sm border border-white/20">
                        WALI KELAS RESMI
                    </span>
                    <span class="text-xs text-emerald-100 font-medium">
                        TP {{ $assignment['active_tp']->tahun ?? '-' }} • Smt {{ $assignment['active_smt']->smt ?? '-' }}
                    </span>
                </div>
                <h2 class="text-2xl font-black tracking-tight">Kelas {{ $waliKelas->nama_kelas }}</h2>
                <p class="text-sm text-emerald-100 mt-1">
                    Daftar seluruh siswa terdaftar aktif pada kelas bimbingan Anda.
                </p>
            </div>

            <!-- Stats Ringkas -->
            <div class="flex items-center gap-3">
                <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-xl px-4 py-2 text-center">
                    <div class="text-2xl font-black text-white">{{ $stats['total'] }}</div>
                    <div class="text-[10px] uppercase tracking-wider text-emerald-100 font-semibold">Total Siswa</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-xl px-4 py-2 text-center">
                    <div class="text-2xl font-black text-sky-200">{{ $stats['laki'] }}</div>
                    <div class="text-[10px] uppercase tracking-wider text-emerald-100 font-semibold">Laki-laki</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-xl px-4 py-2 text-center">
                    <div class="text-2xl font-black text-pink-200">{{ $stats['perempuan'] }}</div>
                    <div class="text-[10px] uppercase tracking-wider text-emerald-100 font-semibold">Perempuan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('guru.wali.siswa') }}" class="w-full md:w-auto flex-1 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ request('q') }}" 
                    placeholder="Cari nama, NIS, atau NISN siswa..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-slate-900 transition"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select 
                name="gender" 
                onchange="this.form.submit()"
                class="px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                <option value="">Semua Gender</option>
                <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki ({{ $stats['laki'] }})</option>
                <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan ({{ $stats['perempuan'] }})</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                Cari
            </button>

            @if(request()->hasAny(['q', 'gender', 'agama']))
                <a href="{{ route('guru.wali.siswa') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition">
                    Reset
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('guru.wali.struktur') }}" class="px-3 py-2 border border-emerald-600 text-emerald-600 dark:border-emerald-500 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Struktur Kelas</span>
            </a>
            <a href="{{ route('guru.wali.catatan') }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Catatan Bimbingan</span>
            </a>
        </div>
    </div>

    <!-- Student Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden" x-data="{ selectedSiswa: null }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nama Siswa</th>
                        <th class="py-3.5 px-4">NIS / NISN</th>
                        <th class="py-3.5 px-4 text-center">L/P</th>
                        <th class="py-3.5 px-4">Agama</th>
                        <th class="py-3.5 px-4">Tempat & Tanggal Lahir</th>
                        <th class="py-3.5 px-4">Nama Orang Tua</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($siswas as $idx => $s)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-extrabold flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800 text-xs">
                                        {{ strtoupper(substr($s->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $s->nama }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $s->username ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                <div><span class="text-[10px] text-slate-400">NIS:</span> {{ $s->nis ?? '-' }}</div>
                                <div><span class="text-[10px] text-slate-400">NISN:</span> {{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($s->jenis_kelamin === 'L')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300">L</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300">P</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $s->agama ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                <div>{{ $s->tempat_lahir ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $s->tanggal_lahir ? date('d-m-Y', strtotime($s->tanggal_lahir)) : '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                <div><span class="text-[10px] text-slate-400">Ayah:</span> {{ $s->nama_ayah ?? '-' }}</div>
                                <div><span class="text-[10px] text-slate-400">Ibu:</span> {{ $s->nama_ibu ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button 
                                    type="button" 
                                    @click="selectedSiswa = {{ json_encode($s) }}"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition"
                                    title="Lihat Profil Lengkap"
                                >
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="text-sm font-semibold">Tidak ada data siswa ditemukan.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Pastikan siswa sudah dialokasikan ke kelas ini pada semester aktif.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Detail Modal Pop-up -->
        <div 
            x-show="selectedSiswa" 
            x-cloak 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="selectedSiswa = null"
        >
            <div 
                @click.away="selectedSiswa = null"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150"
            >
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil Siswa Bimbingan
                    </h3>
                    <button type="button" @click="selectedSiswa = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg">&times;</button>
                </div>

                <div class="p-6 space-y-4" x-if="selectedSiswa">
                    <div class="flex items-center gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white font-black text-2xl flex items-center justify-center shadow-md">
                            <span x-text="selectedSiswa.nama ? selectedSiswa.nama.charAt(0).toUpperCase() : 'S'"></span>
                        </div>
                        <div>
                            <h4 class="text-base font-black text-slate-900 dark:text-white" x-text="selectedSiswa.nama"></h4>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                                <span>Kelas: <strong class="text-emerald-600">{{ $waliKelas->nama_kelas }}</strong></span>
                                <span>•</span>
                                <span x-text="selectedSiswa.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">NIS</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="selectedSiswa.nis || '-'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">NISN</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="selectedSiswa.nisn || '-'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Agama</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedSiswa.agama || '-'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">No. Handphone</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="selectedSiswa.hp || '-'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 col-span-2">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Tempat & Tanggal Lahir</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedSiswa.tempat_lahir || '-') + ', ' + (selectedSiswa.tanggal_lahir || '-')"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Ayah</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedSiswa.nama_ayah || '-'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Ibu</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedSiswa.nama_ibu || '-'"></span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="button" @click="selectedSiswa = null" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-xl text-xs font-bold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
