@extends('layouts.admin')

@section('title', 'Jabatan Guru')
@section('page_title', 'Jabatan Guru')

@section('content')
<script>
window.__jabatanConfig = {
    level: {{ $jabatan?->id_jabatan ? intval($jabatan->id_jabatan) : 5 }},
    kelas_wali: {!! json_encode($jabatan?->id_kelas ? strval($jabatan->id_kelas) : '') !!},
    allMapels: {!! json_encode($mapelList) !!},
    allEkstras: {!! json_encode($ekskulList) !!},
    allKelases: {!! json_encode($kelasList) !!},
    assignedMapels: {!! json_encode($assignedMapels) !!},
    assignedEkskul: {!! json_encode($assignedEkskul) !!}
};

function guruJabatanEditor() {
    return {
        showBeforeModal: false,
        showLevelModal: false,
        level: window.__jabatanConfig.level,
        kelas_wali: window.__jabatanConfig.kelas_wali,
        searchMapel: '',
        searchEkstra: '',
        dropdownOpenMapel: false,
        dropdownOpenEkstra: false,
        dropdownOpenClass: {},
        allMapels: window.__jabatanConfig.allMapels || [],
        allEkstras: window.__jabatanConfig.allEkstras || [],
        allKelases: window.__jabatanConfig.allKelases || [],
        selectedMapelIds: [],
        selectedEkstraIds: [],
        kelasMapels: {},
        kelasEkstras: {},

        init() {
            const mapels = window.__jabatanConfig.assignedMapels;
            if (Array.isArray(mapels)) {
                mapels.forEach(m => {
                    if (m && m.id_mapel) {
                        const id = String(m.id_mapel);
                        if (!this.selectedMapelIds.includes(id)) {
                            this.selectedMapelIds.push(id);
                        }
                        this.kelasMapels[id] = (m.kelas_mapel || []).map(k => String(k.kelas)).filter(Boolean);
                    }
                });
            }

            const ekstras = window.__jabatanConfig.assignedEkskul;
            if (Array.isArray(ekstras)) {
                ekstras.forEach(e => {
                    if (e && e.id_ekstra) {
                        const id = String(e.id_ekstra);
                        if (!this.selectedEkstraIds.includes(id)) {
                            this.selectedEkstraIds.push(id);
                        }
                        this.kelasEkstras[id] = (e.kelas_ekstra || []).map(k => String(k.kelas)).filter(Boolean);
                    }
                });
            }
        },

        getMapel(id) {
            return this.allMapels.find(m => String(m.id_mapel) === String(id));
        },

        getEkstra(id) {
            return this.allEkstras.find(e => String(e.id_ekstra) === String(id));
        },

        getKelas(id) {
            return this.allKelases.find(k => String(k.id_kelas) === String(id));
        },

        addMapel(id) {
            id = String(id);
            if (!this.selectedMapelIds.includes(id)) {
                this.selectedMapelIds.push(id);
                if (!this.kelasMapels[id]) {
                    this.kelasMapels[id] = [];
                }
            }
            this.searchMapel = '';
            this.dropdownOpenMapel = false;
        },

        removeMapel(id) {
            id = String(id);
            this.selectedMapelIds = this.selectedMapelIds.filter(item => item !== id);
            delete this.kelasMapels[id];
        },

        addEkstra(id) {
            id = String(id);
            if (!this.selectedEkstraIds.includes(id)) {
                this.selectedEkstraIds.push(id);
                if (!this.kelasEkstras[id]) {
                    this.kelasEkstras[id] = [];
                }
            }
            this.searchEkstra = '';
            this.dropdownOpenEkstra = false;
        },

        removeEkstra(id) {
            id = String(id);
            this.selectedEkstraIds = this.selectedEkstraIds.filter(item => item !== id);
            delete this.kelasEkstras[id];
        },

        toggleClass(mapelId, classId) {
            mapelId = String(mapelId);
            classId = String(classId);
            if (!this.kelasMapels[mapelId]) {
                this.kelasMapels[mapelId] = [];
            }
            const index = this.kelasMapels[mapelId].indexOf(classId);
            if (index > -1) {
                this.kelasMapels[mapelId].splice(index, 1);
            } else {
                this.kelasMapels[mapelId].push(classId);
            }
        },

        isClassSelected(mapelId, classId) {
            mapelId = String(mapelId);
            classId = String(classId);
            return (this.kelasMapels[mapelId] || []).includes(classId);
        },

        selectAllClasses(mapelId) {
            mapelId = String(mapelId);
            this.kelasMapels[mapelId] = this.allKelases.map(k => String(k.id_kelas));
        },

        clearAllClasses(mapelId) {
            mapelId = String(mapelId);
            this.kelasMapels[mapelId] = [];
        },

        toggleEkstraClass(ekstraId, classId) {
            ekstraId = String(ekstraId);
            classId = String(classId);
            if (!this.kelasEkstras[ekstraId]) {
                this.kelasEkstras[ekstraId] = [];
            }
            const index = this.kelasEkstras[ekstraId].indexOf(classId);
            if (index > -1) {
                this.kelasEkstras[ekstraId].splice(index, 1);
            } else {
                this.kelasEkstras[ekstraId].push(classId);
            }
        },

        isEkstraClassSelected(ekstraId, classId) {
            ekstraId = String(ekstraId);
            classId = String(classId);
            return (this.kelasEkstras[ekstraId] || []).includes(classId);
        },

        selectAllEkstraClasses(ekstraId) {
            ekstraId = String(ekstraId);
            this.kelasEkstras[ekstraId] = this.allKelases.map(k => String(k.id_kelas));
        },

        clearAllEkstraClasses(ekstraId) {
            ekstraId = String(ekstraId);
            this.kelasEkstras[ekstraId] = [];
        }
    };
}
</script>

