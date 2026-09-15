@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page_title', 'Master Data Mata Pelajaran')

@section('content')
<div class="space-y-4" x-data="{
    // Toggle Kelompok Section Visibility
    showKelompok: true,

    // Modal States
    openModalKelompok: false,
    openModalEditKelompok: false,
    openModalSubKelompok: false,
    openModalEditSubKelompok: false,
    openModalAddMapel: false,
    openModalEditMapel: false,
    openModalImport: false,

    // Form Edit States
    editKelompokData: { id: '', kode: '', nama: '', kategori: 'WAJIB' },
    editSubKelompokData: { id: '', kode: '', nama: '', id_parent: '', kategori: '' },
    editMapelData: { id: '', nama_mapel: '', kode: '', kelompok: '', mapel_agama: '0', status: '1', urutan_tampil: '' },

    // Selection State for Bulk Delete
    selectedMapel: [],
    selectAll: false,

    toggleSelectAll(allIds) {
        if (this.selectAll) {
            this.selectedMapel = [...allIds];
        } else {
            this.selectedMapel = [];
        }
    },
    updateSelectAll(totalCount) {
        this.selectAll = this.selectedMapel.length === totalCount && totalCount > 0;
    },

    openEditKelompok(item) {
        this.editKelompokData = {
            id: item.id_kel_mapel,
            kode: item.kode_kel_mapel,
            nama: item.nama_kel_mapel,
            kategori: item.kategori || 'WAJIB'
        };
        this.openModalEditKelompok = true;
    },

    openEditSubKelompok(item) {
        this.editSubKelompokData = {
            id: item.id_kel_mapel,
            kode: item.kode_kel_mapel,
            nama: item.nama_kel_mapel,
            id_parent: item.id_parent,
            kategori: item.kategori || ''
        };
        this.openModalEditSubKelompok = true;
    },

    openEditMapel(item) {
        this.editMapelData = {
            id: item.id_mapel,
            nama_mapel: item.nama_mapel,
            kode: item.kode,
            kelompok: item.kelompok || '-',
            mapel_agama: item.mapel_agama ? String(item.mapel_agama) : '0',
            status: item.status !== undefined ? String(item.status) : '1',
            urutan_tampil: item.urutan_tampil !== null ? item.urutan_tampil : ''
        };
        this.openModalEditMapel = true;
    }
}">

    <!-- Page Header Toolbar (Clean, Unified, No Duplication) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-500/10 dark:bg-brand-500/20 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Master Mata Pelajaran & Kelompok</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pengaturan kelompok kurikulum, kode mapel CBT, dan nomor urut cetak rapor</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.location.reload()" class="px-3 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5" title="Muat ulang halaman">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reload</span>
            </button>

            <button @click="openModalAddMapel = true" type="button" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-brand-600/30 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Mapel</span>
            </button>

            <button @click="openModalImport = true" type="button" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 1: KELOMPOK UTAMA & SUB KELOMPOK (RAPI & SEIMBANG)                -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <!-- Collapsible Bar Header -->
        <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between cursor-pointer select-none" @click="showKelompok = !showKelompok">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400 transition-transform duration-200" :class="{ 'rotate-90': showKelompok }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Kelompok Mata Pelajaran (Kurikulum)</h4>
                <div class="flex items-center gap-1.5 ml-2">
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-200 dark:border-brand-800/60">
                        {{ $kelompokUtama->count() }} Utama
                    </span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60">
                        {{ $subKelompok->count() }} Sub Kelompok
                    </span>
                </div>
            </div>
            <button type="button" class="text-[11px] font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
                <span x-text="showKelompok ? 'Sembunyikan Panel' : 'Tampilkan Panel'"></span>
            </button>
        </div>

        <!-- Collapsible Content -->
        <div x-show="showKelompok" x-collapse class="p-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                
                <!-- Kolom Kiri: Kelompok Utama -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden flex flex-col bg-slate-50/30 dark:bg-slate-950/30">
                    <div class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span class="text-xs font-bold text-slate-800 dark:text-white">Kelompok Utama</span>
                            <span class="text-[10px] text-slate-400">({{ $kelompokUtama->count() }})</span>
                        </div>
                        <button 
                            @click.stop="openModalKelompok = true" 
                            type="button" 
                            class="px-2 py-1 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-[11px] font-semibold transition flex items-center gap-1"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase">
                                <tr>
                                    <th class="py-2.5 px-3 text-center w-32 whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Kategori</th>
                                    <th class="py-2.5 px-3 text-center w-16 whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Kode</th>
                                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-800">Nama Kelompok</th>
                                    <th class="py-2.5 px-3 text-center w-24 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300">
                                @forelse($kelompokUtama as $ku)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap border-r border-slate-200 dark:border-slate-800">
                                            <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-bold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                {{ $ku->kategori }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono font-bold text-brand-600 dark:text-brand-400 border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                            {{ $ku->kode_kel_mapel }}
                                        </td>
                                        <td class="py-2.5 px-3 font-medium text-slate-800 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                            {{ $ku->nama_kel_mapel }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap w-24">
                                            <form id="del-ku-{{ $ku->id_kel_mapel }}" action="{{ route('admin.master.mapel.kelompok.destroy', $ku->id_kel_mapel) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <div class="inline-flex items-center justify-center gap-1.5">
                                                <button 
                                                    @click="openEditKelompok({{ json_encode($ku) }})" 
                                                    type="button" 
                                                    class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 transition shadow-xs hover:shadow-sm" 
                                                    title="Edit Kelompok"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button 
                                                    type="button" 
                                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus Kelompok Utama ini?')) document.getElementById('del-ku-{{ $ku->id_kel_mapel }}').submit();"
                                                    class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 transition shadow-xs hover:shadow-sm" 
                                                    title="Hapus Kelompok"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-400 text-xs">
                                            Belum ada data Kelompok Utama.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Kolom Kanan: Sub Kelompok -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden flex flex-col bg-slate-50/30 dark:bg-slate-950/30">
                    <div class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-bold text-slate-800 dark:text-white">Sub Kelompok</span>
                            <span class="text-[10px] text-slate-400">({{ $subKelompok->count() }})</span>
                        </div>
                        <button 
                            @click.stop="openModalSubKelompok = true" 
                            type="button" 
                            class="px-2 py-1 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-[11px] font-semibold transition flex items-center gap-1"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase">
                                <tr>
                                    <th class="py-2.5 px-3 text-center w-16 whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Kode</th>
                                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-800">Nama Sub Kelompok</th>
                                    <th class="py-2.5 px-3 text-center w-32 whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Kel. Utama</th>
                                    <th class="py-2.5 px-3 text-center w-24 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300">
                                @forelse($subKelompok as $sk)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-600 dark:text-blue-400 border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                            {{ $sk->kode_kel_mapel }}
                                        </td>
                                        <td class="py-2.5 px-3 font-medium text-slate-800 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                            {{ $sk->nama_kel_mapel }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-bold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                {{ $sk->parent?->nama_kel_mapel ?? ('Induk #' . $sk->id_parent) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap w-24">
                                            <form id="del-sk-{{ $sk->id_kel_mapel }}" action="{{ route('admin.master.mapel.kelompok.destroy', $sk->id_kel_mapel) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <div class="inline-flex items-center justify-center gap-1.5">
                                                <button 
                                                    @click="openEditSubKelompok({{ json_encode($sk) }})" 
                                                    type="button" 
                                                    class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 transition shadow-xs hover:shadow-sm" 
                                                    title="Edit Sub Kelompok"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button 
                                                    type="button" 
                                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus Sub Kelompok ini?')) document.getElementById('del-sk-{{ $sk->id_kel_mapel }}').submit();"
                                                    class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 transition shadow-xs hover:shadow-sm" 
                                                    title="Hapus Sub Kelompok"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-400 text-xs">
                                            Belum ada data Sub Kelompok.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 2: DAFTAR MATA PELAJARAN (UTUH, RAPI, BERSIH)                    -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Toolbar & Filter Terpadu -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 space-y-3">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <!-- Sisi Kiri: Judul Card & Tombol Bulk Delete -->
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Daftar Mata Pelajaran</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $mapelList->count() }}
                        </span>
                    </h3>

                    <!-- Bulk Delete Button (muncul jika ada yang dipilih) -->
                    <div x-show="selectedMapel.length > 0" x-cloak>
                        <form action="{{ route('admin.master.mapel.bulk_destroy') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran yang dipilih?')">
                            @csrf
                            <template x-for="id in selectedMapel" :key="id">
                                <input type="hidden" name="checked[]" :value="id">
                            </template>
                            <button type="submit" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus Terpilih (<span x-text="selectedMapel.length"></span>)</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sisi Kanan: Form Filter & Pencarian -->
                <form action="{{ route('admin.master.mapel') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="relative min-w-[170px]">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode..." class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="kelompok" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="all">Semua Kelompok</option>
                        @foreach($allKelompok as $ak)
                            <option value="{{ $ak->kode_kel_mapel }}" {{ request('kelompok') == $ak->kode_kel_mapel ? 'selected' : '' }}>
                                [{{ $ak->kode_kel_mapel }}] {{ $ak->nama_kel_mapel }}
                            </option>
                        @endforeach
                        <option value="-" {{ request('kelompok') == '-' ? 'selected' : '' }}>Belum Dikelompokkan (-)</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="all">Semua Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <button type="submit" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">Cari</button>

                    @if(request()->hasAny(['q', 'kelompok', 'status']))
                        <a href="{{ route('admin.master.mapel') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition" title="Reset filter">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Slim Alert Info -->
            <div class="px-3 py-2 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-300/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong>Nomor Urut Rapor</strong> dan <strong>Kelompok</strong> diperlukan jika ingin mencetak nilai pada rapor siswa.</span>
            </div>
        </div>

        <!-- Tabel Data Mata Pelajaran dengan Proporsi Kolom Presisi -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center border-r border-slate-200 dark:border-slate-800">
                            <input 
                                type="checkbox" 
                                x-model="selectAll" 
                                @change="toggleSelectAll({{ json_encode($mapelList->pluck('id_mapel')->toArray()) }})"
                                class="rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 w-3.5 h-3.5"
                            >
                        </th>
                        <th class="py-3 px-3 text-center w-28 whitespace-nowrap border-r border-slate-200 dark:border-slate-800">No. Urut Rapor</th>
                        <th class="py-3 px-4 min-w-[200px] border-r border-slate-200 dark:border-slate-800">Mata Pelajaran</th>
                        <th class="py-3 px-4 w-32 text-center whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Kode Mapel</th>
                        <th class="py-3 px-4 w-36 text-center whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Kelompok</th>
                        <th class="py-3 px-4 w-28 text-center whitespace-nowrap border-r border-slate-200 dark:border-slate-800">Status</th>
                        <th class="py-3 px-4 w-28 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @php
                        $groupedMapel = $mapelList->groupBy('kelompok');
                    @endphp

                    @forelse($groupedMapel as $groupCode => $items)
                        @php
                            $kelObj = $allKelompok->firstWhere('kode_kel_mapel', $groupCode);
                            $groupName = $kelObj ? "[{$groupCode}] {$kelObj->nama_kel_mapel} ({$kelObj->kategori})" : ($groupCode === '-' ? 'Belum Dikelompokkan (-)' : "[{$groupCode}] Kelompok {$groupCode}");
                        @endphp
                        
                        <!-- Header Baris Kelompok (Group Separator) -->
                        <tr class="bg-slate-100/90 dark:bg-slate-800/90 font-bold text-slate-800 dark:text-slate-100 border-y border-slate-200 dark:border-slate-700">
                            <td colspan="7" class="py-2.5 px-4 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                        <span>{{ $groupName }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                                        {{ $items->count() }} Mata Pelajaran
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <!-- Item Mata Pelajaran dalam Kelompok Ini -->
                        @foreach($items as $mapel)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <!-- Checkbox -->
                                <td class="py-3 px-3 text-center border-r border-slate-200 dark:border-slate-800">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $mapel->id_mapel }}" 
                                        x-model="selectedMapel" 
                                        @change="updateSelectAll({{ $mapelList->count() }})"
                                        class="rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 w-3.5 h-3.5"
                                    >
                                </td>

                                <!-- No. Urut Rapor -->
                                <td class="py-3 px-3 text-center border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $mapel->urutan_tampil ?? '-' }}
                                    </span>
                                </td>

                                <!-- Nama Mapel -->
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $mapel->nama_mapel }}</span>
                                        @if($mapel->mapel_agama == 1 || $mapel->mapel_agama == '1')
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 shrink-0">
                                                Agama
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Kode Mapel -->
                                <td class="py-3 px-4 text-center border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                    <span class="font-mono font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 px-2.5 py-1 rounded border border-brand-200/80 dark:border-brand-800/40 text-[11px]">
                                        {{ $mapel->kode }}
                                    </span>
                                </td>

                                <!-- Kelompok -->
                                <td class="py-3 px-4 text-center border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold {{ $mapel->kelompok && $mapel->kelompok !== '-' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' }}">
                                        {{ $mapel->kelompok ?: '-' }}
                                    </span>
                                </td>

                                <!-- Status (Toggleable via click) -->
                                <td class="py-3 px-4 text-center border-r border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                    <form action="{{ route('admin.master.mapel.toggle_status', $mapel->id_mapel) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Klik untuk mengubah status mapel" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold transition cursor-pointer flex items-center gap-1.5 mx-auto {{ $mapel->status == 1 ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $mapel->status == 1 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            <span>{{ $mapel->status == 1 ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4 text-center whitespace-nowrap w-28">
                                    <form id="del-mapel-{{ $mapel->id_mapel }}" action="{{ route('admin.master.mapel.destroy', $mapel->id_mapel) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <button 
                                            @click="openEditMapel({{ json_encode($mapel) }})" 
                                            type="button" 
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 transition shadow-xs hover:shadow-sm" 
                                            title="Edit Mapel"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus mata pelajaran {{ addslashes($mapel->nama_mapel) }}?')) document.getElementById('del-mapel-{{ $mapel->id_mapel }}').submit();"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 transition shadow-xs hover:shadow-sm" 
                                            title="Hapus Mapel"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-9 h-9 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span class="text-sm font-medium">Belum ada data mata pelajaran yang cocok.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Card Footer Info -->
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Total: <strong>{{ $mapelList->count() }}</strong> mata pelajaran</span>
            <span>Urutan tampil maksimal: <strong>#{{ $stats['max_urutan'] }}</strong></span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL-MODAL DIALOG (ALPINE.JS)                                            -->
    <!-- ========================================================================= -->

    <!-- 1. Modal Tambah Kelompok Utama -->
    <div x-show="openModalKelompok" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalKelompok = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Kelompok Mata Pelajaran</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tambah Kelompok Utama Mata Pelajaran</p>
                </div>
                <button @click="openModalKelompok = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.mapel.kelompok.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id_parent" value="0">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode*</label>
                    <input type="text" name="kode_kel_mapel" placeholder="Contoh: A, B, PEM" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kelompok*</label>
                    <input type="text" name="nama_kel_mapel" placeholder="Contoh: Kelompok A (Wajib)" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori*</label>
                    <select name="kategori" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalKelompok = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Modal Edit Kelompok Utama -->
    <div x-show="openModalEditKelompok" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalEditKelompok = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Kelompok Mapel</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui Kelompok Utama</p>
                </div>
                <button @click="openModalEditKelompok = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('/admin/master/mapel/kelompok') }}/' + editKelompokData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_parent" value="0">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode*</label>
                    <input type="text" name="kode_kel_mapel" x-model="editKelompokData.kode" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kelompok*</label>
                    <input type="text" name="nama_kel_mapel" x-model="editKelompokData.nama" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori*</label>
                    <select name="kategori" x-model="editKelompokData.kategori" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalEditKelompok = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Modal Tambah Sub Kelompok -->
    <div x-show="openModalSubKelompok" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalSubKelompok = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Sub Kelompok Mapel</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tambah Sub Kelompok Mata Pelajaran</p>
                </div>
                <button @click="openModalSubKelompok = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.mapel.kelompok.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode*</label>
                    <input type="text" name="kode_kel_mapel" placeholder="Contoh: C1, C2, MULOK1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sub Kelompok*</label>
                    <input type="text" name="nama_kel_mapel" placeholder="Contoh: Kelompok C1 (Dasar Bidang Keahlian)" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelompok Utama Induk*</label>
                    <select name="id_parent" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">Pilih Kelompok Induk</option>
                        @foreach($kelompokUtama as $ku)
                            <option value="{{ $ku->id_kel_mapel }}">{{ $ku->nama_kel_mapel }} ({{ $ku->kode_kel_mapel }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalSubKelompok = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. Modal Edit Sub Kelompok -->
    <div x-show="openModalEditSubKelompok" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalEditSubKelompok = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Sub Kelompok</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui Sub Kelompok Mapel</p>
                </div>
                <button @click="openModalEditSubKelompok = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('/admin/master/mapel/kelompok') }}/' + editSubKelompokData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode*</label>
                    <input type="text" name="kode_kel_mapel" x-model="editSubKelompokData.kode" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sub Kelompok*</label>
                    <input type="text" name="nama_kel_mapel" x-model="editSubKelompokData.nama" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelompok Utama Induk*</label>
                    <select name="id_parent" x-model="editSubKelompokData.id_parent" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach($kelompokUtama as $ku)
                            <option value="{{ $ku->id_kel_mapel }}">{{ $ku->nama_kel_mapel }} ({{ $ku->kode_kel_mapel }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalEditSubKelompok = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. Modal Tambah Mata Pelajaran -->
    <div x-show="openModalAddMapel" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalAddMapel = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Mata Pelajaran</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan mata pelajaran kurikulum baru</p>
                </div>
                <button @click="openModalAddMapel = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.mapel.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran*</label>
                    <input type="text" name="nama_mapel" placeholder="Contoh: Matematika Peminatan" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Mapel*</label>
                        <input type="text" name="kode" placeholder="Contoh: MAT-PEM" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelompok*</label>
                        <select name="kelompok" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="-">-- Belum Dikelompokkan (-) --</option>
                            @foreach($allKelompok as $ak)
                                <option value="{{ $ak->kode_kel_mapel }}">[{{ $ak->kode_kel_mapel }}] {{ $ak->nama_kel_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mapel Agama</label>
                        <select name="mapel_agama" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="0">Bukan</option>
                            <option value="1">Ya (Mapel Agama)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Mapel</label>
                        <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Urut Tampil (Rapor)</label>
                    <input type="number" name="urutan_tampil" placeholder="Otomatis urutan berikutnya jika kosong" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalAddMapel = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Simpan Mapel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 6. Modal Edit Mata Pelajaran -->
    <div x-show="openModalEditMapel" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalEditMapel = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Mata Pelajaran</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Perbarui ' + editMapelData.nama_mapel"></p>
                </div>
                <button @click="openModalEditMapel = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('/admin/master/mapel') }}/' + editMapelData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran*</label>
                    <input type="text" name="nama_mapel" x-model="editMapelData.nama_mapel" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Mapel*</label>
                        <input type="text" name="kode" x-model="editMapelData.kode" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelompok*</label>
                        <select name="kelompok" x-model="editMapelData.kelompok" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="-">-- Belum Dikelompokkan (-) --</option>
                            @foreach($allKelompok as $ak)
                                <option value="{{ $ak->kode_kel_mapel }}">[{{ $ak->kode_kel_mapel }}] {{ $ak->nama_kel_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mapel Agama</label>
                        <select name="mapel_agama" x-model="editMapelData.mapel_agama" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="0">Bukan</option>
                            <option value="1">Ya (Mapel Agama)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Mapel</label>
                        <select name="status" x-model="editMapelData.status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Urut Tampil (Rapor)</label>
                    <input type="number" name="urutan_tampil" x-model="editMapelData.urutan_tampil" placeholder="Nomor urut" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalEditMapel = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Perbarui Mapel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 7. Modal Import Mapel (Excel / CSV) -->
    <div x-show="openModalImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalImport = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Import Mata Pelajaran</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Unggah file Excel (.xlsx) atau CSV</p>
                </div>
                <button @click="openModalImport = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl mb-4 space-y-2">
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Gunakan template resmi untuk mengimpor daftar mata pelajaran secara massal.
                </p>
                <div>
                    <a href="{{ route('admin.master.mapel.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download Template Excel (format_mapel.xlsx)</span>
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.master.mapel.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih File Excel / CSV *</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-slate-800 dark:file:text-brand-400 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-950">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalImport = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
