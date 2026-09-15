@extends('layouts.admin')

@section('title', 'Kelas & Rombel')
@section('page_title', 'Kelas & Rombongan Belajar')

@section('content')
<div class="space-y-6" x-data="{ 
    openModalAdd: false, 
    openModalEdit: false, 
    openModalBulk: false,
    openModalDetail: false,
    openModalSync: false,
    detailLoading: false,
    detailData: { kelas: {}, siswas: [], total: 0, laki: 0, perempuan: 0 },
    detailSearch: '',
    editKelas: { id: '', level_id: '10', jurusan_id: '', kode_kelas: '', nama_kelas: '', guru_id: '' },
    bulk: {
        format_angka: 'romawi',
        level: '10',
        jumlah: 5,
        char_type: 'angka',
        awal: '1',
        nama_pola: 'Merdeka',
        jurusan_id: ''
    },
    get filteredDetailSiswas() {
        if (!this.detailSearch) return this.detailData.siswas;
        const q = this.detailSearch.toLowerCase();
        return (this.detailData.siswas || []).filter(s => 
            (s.nama && s.nama.toLowerCase().includes(q)) || 
            (s.nis && s.nis.includes(q)) || 
            (s.nisn && s.nisn.includes(q))
        );
    },
    generatePreview() {
        const roman = { 10: 'X', 11: 'XI', 12: 'XII', 7: 'VII', 8: 'VIII', 9: 'IX', 1: 'I', 2: 'II', 3: 'III', 4: 'IV', 5: 'V', 6: 'VI' };
        const lvl = parseInt(this.bulk.level) || 10;
        const lvlText = this.bulk.format_angka === 'romawi' ? (roman[lvl] || lvl) : lvl;
        const list = [];
        const count = Math.min(Math.max(parseInt(this.bulk.jumlah) || 1, 1), 20);
        const pola = (this.bulk.nama_pola || '').trim();

        if (this.bulk.char_type === 'angka') {
            const start = parseInt(this.bulk.awal) || 1;
            for (let i = 0; i < count; i++) {
                const num = start + i;
                const name = pola !== '' ? `${lvlText} ${pola} ${num}` : `${lvlText} ${num}`;
                const code = pola !== '' ? `${lvlText}-${pola.charAt(0).toUpperCase()}${num}` : `${lvlText}-${num}`;
                list.push({ nama: name, kode: code });
            }
        } else {
            const startChar = (this.bulk.awal || 'A').toUpperCase();
            const startCode = startChar.charCodeAt(0) || 65;
            for (let i = 0; i < count; i++) {
                const char = String.fromCharCode(startCode + i);
                const name = pola !== '' ? `${lvlText} ${pola} ${char}` : `${lvlText} ${char}`;
                const code = pola !== '' ? `${lvlText}-${char}` : `${lvlText}-${char}`;
                list.push({ nama: name, kode: code });
            }
        }
        return list;
    },
    openEdit(item) {
        this.editKelas = {
            id: item.id_kelas,
            level_id: item.level_id,
            jurusan_id: item.jurusan_id || '',
            kode_kelas: item.kode_kelas,
            nama_kelas: item.nama_kelas,
            guru_id: item.wali_guru_id || item.guru_id || ''
        };
        this.openModalEdit = true;
    },
    openDetail(item) {
        this.detailLoading = true;
        this.openModalDetail = true;
        this.detailSearch = '';
        this.detailData = { kelas: item, siswas: [], total: 0, laki: 0, perempuan: 0 };
        fetch('{{ url('/admin/master/kelas') }}/' + item.id_kelas + '/detail')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.detailData = data;
                }
                this.detailLoading = false;
            })
            .catch(() => {
                this.detailLoading = false;
            });
    }
}">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 dark:bg-brand-500/20 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold shrink-0 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Data Rombongan Belajar (Kelas)</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300/60 dark:border-emerald-800/40">
                        TP: {{ $activeTp->tahun ?? '2025/2026' }} Smt: {{ $activeSmt->nama_smt ?? 'II' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola data kelas, penetapan wali kelas, dan penyusunan anggota rombel siswa</p>
            </div>
        </div>

        <!-- Tombol Aksi Lengkap Sesuai US1 -->
        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            <!-- 1. Tambah Kelas Satuan -->
            <button 
                @click="openModalAdd = true" 
                type="button" 
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                title="Tambah Kelas Baru"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Kelas</span>
            </button>

            <!-- 2. Tambah Kelas Sekaligus (Bulk) -->
            <button 
                @click="openModalBulk = true" 
                type="button" 
                class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                title="Tambah Kelas Sekaligus"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>+ Sekaligus</span>
            </button>

            <!-- 3. Atur Kelas Semester / Kenaikan Kelas Sesuai Semester Aktif -->
            @if(($activeSmt->id_smt ?? 2) == 2)
                <button 
                    @click="openModalSync = true" 
                    type="button" 
                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                    title="Atur Kelas Semester"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Atur Kelas Semester</span>
                </button>
            @else
                <a 
                    href="{{ route('admin.master.kenaikan_kelas') }}" 
                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                    title="Kenaikan Kelas"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Kenaikan Kelas</span>
                </a>
            @endif

            <!-- 4. Reload -->
            <a 
                href="{{ route('admin.master.kelas') }}" 
                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5" 
                title="Muat Ulang Halaman"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reload</span>
            </a>
        </div>
    </div>

    <!-- Alert Edukatif / Penjelasan Fitur (Persis Seperti di US1 datakelas) -->
    <div class="bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-2xl p-4 text-xs text-blue-900 dark:text-blue-200">
        <div class="flex items-start gap-3">
            <span class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 shrink-0">ℹ️</span>
            <div class="space-y-1 leading-relaxed">
                <p>
                    <span class="px-2 py-0.5 rounded font-bold bg-emerald-600 text-white text-[10px]">+ Kelas</span> dan 
                    <span class="px-2 py-0.5 rounded font-bold bg-sky-600 text-white text-[10px]">+ Sekaligus</span> 
                    digunakan untuk membuat kelas baru jika kelas belum terdaftar pada tahun ajaran atau semester aktif saat ini.
                </p>
                <p>
                    Jika kelas sudah ada di Semester atau Tahun sebelumnya, Anda dapat menyalin data kelas & penempatan siswa secara praktis menggunakan tombol 
                    @if(($activeSmt->id_smt ?? 2) == 2)
                        <span class="px-2 py-0.5 rounded font-bold bg-indigo-600 text-white text-[10px]">Atur Kelas Semester</span> (menyalin seluruh data kelas dan anggota siswa dari Semester I ke Semester II).
                    @else
                        <span class="px-2 py-0.5 rounded font-bold bg-indigo-600 text-white text-[10px]">Kenaikan Kelas</span> (memproses promosi tingkat kelas siswa secara otomatis ke tahun pelajaran baru).
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3.5">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Total Rombel</p>
            <h4 class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total'] ?? 0 }} <span class="text-xs font-normal text-slate-400">Kelas</span></h4>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm">
            <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-400">Fase E (Kelas 10)</p>
            <h4 class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['l10'] ?? 0 }} <span class="text-xs font-normal text-slate-400">Kelas</span></h4>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm">
            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">Fase F (Kelas 11)</p>
            <h4 class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['l11'] ?? 0 }} <span class="text-xs font-normal text-slate-400">Kelas</span></h4>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm">
            <p class="text-[11px] font-semibold text-purple-600 dark:text-purple-400">Tingkat Akhir (12)</p>
            <h4 class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['l12'] ?? 0 }} <span class="text-xs font-normal text-slate-400">Kelas</span></h4>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm">
            <p class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Total Siswa Riil</p>
            <h4 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['total_siswa'] ?? 0 }} <span class="text-xs font-normal text-slate-400">Siswa</span></h4>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 shadow-sm">
            <p class="text-[11px] font-semibold text-pink-600 dark:text-pink-400">Wali Kelas Terisi</p>
            <h4 class="text-xl font-black text-pink-600 dark:text-pink-400 mt-1">{{ $stats['wali_count'] ?? 0 }} <span class="text-xs font-normal text-slate-400">/ {{ $stats['total'] ?? 0 }}</span></h4>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('admin.master.kelas') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-1 flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas, kode, atau wali kelas..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Dropdown Tingkat -->
                <select name="level_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-brand-500">
                    <option value="">Semua Tingkat</option>
                    <option value="10" {{ request('level_id') == '10' ? 'selected' : '' }}>Kelas 10 (Fase E)</option>
                    <option value="11" {{ request('level_id') == '11' ? 'selected' : '' }}>Kelas 11 (Fase F)</option>
                    <option value="12" {{ request('level_id') == '12' ? 'selected' : '' }}>Kelas 12 (Tingkat Akhir)</option>
                </select>

                <!-- Dropdown Jurusan -->
                @if(isset($jurusanList) && count($jurusanList) > 0)
                    <select name="jurusan_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-brand-500">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $jur)
                            <option value="{{ $jur->id_jurusan }}" {{ request('jurusan_id') == $jur->id_jurusan ? 'selected' : '' }}>{{ $jur->nama_jurusan }}</option>
                        @endforeach
                    </select>
                @endif

                <!-- Filter Status Wali Kelas -->
                <select name="status_wali" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-brand-500">
                    <option value="">Status Wali Kelas</option>
                    <option value="terisi" {{ request('status_wali') == 'terisi' ? 'selected' : '' }}>Sudah Ada Wali Kelas</option>
                    <option value="kosong" {{ request('status_wali') == 'kosong' ? 'selected' : '' }}>Belum Ditentukan</option>
                </select>

                <button type="submit" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">Cari</button>

                @if(request()->filled('q') || request()->filled('level_id') || request()->filled('jurusan_id') || request()->filled('status_wali'))
                    <a href="{{ route('admin.master.kelas') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400 shrink-0">
                Menampilkan <strong class="text-slate-800 dark:text-white">{{ $kelasList->total() }}</strong> kelas
            </div>
        </form>
    </div>

    <!-- Table Card: Data Kelas Mirip Persis Format US1 -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center font-bold">No.</th>
                        <th class="py-3 px-4 min-w-[180px] font-bold">Nama Kelas</th>
                        <th class="py-3 px-4 w-36 font-bold">Jurusan</th>
                        <th class="py-3 px-4 w-28 text-center font-bold">Jumlah Siswa</th>
                        <th class="py-3 px-4 min-w-[220px] font-bold">Wali Kelas</th>
                        <th class="py-3 px-4 w-28 text-center font-bold">Import Siswa</th>
                        <th class="py-3 px-4 w-32 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($kelasList as $idx => $kelas)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition group">
                            <!-- 1. No -->
                            <td class="py-3 px-3 text-center text-slate-400 font-medium">
                                {{ $kelasList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $kelasList->firstItem() + $idx : $idx + 1 }}
                            </td>

                            <!-- 2. Nama Kelas & Kode -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <span class="font-mono text-[11px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/80 px-2 py-0.5 rounded border border-brand-200/80 dark:border-brand-800/50">
                                        {{ $kelas->kode_kelas }}
                                    </span>
                                    <span class="text-sm">{{ $kelas->nama_kelas }}</span>
                                </div>
                            </td>

                            <!-- 3. Jurusan / Peminatan -->
                            <td class="py-3 px-4">
                                @if(!empty($kelas->nama_jurusan))
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $kelas->nama_jurusan }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs italic">Umum / Semua</span>
                                @endif
                            </td>

                            <!-- 4. Jumlah Siswa Riil -->
                            <td class="py-3 px-4 text-center">
                                <button 
                                    @click="openDetail({{ json_encode($kelas) }})" 
                                    type="button" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50 hover:bg-blue-100 transition"
                                    title="Klik untuk melihat daftar siswa kelas ini"
                                >
                                    <span>{{ $kelas->jml_siswa ?? 0 }}</span>
                                    <span class="text-[10px] font-normal">Siswa</span>
                                </button>
                            </td>

                            <!-- 5. Wali Kelas -->
                            <td class="py-3 px-4">
                                @if(!empty($kelas->nama_wali))
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-950/80 border border-emerald-300/60 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($kelas->nama_wali, 0, 1)) }}
                                        </div>
                                        <div class="truncate">
                                            <div class="font-bold text-slate-800 dark:text-white truncate">{{ $kelas->nama_wali }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">NIP: {{ $kelas->nip_wali ?? '-' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-900/50">
                                        ⚠️ Belum Ditentukan
                                    </span>
                                @endif
                            </td>

                            <!-- 6. Import Siswa (Shortcut Langsung ke Data Siswa Kelas Tersebut) -->
                            <td class="py-3 px-4 text-center">
                                <a 
                                    href="{{ route('admin.master.siswa') }}?kelas_id={{ $kelas->id_kelas }}" 
                                    class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-emerald-950/40 text-slate-600 hover:text-emerald-600 dark:text-slate-300 dark:hover:text-emerald-400 border border-slate-200 dark:border-slate-700 transition"
                                    title="Kelola & Import Siswa untuk {{ $kelas->nama_kelas }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                </a>
                            </td>

                            <!-- 7. Aksi (Detail, Edit, Hapus) -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <!-- Tombol Detail -->
                                    <button 
                                        @click="openDetail({{ json_encode($kelas) }})" 
                                        type="button" 
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800/50 transition shadow-xs" 
                                        title="Lihat Detail Kelas & Roster Siswa"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>

                                    <!-- Tombol Edit -->
                                    <button 
                                        @click="openEdit({{ json_encode($kelas) }})" 
                                        type="button" 
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800/50 transition shadow-xs" 
                                        title="Edit Kelas & Wali Kelas"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.master.kelas.destroy', $kelas->id_kelas) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas {{ $kelas->nama_kelas }}? Seluruh penempatan siswa di kelas ini akan terlepas.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800/50 transition shadow-xs" title="Hapus Kelas">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Tidak ada data kelas yang cocok dengan filter pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kelasList instanceof \Illuminate\Pagination\LengthAwarePaginator && $kelasList->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $kelasList->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: Tambah Kelas Tunggal (Dengan Pilihan Wali Kelas) -->
    <div x-show="openModalAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="openModalAdd = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Kelas Baru</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Masukkan data rombel dan tentukan wali kelas</p>
                </div>
                <button @click="openModalAdd = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form action="{{ route('admin.master.kelas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tingkat Level *</label>
                        <select name="level_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="10">Kelas 10 (Fase E)</option>
                            <option value="11">Kelas 11 (Fase F)</option>
                            <option value="12">Kelas 12 (Tingkat Akhir)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jurusan / Peminatan</label>
                        <select name="jurusan_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="">-- Tanpa Jurusan (Umum) --</option>
                            @foreach($jurusanList as $jur)
                                <option value="{{ $jur->id_jurusan }}">{{ $jur->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Kelas *</label>
                        <input type="text" name="kode_kelas" placeholder="Contoh: X-M1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kelas *</label>
                        <input type="text" name="nama_kelas" placeholder="Contoh: X Merdeka 1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tugaskan Wali Kelas (Opsional)</label>
                    <select name="guru_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">-- Pilih Guru Wali Kelas --</option>
                        @foreach($guruList as $g)
                            <option value="{{ $g->id_guru }}">{{ $g->nama_guru }} (NIP: {{ $g->nip ?? '-' }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Wali kelas akan otomatis disinkronkan ke jabatan guru pada semester ini.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalAdd = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 transition">
                        Simpan Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Kelas (Dengan Pilihan Ganti Wali Kelas) -->
    <div x-show="openModalEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="openModalEdit = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Data Kelas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui identitas kelas dan penetapan wali kelas</p>
                </div>
                <button @click="openModalEdit = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form :action="'{{ url('/admin/master/kelas') }}/' + editKelas.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tingkat Level *</label>
                        <select name="level_id" x-model="editKelas.level_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="10">Kelas 10 (Fase E)</option>
                            <option value="11">Kelas 11 (Fase F)</option>
                            <option value="12">Kelas 12 (Tingkat Akhir)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jurusan / Peminatan</label>
                        <select name="jurusan_id" x-model="editKelas.jurusan_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="">-- Tanpa Jurusan (Umum) --</option>
                            @foreach($jurusanList as $jur)
                                <option value="{{ $jur->id_jurusan }}">{{ $jur->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Kelas *</label>
                        <input type="text" name="kode_kelas" x-model="editKelas.kode_kelas" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kelas *</label>
                        <input type="text" name="nama_kelas" x-model="editKelas.nama_kelas" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wali Kelas</label>
                    <select name="guru_id" x-model="editKelas.guru_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="0">-- Tanpa Wali Kelas --</option>
                        @foreach($guruList as $g)
                            <option value="{{ $g->id_guru }}">{{ $g->nama_guru }} (NIP: {{ $g->nip ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalEdit = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-600/30 transition">
                        Perbarui Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Tambah Kelas Sekaligus (Bulk Modal Seperti Pada US1) -->
    <div x-show="openModalBulk" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="openModalBulk = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-xl p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">⚡</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Kelas Sekaligus (Bulk)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Generate banyak rombel secara otomatis dengan pola penamaan terstruktur</p>
                    </div>
                </div>
                <button @click="openModalBulk = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.kelas.bulk') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Format Tingkat *</label>
                        <select name="format_angka" x-model="bulk.format_angka" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="romawi">Angka Romawi (X, XI, XII)</option>
                            <option value="biasa">Angka Biasa (10, 11, 12)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tingkat Level *</label>
                        <select name="level" x-model="bulk.level" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="10">Kelas 10 (Fase E)</option>
                            <option value="11">Kelas 11 (Fase F)</option>
                            <option value="12">Kelas 12 (Tingkat Akhir)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pola Nama Tengah</label>
                        <input type="text" name="nama_pola" x-model="bulk.nama_pola" placeholder="Misal: Merdeka / MIPA" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Penomoran Kelas *</label>
                        <select name="char_type" x-model="bulk.char_type" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="angka">Angka (1, 2, 3...)</option>
                            <option value="huruf">Huruf (A, B, C...)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Dari *</label>
                        <template x-if="bulk.char_type === 'angka'">
                            <input type="number" name="awal" x-model="bulk.awal" min="1" max="20" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </template>
                        <template x-if="bulk.char_type === 'huruf'">
                            <input type="text" name="awal" x-model="bulk.awal" maxlength="1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Rombel yang Dibuat *</label>
                        <input type="number" name="jumlah" x-model="bulk.jumlah" min="1" max="20" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Peminatan / Jurusan</label>
                        <select name="jurusan_id" x-model="bulk.jurusan_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="">-- Tanpa Jurusan (Umum) --</option>
                            @foreach($jurusanList as $jur)
                                <option value="{{ $jur->id_jurusan }}">{{ $jur->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Live Preview Hasil (Seperti di US1 datakelas) -->
                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Preview Hasil Generate:</span>
                        <span class="text-[10px] text-slate-400" x-text="generatePreview().length + ' rombel akan dibuat'"></span>
                    </div>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-1">
                        <template x-for="item in generatePreview()" :key="item.kode">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs shadow-2xs">
                                <span class="font-mono text-[10px] font-bold text-brand-600 dark:text-brand-400" x-text="item.kode"></span>
                                <span class="text-slate-700 dark:text-slate-200 font-semibold" x-text="item.nama"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalBulk = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold shadow-md shadow-sky-600/30 transition">
                        Simpan Sekaligus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: Detail Kelas & Roster Siswa (Sesuai Fitur Detail US1) -->
    <div x-show="openModalDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="openModalDetail = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-3xl p-6 shadow-2xl max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="detailData.kelas?.nama_kelas || 'Detail Kelas'"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Wali Kelas: <strong class="text-slate-800 dark:text-slate-200" x-text="detailData.kelas?.nama_wali || 'Belum Ditentukan'"></strong> &bull;
                            Jurusan: <span x-text="detailData.kelas?.nama_jurusan || 'Umum'"></span>
                        </p>
                    </div>
                </div>
                <button @click="openModalDetail = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Stats Rombel Kecil -->
            <div class="grid grid-cols-3 gap-3 py-3 shrink-0">
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Total Siswa</span>
                    <div class="text-lg font-black text-slate-900 dark:text-white" x-text="detailData.total || 0"></div>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/50 text-center">
                    <span class="text-[10px] uppercase font-bold text-blue-600 dark:text-blue-400">Laki-Laki</span>
                    <div class="text-lg font-black text-blue-600 dark:text-blue-400" x-text="detailData.laki || 0"></div>
                </div>
                <div class="p-2.5 rounded-xl bg-pink-50 dark:bg-pink-950/40 border border-pink-200 dark:border-pink-900/50 text-center">
                    <span class="text-[10px] uppercase font-bold text-pink-600 dark:text-pink-400">Perempuan</span>
                    <div class="text-lg font-black text-pink-600 dark:text-pink-400" x-text="detailData.perempuan || 0"></div>
                </div>
            </div>

            <!-- Search Siswa di dalam Kelas -->
            <div class="py-2 shrink-0">
                <input type="text" x-model="detailSearch" placeholder="Cari nama, NIS, atau NISN siswa..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
            </div>

            <!-- Roster Table -->
            <div class="flex-1 overflow-y-auto mt-2 border border-slate-200 dark:border-slate-800 rounded-xl">
                <div x-show="detailLoading" class="py-12 text-center text-xs text-slate-400">
                    <span class="inline-block animate-spin mr-1">⏳</span> Memuat data siswa...
                </div>

                <table x-show="!detailLoading" class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold sticky top-0 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 w-28">NIS / NISN</th>
                            <th class="py-2.5 px-3 min-w-[160px]">Nama Lengkap</th>
                            <th class="py-2.5 px-3 w-16 text-center">L/P</th>
                            <th class="py-2.5 px-3 w-24">Agama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <template x-for="(siswa, sIdx) in filteredDetailSiswas" :key="siswa.id_siswa">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-2.5 px-3 text-center text-slate-400" x-text="sIdx + 1"></td>
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300" x-text="siswa.nis || siswa.nisn || '-'"></td>
                                <td class="py-2.5 px-3 font-bold text-slate-800 dark:text-white" x-text="siswa.nama"></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span :class="siswa.jenis_kelamin === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300'" class="px-2 py-0.5 rounded text-[10px] font-bold" x-text="siswa.jenis_kelamin || '-'"></span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400" x-text="siswa.agama || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="!filteredDetailSiswas || filteredDetailSiswas.length === 0">
                            <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                Belum ada siswa terdaftar di kelas ini atau tidak cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800 mt-3 shrink-0">
                <a :href="'{{ route('admin.master.siswa') }}?kelas_id=' + detailData.kelas?.id_kelas" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                    Kelola Rombel & Import Siswa di Halaman Siswa &rarr;
                </a>
                <button type="button" @click="openModalDetail = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 5: Atur Kelas Semester (Copy Dari SMT 1 ke SMT 2) -->
    <div x-show="openModalSync" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="openModalSync = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Atur Kelas Semester</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Salin kelas dan penempatan siswa</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Fitur ini akan menyalin seluruh daftar kelas beserta penempatan siswa dan penetapan wali kelas dari <strong>Semester I</strong> ke <strong>Semester II</strong> pada Tahun Pelajaran aktif ({{ $activeTp->tahun ?? '2025/2026' }}).
            </p>

            <form action="{{ route('admin.master.kelas.sync_semester') }}" method="POST" class="mt-5">
                @csrf
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModalSync = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 transition">
                        Konfirmasi & Salin Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
