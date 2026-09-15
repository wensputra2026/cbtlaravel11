@extends('layouts.admin')

@section('title', 'Data Guru & Staf')
@section('page_title', 'Master Data Guru & Pengawas CBT')

@section('content')
<div class="space-y-6" x-data="{
    openModalAdd: false,
    addTab: 'manual',
    openModalProfile: false,
    openModalJabatan: false,
    selected: [],
    allSelected: false,
    currentGuru: {},
    profileForm: {
        id: '',
        nama_guru: '',
        nip: '',
        username: '',
        password: '',
        email: '',
        no_hp: '',
        jenis_kelamin: 'L'
    },
    jabatanForm: {
        level: 5,
        kelas_wali: '',
        searchMapel: '',
        selectedMapels: {},
        kelasMapels: {}
    },
    openEditProfile(guru) {
        this.currentGuru = guru;
        this.profileForm = {
            id: guru.id_guru,
            nama_guru: guru.nama_guru || '',
            nip: (guru.nip && guru.nip !== '-') ? guru.nip : '',
            username: guru.username || '',
            password: '',
            email: guru.email || '',
            no_hp: guru.no_hp || '',
            jenis_kelamin: guru.jenis_kelamin || 'L'
        };
        this.openModalProfile = true;
    },
    openEditJabatan(guru) {
        this.currentGuru = guru;
        const jab = guru.jabatan || {};
        this.jabatanForm.level = jab.id_jabatan ? parseInt(jab.id_jabatan) : 5;
        this.jabatanForm.kelas_wali = jab.id_kelas ? String(jab.id_kelas) : '';
        this.jabatanForm.searchMapel = '';
        this.jabatanForm.selectedMapels = {};
        this.jabatanForm.kelasMapels = {};

        const mapels = jab.parsed_mapel_kelas || [];
        mapels.forEach(m => {
            if (m.id_mapel) {
                const mapelId = String(m.id_mapel);
                this.jabatanForm.selectedMapels[mapelId] = true;
                this.jabatanForm.kelasMapels[mapelId] = (m.kelas_mapel || []).map(k => String(k.kelas)).filter(Boolean);
            }
        });

        this.openModalJabatan = true;
    },
    toggleMapel(id) {
        id = String(id);
        if (this.jabatanForm.selectedMapels[id]) {
            delete this.jabatanForm.selectedMapels[id];
            delete this.jabatanForm.kelasMapels[id];
        } else {
            this.jabatanForm.selectedMapels[id] = true;
            this.jabatanForm.kelasMapels[id] = [];
        }
    },
    isMapelSelected(id) {
        return !!this.jabatanForm.selectedMapels[String(id)];
    },
    isClassSelected(mapelId, classId) {
        mapelId = String(mapelId);
        classId = String(classId);
        const list = this.jabatanForm.kelasMapels[mapelId] || [];
        return list.includes(classId);
    },
    toggleClass(mapelId, classId) {
        mapelId = String(mapelId);
        classId = String(classId);
        if (!this.jabatanForm.kelasMapels[mapelId]) {
            this.jabatanForm.kelasMapels[mapelId] = [];
        }
        const index = this.jabatanForm.kelasMapels[mapelId].indexOf(classId);
        if (index > -1) {
            this.jabatanForm.kelasMapels[mapelId].splice(index, 1);
        } else {
            this.jabatanForm.kelasMapels[mapelId].push(classId);
        }
    },
    selectAllClasses(mapelId, allClassIds) {
        mapelId = String(mapelId);
        this.jabatanForm.kelasMapels[mapelId] = [...allClassIds];
    },
    clearAllClasses(mapelId) {
        mapelId = String(mapelId);
        this.jabatanForm.kelasMapels[mapelId] = [];
    },
    toggleSelectAll() {
        this.allSelected = !this.allSelected;
        if (this.allSelected) {
            const checkboxes = document.querySelectorAll('.guru-row-checkbox');
            this.selected = Array.from(checkboxes).map(cb => cb.value);
        } else {
            this.selected = [];
        }
    }
}">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-500/10 dark:bg-brand-500/20 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Master Data Guru & Pengampu</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Tahun Ajaran: <strong class="text-brand-600 dark:text-brand-400">{{ $activeTp->tahun ?? 'Aktif' }}</strong> | Semester: <strong class="text-brand-600 dark:text-brand-400">{{ $activeSmt->nama_smt ?? $activeSmt->smt ?? 'Aktif' }}</strong>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.location.reload()" class="px-3.5 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reload</span>
            </button>
            <button @click="openModalAdd = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-brand-600/30 transition flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Guru / Import</span>
            </button>
        </div>
    </div>



    <!-- Filter & Toolbar Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('admin.master.guru') }}" method="GET" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="flex flex-1 flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama guru, NIP, atau username..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Filter Jabatan -->
                <select name="level_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-brand-500">
                    <option value="">Semua Jabatan</option>
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl->id_level }}" {{ request('level_id') == $lvl->id_level ? 'selected' : '' }}>
                            {{ $lvl->level }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Status Akun -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-brand-500">
                    <option value="">Semua Status Akun</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Filter
                </button>

                @if(request()->anyFilled(['q', 'level_id', 'status']))
                    <a href="{{ route('admin.master.guru') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <!-- Bulk Delete Button -->
                <div x-show="selected.length > 0" x-cloak>
                    <form id="bulkDeleteForm" action="{{ route('admin.master.guru.bulk_destroy') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru terpilih?')">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="checked[]" :value="id">
                        </template>
                        <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus Terpilih (<span x-text="selected.length"></span>)</span>
                        </button>
                    </form>
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400 shrink-0">
                    Menampilkan <strong class="text-slate-800 dark:text-white">{{ $guruList->total() }}</strong> guru
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card (Bordered & Structured like US1) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-3 w-10 text-center border-r border-slate-200 dark:border-slate-800">
                            <input type="checkbox" @click="toggleSelectAll()" class="rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500">
                        </th>
                        <th class="py-3.5 px-3 w-12 text-center border-r border-slate-200 dark:border-slate-800">No.</th>
                        <th class="py-3.5 px-4 min-w-[280px] border-r border-slate-200 dark:border-slate-800">Data Guru</th>
                        <th class="py-3.5 px-4 min-w-[200px] border-r border-slate-200 dark:border-slate-800">Pengampu</th>
                        <th class="py-3.5 px-4 min-w-[220px] border-r border-slate-200 dark:border-slate-800">Mapel Kelas</th>
                        <th class="py-3.5 px-4 w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    @forelse($guruList as $idx => $guru)
                        @php
                            $userActive = $guru->user?->active == 1;
                            $jabatan = $guru->jabatan;
                            $levelName = $jabatan?->level?->level ?? 'Guru';
                            $waliKelas = ($jabatan && $jabatan->id_jabatan == 4 && $jabatan->kelas) ? $jabatan->kelas->nama_kelas : null;
                            $assignedMapels = $jabatan?->parsed_mapel_kelas ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition">
                            <!-- Checkbox -->
                            <td class="py-3.5 px-3 text-center border-r border-slate-200 dark:border-slate-800 align-top">
                                <input type="checkbox" value="{{ $guru->id_guru }}" x-model="selected" class="guru-row-checkbox rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500">
                            </td>

                            <!-- Nomor -->
                            <td class="py-3.5 px-3 text-center font-medium text-slate-400 border-r border-slate-200 dark:border-slate-800 align-top">
                                {{ $guruList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $guruList->firstItem() + $idx : $idx + 1 }}
                            </td>

                            <!-- Data Guru -->
                            <td class="py-3.5 px-4 border-r border-slate-200 dark:border-slate-800 align-top">
                                <div class="flex items-start gap-3">
                                    <!-- Avatar -->
                                    <div class="w-11 h-11 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-sm text-brand-600 dark:text-brand-400">
                                        <img src="{{ $guru->foto_url }}" alt="{{ $guru->nama_guru }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                        <span class="hidden w-full h-full items-center justify-center">{{ strtoupper(substr($guru->nama_guru, 0, 1)) }}</span>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-1">
                                        <div class="text-[11px] font-mono text-slate-400">
                                            NIP: {{ $guru->nip ?: '-' }}
                                        </div>
                                        <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">
                                            {{ $guru->nama_guru }}
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                            <!-- Level / Jabatan Badge -->
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $waliKelas ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                                {{ $levelName }} {{ $waliKelas ? "({$waliKelas})" : '' }}
                                            </span>

                                            <!-- Status Akun Toggle -->
                                            <form action="{{ route('admin.master.guru.toggle_status', $guru->id_guru) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Klik untuk mengubah status akun" class="px-2 py-0.5 text-[10px] font-bold rounded-md transition cursor-pointer flex items-center gap-1 {{ $userActive ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40' }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $userActive ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                    <span>{{ $userActive ? 'Aktif' : 'Nonaktif' }}</span>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 pt-0.5">
                                            <span>User: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $guru->username }}</strong></span>
                                            @if($guru->password)
                                                <span>• Pwd: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $guru->password }}</strong></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Pengampu (Mata Pelajaran) -->
                            <td class="py-3.5 px-4 border-r border-slate-200 dark:border-slate-800 align-top">
                                @if(count($assignedMapels) > 0)
                                    <div class="space-y-2">
                                        @foreach($assignedMapels as $mp)
                                            <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                                <span>{{ $mp['nama_mapel'] ?? '-' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">- Belum ada mapel -</span>
                                @endif
                            </td>

                            <!-- Mapel Kelas (Badges per Subject) -->
                            <td class="py-3.5 px-4 border-r border-slate-200 dark:border-slate-800 align-top">
                                @if(count($assignedMapels) > 0)
                                    <div class="space-y-2">
                                        @foreach($assignedMapels as $mp)
                                            <div class="flex flex-wrap items-center gap-1 min-h-[20px]">
                                                @forelse($mp['kelas_mapel'] ?? [] as $km)
                                                    @if(!empty($km['kelas']))
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                            {{ $kelasMap[$km['kelas']] ?? ('Kls ' . $km['kelas']) }}
                                                        </span>
                                                    @endif
                                                @empty
                                                    <span class="text-[11px] text-slate-400 italic">Semua kelas</span>
                                                @endforelse
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">- Belum ada kelas -</span>
                                @endif
                            </td>

                            <!-- Aksi (Profile, Jabatan, Hapus) -->
                            <td class="py-3.5 px-4 text-center align-top">
                                <div class="flex flex-col gap-1.5 max-w-[140px] mx-auto">
                                    <button 
                                        type="button" 
                                        @click="openEditProfile({{ json_encode($guru) }})" 
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition border border-blue-200/60 dark:border-blue-800/40 text-[11px] font-semibold flex items-center justify-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Profile</span>
                                    </button>

                                    <a 
                                        href="{{ route('admin.master.guru.edit_jabatan', $guru->id_guru) }}"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition border border-emerald-200/60 dark:border-emerald-800/40 text-[11px] font-semibold flex items-center justify-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>Jabatan</span>
                                    </a>

                                    <form action="{{ route('admin.master.guru.destroy', $guru->id_guru) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru {{ $guru->nama_guru }}?')" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-2.5 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition border border-rose-200/60 dark:border-rose-800/40 text-[11px] font-semibold flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-9 h-9 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span class="text-sm font-medium">Belum ada data guru yang sesuai filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guruList instanceof \Illuminate\Pagination\LengthAwarePaginator && $guruList->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $guruList->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TAMBAH GURU MANUAL / IMPORT EXCEL -->
    <!-- ========================================================================= -->
    <div x-show="openModalAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalAdd = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Data Guru / Import</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih metode input manual atau import template excel</p>
                </div>
                <button @click="openModalAdd = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Tabs Header -->
            <div class="flex border-b border-slate-200 dark:border-slate-800 mb-5">
                <button 
                    type="button" 
                    @click="addTab = 'manual'" 
                    class="pb-2.5 px-4 text-xs font-semibold border-b-2 transition" 
                    :class="addTab === 'manual' ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                >
                    Tambah Manual
                </button>
                <button 
                    type="button" 
                    @click="addTab = 'import'" 
                    class="pb-2.5 px-4 text-xs font-semibold border-b-2 transition" 
                    :class="addTab === 'import' ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                >
                    Import File Excel / CSV
                </button>
            </div>

            <!-- Tab 1: Form Manual -->
            <div x-show="addTab === 'manual'">
                <form action="{{ route('admin.master.guru.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap & Gelar *</label>
                        <input type="text" name="nama_guru" placeholder="Contoh: Dra. Hj. Siti Aminah, M.Pd" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIP / NUPTK</label>
                            <input type="text" name="nip" placeholder="198001012005011001" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Username Login *</label>
                            <input type="text" name="username" placeholder="sitiaminah" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password Awal *</label>
                            <input type="text" name="password" placeholder="guru123" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                            <input type="email" name="email" placeholder="guru@sekolah.sch.id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Handphone / WhatsApp</label>
                            <input type="text" name="no_hp" placeholder="081234567890" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div x-data="{ manualLevel: 5 }">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Level Jabatan</label>
                                <select name="id_level" x-model="manualLevel" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                                    @foreach($levels as $lvl)
                                        <option value="{{ $lvl->id_level }}">{{ $lvl->level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="manualLevel == 4" x-cloak>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wali Kelas Untuk</label>
                                <select name="id_kelas" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelasList as $kls)
                                        <option value="{{ $kls->id_kelas }}">{{ $kls->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="openModalAdd = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                            Simpan Guru
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 2: Form Import Excel -->
            <div x-show="addTab === 'import'" x-cloak class="space-y-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl space-y-2">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Petunjuk Import Data Guru
                    </h5>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                        Gunakan file template Excel resmi agar format kolom sesuai. Kolom yang diisi meliputi <strong>Nama Guru</strong>, <strong>NIP</strong>, <strong>Kode Guru</strong>, <strong>Username</strong>, dan <strong>Password</strong> login CBT.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('admin.master.guru.template') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Template (format_guru.xlsx)</span>
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.master.guru.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Berkas Excel / CSV *</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-slate-800 dark:file:text-brand-400 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-950">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="openModalAdd = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span>Mulai Import Guru</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: EDIT PROFILE GURU -->
    <!-- ========================================================================= -->
    <div x-show="openModalProfile" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalProfile = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Profile Guru</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Perbarui profil dan data akun login ' + profileForm.nama_guru"></p>
                </div>
                <button @click="openModalProfile = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('/admin/master/guru') }}/' + profileForm.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap & Gelar *</label>
                    <input type="text" name="nama_guru" x-model="profileForm.nama_guru" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIP / NUPTK</label>
                        <input type="text" name="nip" x-model="profileForm.nip" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="profileForm.jenis_kelamin" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Username Login *</label>
                        <input type="text" name="username" x-model="profileForm.username" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password Baru (Opsional)</label>
                        <input type="text" name="password" placeholder="Kosongkan jika tidak diganti" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input type="email" name="email" x-model="profileForm.email" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Handphone / WA</label>
                        <input type="text" name="no_hp" x-model="profileForm.no_hp" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalProfile = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: EDIT JABATAN & PENUGASAN MAPEL / KELAS (US1 COMPATIBLE) -->
    <!-- ========================================================================= -->
    <div x-show="openModalJabatan" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalJabatan = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-3xl p-6 shadow-2xl my-8 max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4 shrink-0">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Jabatan & Penugasan Mengajar</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Atur level jabatan dan kelas mata pelajaran untuk <strong class="text-slate-800 dark:text-slate-200" x-text="currentGuru.nama_guru"></strong>
                    </p>
                </div>
                <button @click="openModalJabatan = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <form :action="'{{ url('/admin/master/guru') }}/' + currentGuru.id_guru + '/jabatan'" method="POST" class="space-y-5 overflow-y-auto pr-1 flex-1">
                @csrf

                <!-- Section 1: Level & Wali Kelas -->
                <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl space-y-3">
                    <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">1. Level Jabatan</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Jabatan *</label>
                            <select name="level" x-model="jabatanForm.level" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-medium">
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id_level }}">{{ $lvl->level }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="jabatanForm.level == 4" x-cloak>
                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas Bimbingan (Wali Kelas) *</label>
                            <select name="kelas_wali" x-model="jabatanForm.kelas_wali" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-medium">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList as $kls)
                                    <option value="{{ $kls->id_kelas }}">{{ $kls->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Penugasan Mengajar Mata Pelajaran -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">2. Mata Pelajaran Diampu</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Centang mata pelajaran yang diajar, lalu tentukan kelas yang diajar pada tiap mapel</p>
                        </div>
                        <!-- Filter Mapel input -->
                        <div class="w-44">
                            <input type="text" x-model="jabatanForm.searchMapel" placeholder="Cari mapel..." class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    @php
                        $allClassIds = $kelasList->pluck('id_kelas')->map(fn($id) => (string)$id)->toArray();
                    @endphp

                    <!-- Mapel List Grid -->
                    <div class="space-y-2.5 max-h-80 overflow-y-auto border border-slate-200 dark:border-slate-800 rounded-xl p-3 bg-slate-50/50 dark:bg-slate-950/50">
                        @foreach($mapelList as $mp)
                            <div 
                                x-show="!jabatanForm.searchMapel || '{{ strtolower($mp->nama_mapel . ' ' . $mp->kode) }}'.includes(jabatanForm.searchMapel.toLowerCase())"
                                class="p-3 bg-white dark:bg-slate-900 border rounded-xl transition"
                                :class="isMapelSelected('{{ $mp->id_mapel }}') ? 'border-brand-500 shadow-sm' : 'border-slate-200 dark:border-slate-800'"
                            >
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="mapel[]" 
                                            value="{{ $mp->id_mapel }}" 
                                            :checked="isMapelSelected('{{ $mp->id_mapel }}')" 
                                            @change="toggleMapel('{{ $mp->id_mapel }}')"
                                            class="rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 w-4 h-4"
                                        >
                                        <div>
                                            <span class="font-bold text-xs text-slate-800 dark:text-white">{{ $mp->nama_mapel }}</span>
                                            <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded">{{ $mp->kode }}</span>
                                        </div>
                                    </label>

                                    <!-- Convenience Action: Pilih Semua Kelas -->
                                    <div x-show="isMapelSelected('{{ $mp->id_mapel }}')" class="flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            @click="selectAllClasses('{{ $mp->id_mapel }}', {{ json_encode($allClassIds) }})" 
                                            class="text-[10px] font-medium text-brand-600 dark:text-brand-400 hover:underline"
                                        >
                                            Pilih Semua Kelas
                                        </button>
                                        <span class="text-slate-300 dark:text-slate-700">•</span>
                                        <button 
                                            type="button" 
                                            @click="clearAllClasses('{{ $mp->id_mapel }}')" 
                                            class="text-[10px] font-medium text-rose-500 hover:underline"
                                        >
                                            Kosongkan
                                        </button>
                                    </div>
                                </div>

                                <!-- Class Badges Checkbox Selection (appears when mapel is selected) -->
                                <div x-show="isMapelSelected('{{ $mp->id_mapel }}')" x-cloak class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                                    <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-2">Pilih Kelas yang Diajar:</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($kelasList as $kls)
                                            <label 
                                                class="px-2.5 py-1 rounded-lg border text-[11px] font-medium cursor-pointer transition flex items-center gap-1.5"
                                                :class="isClassSelected('{{ $mp->id_mapel }}', '{{ $kls->id_kelas }}') ? 'bg-brand-50 text-brand-700 border-brand-300 dark:bg-brand-950/60 dark:text-brand-300 dark:border-brand-700 font-bold' : 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 hover:bg-slate-100'"
                                            >
                                                <input 
                                                    type="checkbox" 
                                                    :name="'kelasmapel_{{ $mp->id_mapel }}[]'" 
                                                    value="{{ $kls->id_kelas }}"
                                                    :checked="isClassSelected('{{ $mp->id_mapel }}', '{{ $kls->id_kelas }}')"
                                                    @change="toggleClass('{{ $mp->id_mapel }}', '{{ $kls->id_kelas }}')"
                                                    class="hidden"
                                                >
                                                <span>{{ $kls->nama_kelas }}</span>
                                                <span x-show="isClassSelected('{{ $mp->id_mapel }}', '{{ $kls->id_kelas }}')" class="text-brand-600 dark:text-brand-400">✓</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                    <button type="button" @click="openModalJabatan = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Jabatan & Penugasan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
