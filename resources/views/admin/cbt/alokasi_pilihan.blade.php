@extends('layouts.admin')

@section('title', 'Alokasi Mapel Pilihan Fase F')
@section('page_title', 'Alokasi Mapel Pilihan (Kurikulum Merdeka Fase F)')

@section('content')
<div class="space-y-6" x-data="alokasiMatrixApp()">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[11px] font-bold tracking-wide rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    Kurikulum Merdeka • Fase F
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    T.P. {{ $activeTa->tahun ?? '2025/2026' }} - {{ $activeTa->semester_text ?? 'Semester Genap' }}
                </span>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mt-1">Matriks Alokasi Mapel Pilihan Siswa</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Plotting kombinasi 4-5 mata pelajaran pilihan per siswa kelas 11 & 12. Siswa hanya dapat melihat dan mengerjakan ujian mapel yang dipilihnya.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Download Template CSV -->
            <a href="{{ route('admin.cbt.alokasi.pilihan.template', ['kelas_id' => $selectedKelasId]) }}" 
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition flex items-center gap-2 border border-slate-200 dark:border-slate-700"
               title="Unduh format CSV berisi nama & NISN siswa kelas terpilih">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Format Excel/CSV</span>
            </a>

            <!-- Import Modal Trigger -->
            <button @click="openImportModal = true" 
                    type="button"
                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span>Impor CSV</span>
            </button>

            <!-- Submit Button (Direct from Form) -->
            @if($siswas->count() > 0)
            <button @click="submitMatrixForm()" 
                    type="button"
                    class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Matriks</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-sm font-bold">&times;</button>
    </div>
    @endif

    <!-- Filter & Control Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form id="filterForm" action="{{ route('admin.cbt.alokasi.pilihan') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Selector Kelas Rombel -->
            <div class="w-full sm:w-80">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pilih Rombel / Kelas Siswa:</label>
                <div class="relative">
                    <select name="kelas_id" onchange="this.form.submit()" 
                            class="w-full pl-3 pr-8 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-white focus:outline-none focus:border-brand-500 transition">
                        @foreach($kelasList as $k)
                            @php
                                $isFaseF = in_array($k->level_id, [11, 12]) || str_contains($k->nama_kelas, 'XI') || str_contains($k->nama_kelas, 'XII');
                            @endphp
                            <option value="{{ $k->id_kelas }}" {{ $selectedKelasId == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} {{ $isFaseF ? '★ (Fase F)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Search Filter Siswa & Info -->
            <div class="w-full sm:w-auto flex-1 flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 sm:pt-0">
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari siswa / NISN..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-brand-500 transition">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                @if($selectedKelas)
                <div class="text-right shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Total: <strong class="text-slate-800 dark:text-white">{{ $siswas->count() }}</strong> Siswa
                    </span>
                </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Matrix Checklist Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        @if($siswas->count() === 0)
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Tidak ada data siswa</h4>
                <p class="text-xs text-slate-400 mt-1">Pilih rombel kelas lain melalui pilihan filter di atas.</p>
            </div>
        @else
            <form id="matrixForm" action="{{ route('admin.cbt.alokasi.pilihan.save') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300 border-collapse">
                        <!-- Table Header -->
                        <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-600 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10 backdrop-blur">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center border-r border-slate-200 dark:border-slate-800">No</th>
                                <th class="py-3 px-4 w-32 border-r border-slate-200 dark:border-slate-800">NISN / Akun</th>
                                <th class="py-3 px-4 min-w-[200px] border-r border-slate-200 dark:border-slate-800">Nama Lengkap Siswa</th>
                                <th class="py-3 px-3 w-24 text-center border-r border-slate-200 dark:border-slate-800">Agama</th>
                                <th class="py-3 px-3 w-28 text-center border-r border-slate-200 dark:border-slate-800 bg-brand-50/40 dark:bg-brand-950/30 font-bold text-brand-700 dark:text-brand-300">
                                    Pilihan Siswa
                                </th>
                                
                                <!-- Dynamic Columns for Elective Subjects -->
                                @foreach($mapelPilihanList as $mapel)
                                <th class="py-3 px-3 text-center min-w-[100px] border-r border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                                    title="{{ $mapel->nama_mapel }}">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $mapel->kode }}</span>
                                        <span class="text-[9px] font-normal normal-case text-slate-400 dark:text-slate-500 truncate max-w-[90px]">{{ $mapel->nama_mapel }}</span>
                                        
                                        <!-- Column Toggle All Checkbox -->
                                        <button type="button" 
                                                @click="toggleColumn({{ $mapel->id_mapel }})"
                                                class="px-1.5 py-0.5 mt-0.5 text-[9px] font-semibold rounded bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition"
                                                title="Pilih / Batalkan semua siswa di kolom ini">
                                            Toggle
                                        </button>
                                    </div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($siswas as $idx => $s)
                            @php
                                $sAlloc = $matrixAllocations[$s->id_siswa] ?? [];
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                                x-show="matchesSearch('{{ strtolower($s->nama) }}', '{{ strtolower($s->nisn) }}', '{{ strtolower($s->username) }}')">
                                <!-- No -->
                                <td class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-800 text-slate-400 font-mono text-[11px]">
                                    {{ $loop->iteration }}
                                </td>

                                <!-- NISN / Akun -->
                                <td class="py-2.5 px-4 border-r border-slate-200 dark:border-slate-800 font-mono text-[11px] text-slate-600 dark:text-slate-400">
                                    {{ $s->nisn ?: $s->username }}
                                </td>

                                <!-- Nama Lengkap Siswa -->
                                <td class="py-2.5 px-4 border-r border-slate-200 dark:border-slate-800">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $s->nama }}
                                    </div>
                                </td>

                                <!-- Agama -->
                                <td class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-800">
                                    @php
                                        $ag = $s->agama ?: 'Islam';
                                        $agColor = match(strtolower($ag)) {
                                            'islam' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                                            'kristen' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
                                            'katolik' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800'
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full border {{ $agColor }}">
                                        {{ $ag }}
                                    </span>
                                </td>

                                <!-- Pilihan Counter Pill -->
                                <td class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-800 bg-brand-50/20 dark:bg-brand-950/10">
                                    <span class="inline-block px-2.5 py-1 text-[11px] font-bold rounded-lg transition"
                                          :class="getBadgeClass({{ $s->id_siswa }})">
                                        <span x-text="countStudentChoices({{ $s->id_siswa }})"></span> Mapel
                                    </span>
                                </td>

                                <!-- Checkbox per Mapel Pilihan -->
                                @foreach($mapelPilihanList as $mapel)
                                <td class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-800">
                                    <label class="inline-flex items-center justify-center cursor-pointer p-1 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/60 transition">
                                        <input type="checkbox" 
                                               name="pilihan[{{ $s->id_siswa }}][]" 
                                               value="{{ $mapel->id_mapel }}"
                                               x-model="matrix[{{ $s->id_siswa }}]"
                                               value="{{ $mapel->id_mapel }}"
                                               class="w-4 h-4 text-brand-600 bg-slate-100 border-slate-300 rounded focus:ring-brand-500 dark:focus:ring-brand-600 dark:ring-offset-slate-900 focus:ring-1 dark:bg-slate-950 dark:border-slate-700 cursor-pointer">
                                    </label>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Action Bar -->
                <div class="p-4 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-slate-700 dark:text-slate-300">Tips:</span> Siswa Kurikulum Merdeka Fase F umumnya memilih <strong>4 s.d. 5 mata pelajaran</strong> pilihan. Indikator hijau menandakan 4-5 mapel telah terpilih.
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Perubahan Matriks</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <!-- Modal Impor CSV -->
    <div x-show="openImportModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="openImportModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="openImportModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="openImportModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                
                <form action="{{ route('admin.cbt.alokasi.pilihan.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="target_kelas_id" value="{{ $selectedKelasId }}">

                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Impor Alokasi Mapel Pilihan (CSV)</span>
                            </h3>
                            <button type="button" @click="openImportModal = false" class="text-slate-400 hover:text-slate-500 text-lg font-bold">&times;</button>
                        </div>

                        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl p-3 text-xs text-amber-800 dark:text-amber-200 space-y-1">
                            <p class="font-bold">Format File CSV:</p>
                            <p>Kolom wajib: <code>NISN</code> dan <code>KODE_MAPEL_PILIHAN</code> (pisahkan dengan koma atau titik koma).</p>
                            <p class="text-[11px] text-amber-700 dark:text-amber-300">
                                Contoh: <code>BIO_TL; FIS_TL; KIM_TL; MAT_TL</code>
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Pilih File CSV:</label>
                            <input type="file" 
                                   name="file_csv" 
                                   accept=".csv,.txt" 
                                   required 
                                   class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 dark:file:bg-brand-950 dark:file:text-brand-300 hover:file:bg-brand-100 cursor-pointer border border-slate-200 dark:border-slate-800 rounded-xl p-1 bg-slate-50 dark:bg-slate-950">
                        </div>

                        <div class="pt-2 flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Belum punya format file?</span>
                            <a href="{{ route('admin.cbt.alokasi.pilihan.template', ['kelas_id' => $selectedKelasId]) }}" class="text-brand-600 dark:text-brand-400 hover:underline font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh Template Kelas Ini</span>
                            </a>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="button" @click="openImportModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span>Mulai Impor File</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Matrix App Script -->
<script>
function alokasiMatrixApp() {
    return {
        openImportModal: false,
        searchQuery: '',
        // Inisialisasi state matriks siswa: [siswaId] = [mapelId1, mapelId2, ...]
        matrix: {
            @foreach($siswas as $s)
                {{ $s->id_siswa }}: @json(array_map('strval', $matrixAllocations[$s->id_siswa] ?? [])),
            @endforeach
        },

        countStudentChoices(siswaId) {
            if (!this.matrix[siswaId]) return 0;
            return this.matrix[siswaId].length;
        },

        getBadgeClass(siswaId) {
            const count = this.countStudentChoices(siswaId);
            if (count >= 4 && count <= 5) {
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 font-bold';
            } else if (count > 5) {
                return 'bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 font-bold';
            } else if (count > 0) {
                return 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 font-semibold';
            } else {
                return 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400';
            }
        },

        toggleColumn(mapelId) {
            const mapelStr = String(mapelId);
            // Cek apakah sebagian besar sudah checked
            let allChecked = true;
            for (let sid in this.matrix) {
                if (!this.matrix[sid].includes(mapelStr)) {
                    allChecked = false;
                    break;
                }
            }

            for (let sid in this.matrix) {
                if (allChecked) {
                    // Hapus jika sebelumnya semua tercentang
                    this.matrix[sid] = this.matrix[sid].filter(id => id !== mapelStr);
                } else {
                    // Tambah jika belum ada
                    if (!this.matrix[sid].includes(mapelStr)) {
                        this.matrix[sid].push(mapelStr);
                    }
                }
            }
        },

        matchesSearch(nama, nisn, username) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            return (nama && nama.includes(q)) || (nisn && nisn.includes(q)) || (username && username.includes(q));
        },

        submitMatrixForm() {
            document.getElementById('matrixForm').submit();
        }
    };
}
</script>
@endsection
