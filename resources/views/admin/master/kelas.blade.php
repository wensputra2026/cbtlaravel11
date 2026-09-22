@extends('layouts.admin')

@section('title', 'Kelas & Rombel')
@section('page_title', 'Kelas & Rombongan Belajar')

@section('content')
<div class="space-y-5" x-data="{
    // Filter State
    searchQuery: '',
    filterLevel: '',
    filterJurusan: '',

    // Modal State
    openModal: false,
    isEdit: false,
    actionUrl: '{{ route('admin.master.kelas.store') }}',
    form: {
        id_kelas: '',
        level_id: '10',
        jurusan_id: '',
        kode_kelas: '',
        nama_kelas: '',
        guru_id: '0'
    },

    openAddModal() {
        this.isEdit = false;
        this.actionUrl = '{{ route('admin.master.kelas.store') }}';
        this.form = {
            id_kelas: '',
            level_id: '10',
            jurusan_id: '{{ $jurusanList->first()?->id_jurusan ?? '' }}',
            kode_kelas: '',
            nama_kelas: '',
            guru_id: '0'
        };
        this.openModal = true;
    },

    openEditModal(item) {
        this.isEdit = true;
        this.actionUrl = '{{ url('admin/master/kelas/update') }}/' + item.id_kelas;
        this.form = {
            id_kelas: item.id_kelas,
            level_id: String(item.level_id || '10'),
            jurusan_id: String(item.jurusan_id || ''),
            kode_kelas: item.kode_kelas,
            nama_kelas: item.nama_kelas,
            guru_id: String(item.guru_id || '0')
        };
        this.openModal = true;
    },

    confirmDeleteKelas(id, nama) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Rombel?',
                text: 'Apakah Anda yakin ingin menghapus kelas "' + nama + '"? Aksi ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa fa-trash-alt mr-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.getElementById('deleteKelasForm');
                    form.action = '{{ url("admin/master/kelas/delete") }}/' + id;
                    form.submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus kelas "' + nama + '"?')) {
                var form = document.getElementById('deleteKelasForm');
                form.action = '{{ url("admin/master/kelas/delete") }}/' + id;
                form.submit();
            }
        }
    },

    // Check visibility based on filters
    rowMatches(level, jurusanId, nama, kode) {
        const q = this.searchQuery.toLowerCase();
        const matchesSearch = !q || nama.toLowerCase().includes(q) || kode.toLowerCase().includes(q);
        const matchesLevel = !this.filterLevel || String(level) === String(this.filterLevel);
        const matchesJurusan = !this.filterJurusan || String(jurusanId) === String(this.filterJurusan);
        return matchesSearch && matchesLevel && matchesJurusan;
    }
}">

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    <!-- Header Actions Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Daftar Kelas & Rombongan Belajar</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                <span>Tahun Pelajaran: <strong class="text-slate-700">{{ $tp_active?->tahun ?? '-' }}</strong></span>
                <span>&bull;</span>
                <span>Semester: <strong class="text-slate-700">{{ $smt_active?->smt ?? '-' }}</strong></span>
                <span>&bull;</span>
                <span>Total: <strong class="text-indigo-600 font-bold">{{ count($kelasList) }} Rombel</strong></span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="location.reload()" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-slate-300 shadow-xs">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Muat Ulang</span>
            </button>
            <a href="{{ route('admin.master.kenaikan_kelas') }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-indigo-200 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Kenaikan Kelas</span>
            </a>
            <button type="button" @click="openAddModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kelas</span>
            </button>
        </div>
    </div>

    <!-- Alert Edukasi / Petunjuk Garuda CBT -->
    <div class="flex items-start gap-3 p-3.5 bg-blue-50/80 border border-blue-200 rounded-xl text-xs text-blue-900 shadow-xs">
        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="leading-relaxed">
            <strong>Petunjuk Pengelolaan Rombel:</strong> Data kelas di bawah ini terhubung langsung dengan alokasi peserta ujian CBT, bank soal, dan kenaikan kelas. Anda dapat mengedit nama kelas, kode rombel, jurusan, serta menetapkan guru wali kelas melalui tombol <strong>Edit</strong>.
        </div>
    </div>

    <!-- Main Card & Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" x-model="searchQuery" placeholder="Cari nama kelas atau kode rombel..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xs">
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Filter Tingkat -->
                <select x-model="filterLevel" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-indigo-500 shadow-xs">
                    <option value="">Semua Tingkat (Level)</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>

                <!-- Filter Jurusan -->
                <select x-model="filterJurusan" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-indigo-500 shadow-xs">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusanList as $jur)
                        <option value="{{ $jur->id_jurusan }}">{{ $jur->nama_jurusan }} ({{ $jur->kode_jurusan }})</option>
                    @endforeach
                </select>

                <!-- Reset Filter -->
                <button type="button" @click="searchQuery = ''; filterLevel = ''; filterJurusan = ''" x-show="searchQuery || filterLevel || filterJurusan" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 font-bold underline" title="Reset filter">
                    Reset
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-100/80 text-slate-700 uppercase text-[11px] tracking-wider border-b border-slate-200 font-bold select-none">
                    <tr>
                        <th class="py-3 px-3 text-center w-12 border-r border-slate-200">No</th>
                        <th class="py-3 px-3 text-center w-32 border-r border-slate-200 whitespace-nowrap">Tingkat / Level</th>
                        <th class="py-3 px-3 text-center w-28 border-r border-slate-200 whitespace-nowrap">Kode Kelas</th>
                        <th class="py-3 px-4 border-r border-slate-200">Nama Kelas & Rombel</th>
                        <th class="py-3 px-3 text-center w-32 border-r border-slate-200">Jurusan</th>
                        <th class="py-3 px-4 border-r border-slate-200">Wali Kelas</th>
                        <th class="py-3 px-3 text-center w-28 border-r border-slate-200 whitespace-nowrap">Jumlah Siswa</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($kelasList as $idx => $kelas)
                        <tr x-show="rowMatches({{ $kelas->level_id }}, '{{ $kelas->jurusan_id }}', '{{ addslashes($kelas->nama_kelas) }}', '{{ addslashes($kelas->kode_kelas) }}')" 
                            class="hover:bg-indigo-50/30 transition border-b border-slate-200 odd:bg-white even:bg-slate-50/40">
                            
                            <!-- No -->
                            <td class="py-3 px-3 text-center text-slate-500 font-medium border-r border-slate-200">
                                {{ $idx + 1 }}
                            </td>

                            <!-- Tingkat / Level -->
                            <td class="py-3 px-3 text-center border-r border-slate-200 whitespace-nowrap">
                                @if($kelas->level_id == 10)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs whitespace-nowrap">
                                        Kelas 10 (X)
                                    </span>
                                @elseif($kelas->level_id == 11)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs whitespace-nowrap">
                                        Kelas 11 (XI)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs whitespace-nowrap">
                                        Kelas 12 (XII)
                                    </span>
                                @endif
                            </td>

                            <!-- Kode Kelas -->
                            <td class="py-3 px-3 text-center font-mono font-bold text-indigo-700 border-r border-slate-200 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 rounded-md">
                                    {{ $kelas->kode_kelas }}
                                </span>
                            </td>

                            <!-- Nama Kelas -->
                            <td class="py-3 px-4 border-r border-slate-200">
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $kelas->nama_kelas }}
                                </div>
                            </td>

                            <!-- Jurusan -->
                            <td class="py-3 px-3 text-center border-r border-slate-200">
                                @if($kelas->jurusan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $kelas->jurusan->nama_jurusan }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-xs">Umum</span>
                                @endif
                            </td>

                            <!-- Wali Kelas -->
                            <td class="py-3 px-4 border-r border-slate-200">
                                @if($kelas->waliKelas)
                                    <div class="font-medium text-slate-800 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>{{ $kelas->waliKelas->nama_guru }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">- Belum ditentukan -</span>
                                @endif
                            </td>

                            <!-- Jumlah Siswa -->
                            <td class="py-3 px-3 text-center border-r border-slate-200 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs whitespace-nowrap">
                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>{{ $kelas->jumlah_siswa_count }} Siswa</span>
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-3 text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($kelas) }})" 
                                            class="w-7 h-7 inline-flex items-center justify-center text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded-lg transition shadow-2xs" 
                                            title="Edit Rombel">
                                        <i class="fa fa-pencil-alt text-xs"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            @click="confirmDeleteKelas({{ $kelas->id_kelas }}, '{{ addslashes($kelas->nama_kelas) }}')" 
                                            class="w-7 h-7 inline-flex items-center justify-center text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-300 rounded-lg transition shadow-2xs" 
                                            title="Hapus Rombel">
                                        <i class="fa fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">Belum ada data kelas / rombongan belajar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Tambah / Edit Kelas -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-800" x-text="isEdit ? 'Edit Rombongan Belajar' : 'Tambah Kelas Baru'"></h3>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
            </div>
            
            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat / Level*</label>
                        <select name="level_id" x-model="form.level_id" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xs">
                            <option value="10">Kelas 10 (X)</option>
                            <option value="11">Kelas 11 (XI)</option>
                            <option value="12">Kelas 12 (XII)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan</label>
                        <select name="jurusan_id" x-model="form.jurusan_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xs">
                            <option value="">-- Tanpa Jurusan (Umum) --</option>
                            @foreach($jurusanList as $jur)
                                <option value="{{ $jur->id_jurusan }}">{{ $jur->nama_jurusan }} ({{ $jur->kode_jurusan }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Kelas*</label>
                        <input type="text" name="kode_kelas" x-model="form.kode_kelas" placeholder="Contoh: X-M1, XI-IPA-1" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs uppercase font-mono text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kelas*</label>
                        <input type="text" name="nama_kelas" x-model="form.nama_kelas" placeholder="Contoh: X Merdeka 1, XI MIPA 1" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Wali Kelas</label>
                    <select name="guru_id" x-model="form.guru_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xs">
                        <option value="0">-- Belum Ditentukan --</option>
                        @foreach($guruList as $guru)
                            <option value="{{ $guru->id_guru }}">{{ $guru->nama_guru }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form for SweetAlert2 -->
    <form id="deleteKelasForm" method="POST" style="display: none;">
        @csrf
    </form>

</div>
@endsection
