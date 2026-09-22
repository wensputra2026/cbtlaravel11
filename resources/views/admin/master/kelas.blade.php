@extends('layouts.admin')

@section('title', 'Kelas & Rombel')
@section('page_title', 'Kelas & Rombongan Belajar')

@section('content')
<div class="space-y-6" x-data="{
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
    }
}">

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    <!-- Header Actions -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Daftar Kelas & Rombongan Belajar</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Tahun Pelajaran: <strong class="text-slate-800">{{ $tp_active?->tahun ?? '-' }}</strong> &bull; Semester: <strong class="text-slate-800">{{ $smt_active?->smt ?? '-' }}</strong>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="location.reload()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reload</span>
            </button>
            <a href="{{ route('admin.master.kenaikan_kelas') }}" class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-indigo-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span>Kenaikan Kelas</span>
            </a>
            <button type="button" @click="openAddModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kelas</span>
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[11px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3 text-center w-28">Tingkat / Level</th>
                        <th class="py-3 px-4 text-center w-28">Kode Kelas</th>
                        <th class="py-3 px-4">Nama Kelas</th>
                        <th class="py-3 px-4 text-center w-28">Jurusan</th>
                        <th class="py-3 px-4">Wali Kelas</th>
                        <th class="py-3 px-3 text-center w-24">Jumlah Siswa</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kelasList as $idx => $kelas)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3 text-center text-slate-500 font-medium">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3 text-center">
                                @if($kelas->level_id == 10)
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Kelas 10 (X)
                                    </span>
                                @elseif($kelas->level_id == 11)
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Kelas 11 (XI)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Kelas 12 (XII)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-indigo-700">
                                {{ $kelas->kode_kelas }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800 text-sm">
                                {{ $kelas->nama_kelas }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $kelas->jurusan->nama_jurusan ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ $kelas->waliKelas->nama_guru ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $kelas->jumlah_siswa_count }} Siswa
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEditModal({{ json_encode($kelas) }})" class="p-1.5 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition" title="Edit Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.master.kelas.destroy', $kelas->id_kelas) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas {{ $kelas->nama_kelas }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-lg border border-slate-200 transition" title="Hapus Kelas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">Belum ada data kelas / rombel.</td>
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
                <h3 class="text-sm font-bold text-slate-900" x-text="isEdit ? 'Edit Kelas & Rombel' : 'Tambah Kelas Baru'"></h3>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>
            
            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat / Level*</label>
                        <select name="level_id" x-model="form.level_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
                            <option value="10">Kelas 10 (X)</option>
                            <option value="11">Kelas 11 (XI)</option>
                            <option value="12">Kelas 12 (XII)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan</label>
                        <select name="jurusan_id" x-model="form.jurusan_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
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
                        <input type="text" name="kode_kelas" x-model="form.kode_kelas" placeholder="Contoh: X-M1, XI-IPA-1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs uppercase font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kelas*</label>
                        <input type="text" name="nama_kelas" x-model="form.nama_kelas" placeholder="Contoh: X Merdeka 1, XI MIPA 1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Wali Kelas</label>
                    <select name="guru_id" x-model="form.guru_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
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

</div>
@endsection