<div class="space-y-6" x-data="guruJabatanEditor()">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Jabatan Guru</h2>
        </div>
        <a href="{{ route('admin.master.guru') }}" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Green Info Notice with Copy Button Matching CodeIgniter Reference -->
    <div class="p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800/60 text-emerald-950 dark:text-emerald-200 text-sm shadow-sm">
        <p class="mb-2 font-semibold">Jika ada jabatan guru di semester sebelumnya, jabatan dan mata pelajaran yang diampu bisa dicopy ke semester sekarang, dengan catatan:</p>
        <ul class="list-disc list-inside space-y-1 text-emerald-900 dark:text-emerald-300 text-xs md:text-sm pl-1">
            <li><strong>Aksi ini akan mengganti jabatan dan mapel guru</strong> yang sudah diatur di semester sekarang.</li>
            <li>Pastikan <strong>Kode Kelas</strong> sudah dibuat dan sama seperti Kode Kelas sebelumnya.</li>
            <li>Aksi ini tidak akan berjalan sempurna <strong>jika nama kode kelas di semester sebelumnya berbeda dengan kode kelas di semestr sekarang.</strong></li>
            <li>Kode kelas seperti <code class="px-1.5 py-0.5 bg-emerald-200/70 dark:bg-emerald-900/60 font-semibold rounded text-[11px]">7.A</code>, <code class="px-1.5 py-0.5 bg-emerald-200/70 dark:bg-emerald-900/60 font-semibold rounded text-[11px]">7-A</code>, <code class="px-1.5 py-0.5 bg-emerald-200/70 dark:bg-emerald-900/60 font-semibold rounded text-[11px]">7 A</code>, <code class="px-1.5 py-0.5 bg-emerald-200/70 dark:bg-emerald-900/60 font-semibold rounded text-[11px]">7_A</code> atau <code class="px-1.5 py-0.5 bg-emerald-200/70 dark:bg-emerald-900/60 font-semibold rounded text-[11px]">7A</code> akan dianggap sama</li>
        </ul>
        <div class="mt-4 flex justify-end">
            <button type="button" @click="showBeforeModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs md:text-sm font-semibold shadow-sm shadow-emerald-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                <span>Salin jabatan sebelumnya</span>
            </button>
        </div>
    </div>

    <!-- Main Form Card -->
    <form id="form-jabatan" action="{{ route('admin.master.guru.jabatan.update', $guru->id_guru) }}" method="POST">
        @csrf

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/40">
                <div>
                    <h3 class="text-base md:text-lg font-bold text-slate-900 dark:text-white">Edit Jabatan {{ $guru->nama_guru }}</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.location.reload()" class="px-3.5 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reload</span>
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-blue-600/30 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span>Simpan</span>
                    </button>
                </div>
            </div>

            <!-- Card Body Grid -->
            <div class="p-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Left Column: Mengajar & Tentukan Kelas Mapel -->
                <div class="lg:col-span-3 space-y-6">

                    <!-- Section Mengajar -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800">
                            <h4 class="text-sm md:text-base font-bold text-slate-900 dark:text-white">Mengajar</h4>
                        </div>
                        <div class="p-5 space-y-5">
                            <!-- Mata Pelajaran Input Tag Container -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mata Pelajaran:</label>
                                
                                <div class="relative" @click.outside="dropdownOpenMapel = false">
                                    <div class="min-h-[42px] p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-wrap items-center gap-1.5 cursor-text"
                                         @click="dropdownOpenMapel = true; $nextTick(() => $refs.inputSearchMapel.focus())">
                                        
                                        <!-- Selected Mapel Tags (Matching Screenshot) -->
                                        <template x-for="id in selectedMapelIds" :key="id">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm animate-fade-in">
                                                <span x-text="getMapel(id)?.nama_mapel || id"></span>
                                                <button type="button" @click.stop="removeMapel(id)" class="hover:bg-blue-700 rounded-full p-0.5 transition" title="Hapus mapel">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <input type="hidden" name="mapel[]" :value="id">
                                            </span>
                                        </template>

                                        <!-- Search input for adding -->
                                        <input type="text"
                                               x-ref="inputSearchMapel"
                                               x-model="searchMapel"
                                               @focus="dropdownOpenMapel = true"
                                               placeholder="Pilih Mata Pelajaran..."
                                               class="flex-1 min-w-[140px] bg-transparent border-none text-xs text-slate-900 dark:text-white focus:outline-none p-1 placeholder:text-slate-400">
                                    </div>

                                    <!-- Dropdown Options -->
                                    <div x-show="dropdownOpenMapel" x-cloak
                                         class="absolute z-20 top-full left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-1.5 space-y-1">
                                        <template x-for="mp in allMapels.filter(m => !selectedMapelIds.includes(String(m.id_mapel)) && (!searchMapel || m.nama_mapel.toLowerCase().includes(searchMapel.toLowerCase()) || m.kode.toLowerCase().includes(searchMapel.toLowerCase())))" :key="mp.id_mapel">
                                            <div @click="addMapel(mp.id_mapel)"
                                                 class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-800 cursor-pointer transition text-xs">
                                                <span class="font-medium text-slate-800 dark:text-slate-200" x-text="mp.nama_mapel"></span>
                                                <span class="text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded" x-text="mp.kode"></span>
                                            </div>
                                        </template>
                                        <div x-show="allMapels.filter(m => !selectedMapelIds.includes(String(m.id_mapel)) && (!searchMapel || m.nama_mapel.toLowerCase().includes(searchMapel.toLowerCase()))).length === 0"
                                             class="p-3 text-center text-xs text-slate-400 italic">
                                            Tidak ada mata pelajaran yang cocok.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ekstrakurikuler Input Tag Container -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ekstrakurikuler:</label>
                                
                                <div class="relative" @click.outside="dropdownOpenEkstra = false">
                                    <div class="min-h-[42px] p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-wrap items-center gap-1.5 cursor-text"
                                         @click="dropdownOpenEkstra = true; $nextTick(() => $refs.inputSearchEkstra.focus())">
                                        
                                        <!-- Selected Ekstra Tags -->
                                        <template x-for="id in selectedEkstraIds" :key="id">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-600 text-white text-xs font-semibold shadow-sm animate-fade-in">
                                                <span x-text="getEkstra(id)?.nama_ekstra || id"></span>
                                                <button type="button" @click.stop="removeEkstra(id)" class="hover:bg-emerald-700 rounded-full p-0.5 transition" title="Hapus ekstra">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <input type="hidden" name="ekstra[]" :value="id">
                                            </span>
                                        </template>

                                        <!-- Search input for adding -->
                                        <input type="text"
                                               x-ref="inputSearchEkstra"
                                               x-model="searchEkstra"
                                               @focus="dropdownOpenEkstra = true"
                                               placeholder="Pilih Ekstrakurikuler..."
                                               class="flex-1 min-w-[140px] bg-transparent border-none text-xs text-slate-900 dark:text-white focus:outline-none p-1 placeholder:text-slate-400">
                                    </div>

                                    <!-- Dropdown Options -->
                                    <div x-show="dropdownOpenEkstra" x-cloak
                                         class="absolute z-20 top-full left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-1.5 space-y-1">
                                        <template x-for="eks in allEkstras.filter(e => !selectedEkstraIds.includes(String(e.id_ekstra)) && (!searchEkstra || e.nama_ekstra.toLowerCase().includes(searchEkstra.toLowerCase())))" :key="eks.id_ekstra">
                                            <div @click="addEkstra(eks.id_ekstra)"
                                                 class="flex items-center justify-between p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-slate-800 cursor-pointer transition text-xs">
                                                <span class="font-medium text-slate-800 dark:text-slate-200" x-text="eks.nama_ekstra"></span>
                                                <span class="text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded" x-text="eks.kode_ekstra"></span>
                                            </div>
                                        </template>
                                        <div x-show="allEkstras.filter(e => !selectedEkstraIds.includes(String(e.id_ekstra)) && (!searchEkstra || e.nama_ekstra.toLowerCase().includes(searchEkstra.toLowerCase()))).length === 0"
                                             class="p-3 text-center text-xs text-slate-400 italic">
                                            Tidak ada ekstrakurikuler yang cocok.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Tentukan Kelas Mapel (Matching Screenshot) -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <h4 class="text-sm md:text-base font-bold text-slate-900 dark:text-white">Tentukan Kelas Mapel</h4>
                        </div>
                        <div class="p-5 space-y-5">
                            <!-- Empty State -->
                            <div x-show="selectedMapelIds.length === 0 && selectedEkstraIds.length === 0" class="text-center py-10 text-slate-400 text-xs md:text-sm italic">
                                Pilih mata pelajaran atau ekstrakurikuler terlebih dahulu di atas untuk menentukan rombel/kelas.
                            </div>

                            <!-- List Mapel Classes -->
                            <template x-for="mapelId in selectedMapelIds" :key="'mapel_' + mapelId">
                                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/40 dark:bg-slate-950/20">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <h5 class="text-xs md:text-sm font-bold text-slate-900 dark:text-white" x-text="getMapel(mapelId)?.nama_mapel"></h5>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="selectAllClasses(mapelId)"
                                                    class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition border border-blue-200/60">
                                                Pilih Semua
                                            </button>
                                            <button type="button"
                                                    @click="clearAllClasses(mapelId)"
                                                    class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition border border-slate-200">
                                                Kosongkan
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Render Class Tags Container (Blue Pills with X, matching screenshot) -->
                                    <div class="p-2 min-h-[42px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-wrap gap-1.5 items-center">
                                        <!-- Active Class Tags -->
                                        <template x-for="classId in (kelasMapels[mapelId] || [])" :key="classId">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm">
                                                <span x-text="getKelas(classId)?.nama_kelas || classId"></span>
                                                <button type="button" @click="toggleClass(mapelId, classId)" class="hover:bg-blue-700 rounded-full p-0.5 transition" title="Hapus kelas">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <input type="hidden" :name="'kelasmapel_' + mapelId + '[]'" :value="classId">
                                            </span>
                                        </template>

                                        <!-- Add Class Picker Button -->
                                        <div class="relative" @click.outside="dropdownOpenClass[mapelId] = false">
                                            <button type="button"
                                                    @click="dropdownOpenClass[mapelId] = !dropdownOpenClass[mapelId]"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium border border-slate-200 dark:border-slate-700 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>Tambah Kelas</span>
                                            </button>

                                            <!-- Dropdown of Unselected Classes -->
                                            <div x-show="dropdownOpenClass[mapelId]" x-cloak
                                                 class="absolute z-10 top-full left-0 mt-1 w-48 max-h-48 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-1.5 space-y-1">
                                                <template x-for="kls in allKelases" :key="kls.id_kelas">
                                                    <div @click="toggleClass(mapelId, kls.id_kelas); dropdownOpenClass[mapelId] = false"
                                                         class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-800 cursor-pointer transition text-xs"
                                                         :class="isClassSelected(mapelId, kls.id_kelas) ? 'font-semibold text-blue-600 bg-blue-50/50' : 'text-slate-700 dark:text-slate-300'">
                                                        <span x-text="kls.nama_kelas"></span>
                                                        <span x-show="isClassSelected(mapelId, kls.id_kelas)">✓</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- List Ekstrakurikuler Classes -->
                            <template x-for="ekstraId in selectedEkstraIds" :key="'ekstra_' + ekstraId">
                                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-emerald-50/30 dark:bg-slate-950/20">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <h5 class="text-xs md:text-sm font-bold text-emerald-900 dark:text-emerald-300" x-text="getEkstra(ekstraId)?.nama_ekstra"></h5>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="selectAllEkstraClasses(ekstraId)"
                                                    class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 transition border border-emerald-200/60">
                                                Pilih Semua
                                            </button>
                                            <button type="button"
                                                    @click="clearAllEkstraClasses(ekstraId)"
                                                    class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition border border-slate-200">
                                                Kosongkan
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Render Ekstra Class Tags Container -->
                                    <div class="p-2 min-h-[42px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-wrap gap-1.5 items-center">
                                        <template x-for="classId in (kelasEkstras[ekstraId] || [])" :key="classId">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-600 text-white text-xs font-semibold shadow-sm">
                                                <span x-text="getKelas(classId)?.nama_kelas || classId"></span>
                                                <button type="button" @click="toggleEkstraClass(ekstraId, classId)" class="hover:bg-emerald-700 rounded-full p-0.5 transition" title="Hapus kelas">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <input type="hidden" :name="'kelasekstra_' + ekstraId + '[]'" :value="classId">
                                            </span>
                                        </template>

                                        <!-- Add Ekstra Class Picker -->
                                        <div class="relative" @click.outside="dropdownOpenClass['ekstra_' + ekstraId] = false">
                                            <button type="button"
                                                    @click="dropdownOpenClass['ekstra_' + ekstraId] = !dropdownOpenClass['ekstra_' + ekstraId]"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium border border-slate-200 dark:border-slate-700 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>Tambah Kelas</span>
                                            </button>

                                            <div x-show="dropdownOpenClass['ekstra_' + ekstraId]" x-cloak
                                                 class="absolute z-10 top-full left-0 mt-1 w-48 max-h-48 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-1.5 space-y-1">
                                                <template x-for="kls in allKelases" :key="kls.id_kelas">
                                                    <div @click="toggleEkstraClass(ekstraId, kls.id_kelas); dropdownOpenClass['ekstra_' + ekstraId] = false"
                                                         class="flex items-center justify-between p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-slate-800 cursor-pointer transition text-xs"
                                                         :class="isEkstraClassSelected(ekstraId, kls.id_kelas) ? 'font-semibold text-emerald-600 bg-emerald-50/50' : 'text-slate-700 dark:text-slate-300'">
                                                        <span x-text="kls.nama_kelas"></span>
                                                        <span x-show="isEkstraClassSelected(ekstraId, kls.id_kelas)">✓</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Jabatan -->
                <div class="lg:col-span-2">
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 sticky top-4 shadow-sm">
                        <!-- Jabatan Header with (+) Button -->
                        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <h4 class="text-sm md:text-base font-bold text-slate-900 dark:text-white">Jabatan</h4>
                            <button type="button"
                                    @click="showLevelModal = true"
                                    class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition"
                                    title="Tambah / Kelola Level Jabatan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>

                        <!-- Jabatan Body -->
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Jabatan</label>
                                <select name="level" x-model="level" class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs md:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-medium transition">
                                    @foreach($levels as $lvl)
                                        <option value="{{ $lvl->id_level }}">{{ $lvl->level }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- If Walikelas (level == 4) -->
                            <div x-show="level == 4" x-cloak class="animate-fade-in">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Wali Kelas Untuk</label>
                                <select name="kelas_wali" x-model="kelas_wali" class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs md:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelasList as $kls)
                                        <option value="{{ $kls->id_kelas }}">{{ $kls->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Summary Info -->
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 space-y-2 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500 dark:text-slate-400">Tahun Ajaran:</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $activeTp?->tahun ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500 dark:text-slate-400">Semester:</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $activeSmt?->nama_smt ?? $activeSmt?->smt ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500 dark:text-slate-400">Mapel Dipilih:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" x-text="selectedMapelIds.length + ' mapel'">0 mapel</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500 dark:text-slate-400">Ekstra Dipilih:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="selectedEkstraIds.length + ' ekstra'">0 ekstra</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button in Right Column -->
                            <div class="pt-2">
                                <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs md:text-sm font-semibold shadow-sm shadow-blue-600/30 transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Simpan Perubahan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal 1: Salin Jabatan Sebelumnya -->
    <div x-show="showBeforeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="showBeforeModal = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-2xl rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-title">
                        Jabatan Sebelumnya
                    </h3>
                    <button type="button" @click="showBeforeModal = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="py-4 space-y-4">
                    @if($jabatanBefore)
                        <div class="space-y-3">
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span>Periode Sebelumnya:</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $tpBefore?->tahun ?? '-' }} ({{ $smtBefore?->nama_smt ?? $smtBefore?->smt ?? '-' }})</span>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Jabatan:</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg inline-block">
                                    {{ $jabatanBefore->level?->level ?? 'Guru' }}
                                    @if($jabatanBefore->id_jabatan == 4 && $jabatanBefore->kelas)
                                        - Wali Kelas {{ $jabatanBefore->kelas->nama_kelas }}
                                    @endif
                                </span>
                            </div>

                            @php
                                $prevMapels = $jabatanBefore->parsed_mapel_kelas;
                                $prevEkstras = $jabatanBefore->parsed_ekstra_kelas;
                            @endphp

                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1.5 font-semibold">Pengampu Mata Pelajaran:</span>
                                @if(!empty($prevMapels))
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden text-xs">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-semibold text-slate-700 dark:text-slate-300">
                                                    <th class="p-2.5 text-center w-10">No.</th>
                                                    <th class="p-2.5">Mata Pelajaran</th>
                                                    <th class="p-2.5">Kelas</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                                @foreach($prevMapels as $idx => $pm)
                                                    <tr>
                                                        <td class="p-2.5 text-center text-slate-500">{{ $idx + 1 }}</td>
                                                        <td class="p-2.5 font-medium text-slate-900 dark:text-white">{{ $pm['nama_mapel'] ?? '-' }}</td>
                                                        <td class="p-2.5">
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach($pm['kelas_mapel'] ?? [] as $pkm)
                                                                    @php $klsId = is_array($pkm) ? ($pkm['kelas'] ?? null) : $pkm; @endphp
                                                                    <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[11px] font-semibold border border-blue-200/50">
                                                                        {{ $kelasMap[$klsId] ?? "Kls #{$klsId}" }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-xs text-slate-400 italic">Tidak ada mapel terdaftar di semester sebelumnya.</div>
                                @endif
                            </div>

                            @if(!empty($prevEkstras))
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1.5 font-semibold">Pengampu Ekstrakurikuler:</span>
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden text-xs">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-semibold text-slate-700 dark:text-slate-300">
                                                    <th class="p-2.5 text-center w-10">No.</th>
                                                    <th class="p-2.5">Ekstrakurikuler</th>
                                                    <th class="p-2.5">Kelas</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                                @foreach($prevEkstras as $idx => $pe)
                                                    <tr>
                                                        <td class="p-2.5 text-center text-slate-500">{{ $idx + 1 }}</td>
                                                        <td class="p-2.5 font-medium text-slate-900 dark:text-white">{{ $pe['nama_ekstra'] ?? '-' }}</td>
                                                        <td class="p-2.5">
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach($pe['kelas_ekstra'] ?? [] as $pke)
                                                                    @php $klsId = is_array($pke) ? ($pke['kelas'] ?? null) : $pke; @endphp
                                                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-[11px] font-semibold border border-emerald-200/50">
                                                                        {{ $kelasMap[$klsId] ?? "Kls #{$klsId}" }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300/80 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Tidak ada jabatan sebelumnya</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="showBeforeModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    @if($jabatanBefore)
                        <form action="{{ route('admin.master.guru.jabatan.copy', $guru->id_guru) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                <span>Salin ke Semester Sekarang</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Level Jabatan -->
    <div x-show="showLevelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-level" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="showLevelModal = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-2xl rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-level">
                        Level <b>Jabatan</b>
                    </h3>
                    <button type="button" @click="showLevelModal = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="py-4 space-y-4">
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 text-xs">
                        Nomor 1 sampai 5 <b>jangan diubah dan jangan diganti</b>.
                    </div>

                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden text-xs">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 font-semibold text-slate-700 dark:text-slate-300">
                                    <th class="p-2.5 text-center w-12">ID</th>
                                    <th class="p-2.5">Level Jabatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach($levels as $lvl)
                                    <tr>
                                        <td class="p-2.5 text-center font-mono text-slate-500">{{ $lvl->id_level }}</td>
                                        <td class="p-2.5 font-medium text-slate-900 dark:text-white">{{ $lvl->level }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <form action="{{ route('admin.master.guru.jabatan.level.store') }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tambah Level Baru:</label>
                            <div class="flex gap-2">
                                <input type="text" name="level" placeholder="Misal: Pembina OSIS..." required class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="showLevelModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection