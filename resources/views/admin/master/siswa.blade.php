@extends('layouts.admin')

@section('title', 'Data Siswa & Akun')
@section('page_title', 'Master Data Siswa & Akun CBT')

@section('content')
<div class="space-y-6" x-data="{ 
    openModalAdd: false, 
    openModalEdit: false, 
    openModalImport: false,
    openModalExport: false,
    openModalReset: false,
    openDropdownAksi: false,
    selectedIds: [],
    selectAll: false,
    showPasswordId: null,
    editSiswa: {
        id: '',
        nama: '',
        nis: '',
        nisn: '',
        jenis_kelamin: 'L',
        agama: 'Islam',
        id_kelas: '',
        kelas_awal: '',
        tahun_masuk: '',
        username: '',
        password: '',
        status: 'aktif',
        mapel_pilihan: []
    },
    resetData: {
        id: '',
        nama: '',
        username: '',
        password: ''
    },
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value);
        } else {
            this.selectedIds = [];
        }
    },
    updateSelectAll() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        this.selectAll = checkboxes.length > 0 && this.selectedIds.length === checkboxes.length;
    },
    openEdit(data) {
        this.editSiswa = { 
            id: data.id_siswa,
            nama: data.nama || '',
            nis: data.nis && data.nis !== '-' ? data.nis : '',
            nisn: data.nisn && data.nisn !== '-' ? data.nisn : '',
            jenis_kelamin: data.jenis_kelamin || 'L',
            agama: data.agama || 'Islam',
            id_kelas: data.id_kelas || '',
            kelas_awal: data.kelas_awal || '',
            tahun_masuk: data.tahun_masuk || '',
            username: data.username || '',
            password: '',
            status: data.status || 'aktif',
            mapel_pilihan: data.mapel_pilihan || []
        };
        this.openModalEdit = true;
    },
    openReset(id, nama, username, defaultPass) {
        this.resetData = { 
            id: id, 
            nama: nama, 
            username: username, 
            password: defaultPass || '123456' 
        };
        this.openModalReset = true;
    },
    triggerBulk(action) {
        if (this.selectedIds.length === 0) return;
        const form = document.getElementById('bulkActionForm');
        document.getElementById('bulkActionInput').value = action;
        
        let confirmMsg = `Terapkan status '${action.toUpperCase()}' pada ${this.selectedIds.length} siswa terpilih?`;
        if (action === 'hapus') {
            confirmMsg = `PERINGATAN: Anda yakin ingin MENGHAPUS ${this.selectedIds.length} data siswa terpilih beserta seluruh akun login & riwayat kelas? Aksi ini permanen!`;
        }
        
        if (confirm(confirmMsg)) {
            form.submit();
        }
    }
}">

    <!-- Top Statistics Cards (Matching US1 Master & Garuda Metrics) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Siswa</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($totalSiswa ?? 0) }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Laki-laki</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($totalLaki ?? 0) }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Perempuan</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($totalPerempuan ?? 0) }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Status Aktif</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($totalAktif ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Header Actions & Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Siswa & Peserta Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola master siswa, rombel kelas, akun login CBT, mapel pilihan, dan status mutasi</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Cetak Kartu Peserta -->
            <a href="{{ route('print.kartu_peserta') }}" target="_blank" class="px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Kartu</span>
            </a>

            <!-- Unduh / Ekspor Data -->
            <button @click="openModalExport = true" type="button" class="px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Ekspor Data</span>
            </button>

            <!-- Import Siswa (Excel/CSV) -->
            <button @click="openModalImport = true" type="button" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Siswa</span>
            </button>

            <!-- Tambah Siswa -->
            <button @click="openModalAdd = true" type="button" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Siswa</span>
            </button>
        </div>
    </div>

    <!-- Filter & Bulk Action Toolbar Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
        <!-- Form Search & Filter -->
        <form id="filterForm" action="{{ route('admin.master.siswa') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Limit / Per Page (US1 Parity) -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tampilkan</label>
                <select name="per_page" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-brand-500 font-medium">
                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 Siswa</option>
                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25 Siswa</option>
                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 Siswa</option>
                    <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 Siswa</option>
                </select>
            </div>

            <!-- Search Input (Nama, NIS, NISN, Username) -->
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pencarian</label>
                <div class="relative">
                    <input type="text" id="searchInput" name="q" value="{{ $search }}" placeholder="Nama siswa, NISN, NIS, atau username..." class="w-full pl-9 pr-8 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    @if($search)
                        <a href="{{ route('admin.master.siswa', request()->except(['q', 'page'])) }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Status Filter (US1 Parity: Semua, Aktif, Nonaktif, Pindah, Keluar) -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status Siswa</label>
                <select name="status" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-brand-500 font-medium">
                    <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>Semua Status</option>
                    <option value="aktif" {{ $statusFilter == 'aktif' || $statusFilter == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ $statusFilter == 'nonaktif' || $statusFilter == '2' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="pindah" {{ $statusFilter == 'pindah' || $statusFilter == '3' ? 'selected' : '' }}>Pindah</option>
                    <option value="keluar" {{ $statusFilter == 'keluar' || $statusFilter == '4' ? 'selected' : '' }}>Keluar</option>
                </select>
            </div>

            <!-- Filter Kelas -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Rombel Kelas</label>
                <select name="kelas_id" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-brand-500 font-medium">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $kelasId == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tahun Ajaran Filter -->
            <div class="sm:col-span-2 flex items-end gap-2">
                <div class="w-full">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tahun Pelajaran</label>
                    <select name="tahun_ajaran_id" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-brand-500 font-medium">
                        @if(isset($allYears))
                            @foreach($allYears as $y)
                                <option value="{{ $y->id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->id) ? 'selected' : '' }}>
                                    {{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @if($search || $kelasId || $statusFilter)
                    <a href="{{ route('admin.master.siswa') }}" class="p-2 mb-0.5 text-xs text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0 transition" title="Reset Semua Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </a>
                @endif
            </div>
        </form>

        <!-- Bulk Action Dropdown & Selection Indicator (US1 Parity) -->
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <!-- Dropdown Tombol Aksi -->
                <div class="relative" @click.away="openDropdownAksi = false">
                    <button 
                        @click="openDropdownAksi = !openDropdownAksi"
                        :disabled="selectedIds.length === 0"
                        type="button" 
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-sm"
                        :class="selectedIds.length > 0 ? 'bg-rose-600 hover:bg-rose-500 text-white cursor-pointer' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed border border-slate-200 dark:border-slate-700'"
                    >
                        <span>Aksi Terpilih</span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="openDropdownAksi ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Content -->
                    <div 
                        x-show="openDropdownAksi" 
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 mt-1.5 w-52 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 z-30 divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <div class="py-1">
                            <button @click="triggerBulk('pindah'); openDropdownAksi = false" type="button" class="w-full text-left px-3.5 py-2 text-xs text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 flex items-center gap-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                <span>Set sebagai PINDAH</span>
                            </button>
                            <button @click="triggerBulk('keluar'); openDropdownAksi = false" type="button" class="w-full text-left px-3.5 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center gap-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Set sebagai KELUAR</span>
                            </button>
                        </div>
                        <div class="py-1">
                            <button @click="triggerBulk('aktif'); openDropdownAksi = false" type="button" class="w-full text-left px-3.5 py-2 text-xs text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 flex items-center gap-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Set sebagai AKTIF</span>
                            </button>
                            <button @click="triggerBulk('nonaktif'); openDropdownAksi = false" type="button" class="w-full text-left px-3.5 py-2 text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <span>Set sebagai NONAKTIF</span>
                            </button>
                        </div>
                        <div class="py-1">
                            <button @click="triggerBulk('hapus'); openDropdownAksi = false" type="button" class="w-full text-left px-3.5 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 font-bold flex items-center gap-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>HAPUS Terpilih</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="selectedIds.length > 0" x-cloak class="text-xs font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-3 py-1 rounded-lg">
                    <span x-text="selectedIds.length"></span> siswa dipilih
                </div>
            </div>

            <!-- Total Entri Info (US1 Parity) -->
            <div class="text-xs text-slate-500 dark:text-slate-400">
                @if($siswas->total() > 0)
                    Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200">{{ $siswas->firstItem() }} ~ {{ $siswas->lastItem() }}</span> dari <span class="font-bold text-slate-800 dark:text-slate-200">{{ $siswas->total() }}</span> entri siswa
                @else
                    0 entri siswa
                @endif
            </div>
        </div>
    </div>

    <!-- Hidden Bulk Action Form -->
    <form id="bulkActionForm" action="{{ route('admin.master.siswa.bulk_action') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="aksi" id="bulkActionInput" value="">
        <template x-for="id in selectedIds" :key="id">
            <input type="hidden" name="checked[]" :value="id">
        </template>
    </form>

    <!-- Data Table Card (Exact US1 UI Presentation) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/70 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800 select-none">
                    <tr>
                        <th class="py-3.5 px-4 w-10 text-center">
                            <input 
                                type="checkbox" 
                                x-model="selectAll" 
                                @change="toggleSelectAll()" 
                                class="rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                title="Pilih Semua di Halaman Ini"
                            >
                        </th>
                        <th class="py-3.5 px-3 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">NAMA & KELAS</th>
                        <th class="py-3.5 px-4">NIS & NISN</th>
                        <th class="py-3.5 px-4">AKUN LOGIN CBT</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($siswas as $idx => $siswa)
                        @php
                            $rombelEntry = $siswa->kelasSiswa->first();
                            $namaKelas = $rombelEntry?->kelas?->nama_kelas ?? '-';
                            $idKelas = $rombelEntry?->id_kelas ?? '';
                            
                            $statusRombel = $siswa->rombelTahun->first()?->status ?? ($siswa->user?->active ? 'aktif' : 'nonaktif');
                            
                            // Mapel pilihan labels
                            $mpLabels = [];
                            if (!empty($siswa->mapel_pilihan)) {
                                $mpIds = is_array($siswa->mapel_pilihan) ? $siswa->mapel_pilihan : json_decode($siswa->mapel_pilihan, true);
                                if (is_array($mpIds)) {
                                    $mapelMap = $mapelList->pluck('nama_mapel', 'id_mapel')->toArray();
                                    foreach ($mpIds as $mpId) {
                                        if (isset($mapelMap[$mpId])) {
                                            $mpLabels[] = $mapelMap[$mpId];
                                        }
                                    }
                                }
                            }

                            // Pass payload to JS for Edit Modal
                            $clientData = [
                                'id_siswa'      => $siswa->id_siswa,
                                'nama'          => $siswa->nama,
                                'nis'           => $siswa->nis,
                                'nisn'          => $siswa->nisn,
                                'jenis_kelamin' => $siswa->jenis_kelamin ?? 'L',
                                'agama'         => $siswa->agama ?? 'Islam',
                                'id_kelas'      => $idKelas,
                                'kelas_awal'    => $siswa->kelas_awal ?? '',
                                'tahun_masuk'   => $siswa->tahun_masuk ?? '',
                                'username'      => $siswa->username,
                                'status'        => $statusRombel,
                                'mapel_pilihan' => is_array($siswa->mapel_pilihan) ? $siswa->mapel_pilihan : (json_decode($siswa->mapel_pilihan, true) ?? []),
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <!-- Checkbox -->
                            <td class="py-3 px-4 text-center">
                                <input 
                                    type="checkbox" 
                                    value="{{ $siswa->id_siswa }}" 
                                    x-model="selectedIds" 
                                    @change="updateSelectAll()"
                                    class="row-checkbox rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                >
                            </td>

                            <!-- Nomor -->
                            <td class="py-3 px-3 text-center text-slate-400 font-mono text-[11px]">
                                {{ ($siswas->currentPage() - 1) * $siswas->perPage() + $loop->iteration }}
                            </td>

                            <!-- NAMA & KELAS (US1 Card Presentation) -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <!-- Avatar -->
                                    <div class="relative shrink-0">
                                        @if(!empty($siswa->foto) && file_exists(public_path($siswa->foto)))
                                            <img src="{{ asset($siswa->foto) }}" alt="{{ $siswa->nama }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold text-xs flex items-center justify-center shadow-sm uppercase">
                                                {{ substr($siswa->nama, 0, 2) }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Details & Badges -->
                                    <div class="space-y-1">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs leading-tight">
                                            {{ $siswa->nama }}
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <!-- Kelas Badge -->
                                            @if($namaKelas !== '-')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                                                    {{ $namaKelas }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                                    Tanpa Kelas
                                                </span>
                                            @endif

                                            <!-- Agama Badge -->
                                            @if($siswa->agama)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    {{ $siswa->agama }}
                                                </span>
                                            @endif

                                            <!-- Gender Badge -->
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $siswa->jenis_kelamin === 'P' ? 'bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-200 dark:border-pink-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                                {{ $siswa->jenis_kelamin ?? 'L' }}
                                            </span>

                                            <!-- Status Badge -->
                                            @if($statusRombel === 'aktif')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">
                                                    Aktif
                                                </span>
                                            @elseif($statusRombel === 'pindah')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                                    Pindah
                                                </span>
                                            @elseif($statusRombel === 'keluar')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30">
                                                    Keluar
                                                </span>
                                            @elseif($statusRombel === 'lulus')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/30">
                                                    Lulus
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    Nonaktif
                                                </span>
                                            @endif

                                            <!-- Mapel Pilihan Badges -->
                                            @foreach($mpLabels as $mpLabel)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-500/20">
                                                    {{ $mpLabel }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- NIS & NISN -->
                            <td class="py-3 px-4 font-mono text-[11px] space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] uppercase font-bold text-slate-400 w-8">NIS:</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-semibold border border-slate-200/60 dark:border-slate-700/60">
                                        {{ $siswa->nis && $siswa->nis !== '-' ? $siswa->nis : '-' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] uppercase font-bold text-slate-400 w-8">NISN:</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-semibold border border-slate-200/60 dark:border-slate-700/60">
                                        {{ $siswa->nisn && $siswa->nisn !== '-' ? $siswa->nisn : '-' }}
                                    </span>
                                </div>
                            </td>

                            <!-- AKUN LOGIN CBT -->
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 w-8">User:</span>
                                        <span class="font-mono font-bold text-brand-600 dark:text-brand-400 text-xs">
                                            {{ $siswa->username }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 w-8">Pass:</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono bg-slate-100 dark:bg-slate-950 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                                <span x-show="showPasswordId === {{ $siswa->id_siswa }}">{{ $siswa->password ?? '••••••' }}</span>
                                                <span x-show="showPasswordId !== {{ $siswa->id_siswa }}">••••••</span>
                                            </span>
                                            <button 
                                                type="button" 
                                                @click="showPasswordId = (showPasswordId === {{ $siswa->id_siswa }} ? null : {{ $siswa->id_siswa }})" 
                                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-0.5" 
                                                title="Lihat / Sembunyikan Password"
                                            >
                                                <svg x-show="showPasswordId !== {{ $siswa->id_siswa }}" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <svg x-show="showPasswordId === {{ $siswa->id_siswa }}" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- AKSI (US1 Buttons: Edit, Reset Password, Hapus) -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button (US1 btn-warning) -->
                                    <button 
                                        @click="openEdit({{ json_encode($clientData) }})" 
                                        type="button" 
                                        class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white rounded-lg transition border border-amber-500/20" 
                                        title="Edit Data Siswa"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    <!-- Reset Password Button -->
                                    <button 
                                        @click="openReset({{ $siswa->id_siswa }}, '{{ addslashes($siswa->nama) }}', '{{ $siswa->username }}', '{{ $siswa->nisn !== '-' ? $siswa->nisn : '123456' }}')" 
                                        type="button" 
                                        class="p-1.5 bg-indigo-500/10 hover:bg-indigo-500 text-indigo-600 hover:text-white rounded-lg transition border border-indigo-500/20" 
                                        title="Reset Password"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </button>

                                    <!-- Hapus Single Button -->
                                    <form action="{{ route('admin.master.siswa.destroy', $siswa->id_siswa) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa {{ addslashes($siswa->nama) }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white rounded-lg transition border border-rose-500/20" 
                                            title="Hapus Siswa"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <div class="text-xs font-semibold">Tidak ada data siswa ditemukan.</div>
                                    <div class="text-[11px] text-slate-400">Silakan sesuaikan filter pencarian atau tambahkan siswa baru.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if($siswas->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold">{{ $siswas->firstItem() }}</span> hingga <span class="font-bold">{{ $siswas->lastItem() }}</span> dari <span class="font-bold">{{ $siswas->total() }}</span> siswa
                </div>
                <div>
                    {{ $siswas->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 1: TAMBAH SISWA (Full US1 Input Fields Parity) -->
    <!-- ======================================================================= -->
    <div x-show="openModalAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalAdd = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Siswa Baru</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Input data lengkap peserta ujian, rombel kelas, dan akun login CBT</p>
                </div>
                <button type="button" @click="openModalAdd = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.siswa.store') }}" method="POST" class="space-y-4">
                @csrf
                <!-- Nama Siswa -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" placeholder="Contoh: Ahmad Fauzan" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <!-- NIS & NISN -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIS</label>
                        <input type="text" name="nis" placeholder="Nomor Induk Siswa" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NISN</label>
                        <input type="text" name="nisn" placeholder="Nomor Induk Siswa Nasional" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <!-- Gender & Agama -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Agama</label>
                        <select name="agama" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Protestan">Protestan</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Budha">Budha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Rombel Kelas & Tanggal Masuk -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombel Kelas <span class="text-red-500">*</span></label>
                        <select name="id_kelas" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="">-- Pilih Rombel Kelas --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tgl / Tahun Masuk</label>
                        <input type="text" name="tahun_masuk" placeholder="Contoh: 2024 atau 2024-07-15" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <!-- Mapel Pilihan (US1 Multi-select Parity) -->
                @if(isset($mapelList) && $mapelList->count() > 0)
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mapel Pilihan (Kurikulum Merdeka - Opsional)</label>
                        <div class="max-h-28 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl grid grid-cols-2 gap-2 text-xs">
                            @foreach($mapelList as $mp)
                                <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="mapel_pilihan[]" value="{{ $mp->id_mapel }}" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                                    <span class="truncate">{{ $mp->nama_mapel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Akun Login: Username & Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Username Login <span class="text-red-500">*</span></label>
                        <input type="text" name="username" placeholder="Contoh: 0051234567" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="text" name="password" value="123456" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalAdd = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl transition">
                        Batal
                    </button>
                    <button type="reset" class="px-4 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 rounded-xl transition">
                        Reset Form
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 2: EDIT SISWA -->
    <!-- ======================================================================= -->
    <div x-show="openModalEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalEdit = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Data Siswa</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui profil siswa, rombel kelas, mapel pilihan, dan status mutasi</p>
                </div>
                <button type="button" @click="openModalEdit = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/master/siswa') }}/' + editSiswa.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" x-model="editSiswa.nama" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIS</label>
                        <input type="text" name="nis" x-model="editSiswa.nis" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NISN</label>
                        <input type="text" name="nisn" x-model="editSiswa.nisn" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="editSiswa.jenis_kelamin" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Agama</label>
                        <select name="agama" x-model="editSiswa.agama" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Protestan">Protestan</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Budha">Budha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombel Kelas <span class="text-red-500">*</span></label>
                        <select name="id_kelas" x-model="editSiswa.id_kelas" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="">-- Pilih Rombel Kelas --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Keaktifan</label>
                        <select name="status" x-model="editSiswa.status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                            <option value="lulus">Lulus</option>
                        </select>
                    </div>
                </div>

                <!-- Mapel Pilihan Checkboxes -->
                @if(isset($mapelList) && $mapelList->count() > 0)
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mapel Pilihan</label>
                        <div class="max-h-24 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl grid grid-cols-2 gap-2 text-xs">
                            @foreach($mapelList as $mp)
                                <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="mapel_pilihan[]" value="{{ $mp->id_mapel }}" :checked="(editSiswa.mapel_pilihan || []).includes({{ $mp->id_mapel }}) || (editSiswa.mapel_pilihan || []).includes('{{ $mp->id_mapel }}')" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                                    <span class="truncate">{{ $mp->nama_mapel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Username & Password Baru (Opsional) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Username Login <span class="text-red-500">*</span></label>
                        <input type="text" name="username" x-model="editSiswa.username" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                        <input type="text" name="password" x-model="editSiswa.password" placeholder="Password baru" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalEdit = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 3: IMPORT SISWA (.XLSX & .CSV with Template) -->
    <!-- ======================================================================= -->
    <div x-show="openModalImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalImport = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Import Data Siswa Massal</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Unggah berkas Excel (.xlsx) atau CSV untuk memasukkan ratusan siswa sekaligus</p>
                </div>
                <button type="button" @click="openModalImport = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Petunjuk & Download Template -->
            <div class="mb-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl p-3 text-xs text-emerald-800 dark:text-emerald-300">
                <div class="font-bold flex items-center gap-1.5 mb-1">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Format Kolom Import</span>
                </div>
                <p class="mb-2">Gunakan format template resmi agar data nama, NIS, NISN, gender, kelas, dan akun CBT terbaca otomatis.</p>
                <a href="{{ route('admin.master.siswa.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-semibold text-[11px] shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Template CSV</span>
                </a>
            </div>

            <form action="{{ route('admin.master.siswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <!-- Target Kelas Default (Opsional jika di file tidak ada kelas) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombel Kelas Tujuan (Opsional)</label>
                    <select name="id_kelas" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">-- Baca dari kolom 'Kelas' di file excel/csv --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-400">Jika kolom 'Kelas' diisi di berkas, sistem akan otomatis menetapkan kelas tersebut.</span>
                </div>

                <!-- File Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Berkas (.xlsx, .csv) <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-slate-800 dark:file:text-slate-200 border border-slate-200 dark:border-slate-800 rounded-xl p-1 bg-slate-50 dark:bg-slate-950">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalImport = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 4: EKSPOR DATA SISWA (US1 Parity) -->
    <!-- ======================================================================= -->
    <div x-show="openModalExport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalExport = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Ekspor Data Siswa</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Unduh data rekap siswa per rombel kelas atau seluruh sekolah</p>
                </div>
                <button type="button" @click="openModalExport = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.siswa.export') }}" method="GET" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Rombel Kelas</label>
                    <select name="kelas_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">-- Seluruh Siswa (Semua Kelas) --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalExport = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" @click="openModalExport = false" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh File CSV</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 5: RESET PASSWORD SISWA -->
    <!-- ======================================================================= -->
    <div x-show="openModalReset" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openModalReset = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Reset Password Akun</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Siswa: ' + resetData.nama"></p>
                </div>
                <button type="button" @click="openModalReset = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/master/siswa') }}/' + resetData.id + '/reset-password'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Username Login</label>
                    <input type="text" x-model="resetData.username" readonly class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password Baru <span class="text-red-500">*</span></label>
                    <input type="text" name="password" x-model="resetData.password" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:border-brand-500">
                    <span class="text-[10px] text-slate-400">Bisa diatur sama dengan NISN atau kata sandi default 123456.</span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalReset = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
