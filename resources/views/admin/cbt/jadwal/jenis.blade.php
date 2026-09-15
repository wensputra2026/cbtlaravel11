@extends('layouts.admin')

@section('title', 'Jenis Ujian')
@section('page_title', 'Master Jenis Ujian CBT')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, openEditModal: false, editId: null, editKode: '', editNama: '' }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.cbt.jadwal.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Jadwal Ujian
                </a>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Jenis Penilaian & Asesmen</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Klasifikasi asesmen seperti PAS, PTS, PAT, USBK, Penilaian Harian, Simulasi, dll.</p>
        </div>
        <div>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Jenis Ujian</span>
            </button>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form action="{{ route('admin.cbt.jenis.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-80">
            <div class="relative w-full">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode jenis ujian..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if(request('q'))
                <a href="{{ route('admin.cbt.jenis.index') }}" class="p-2 text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
        <span class="text-xs text-slate-500 dark:text-slate-400">Total: <strong class="text-slate-800 dark:text-white">{{ $jenisList->total() }}</strong> Jenis Ujian</span>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Kode Asesmen</th>
                        <th class="py-3 px-4">Nama Jenis Asesmen</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($jenisList as $idx => $jen)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $jenisList->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30">
                                    {{ $jen->kode_jenis }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">{{ $jen->nama_jenis }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                    Aktif
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="editId = {{ $jen->id_jenis }}; editKode = '{{ addslashes($jen->kode_jenis) }}'; editNama = '{{ addslashes($jen->nama_jenis) }}'; openEditModal = true" class="p-1.5 text-brand-600 hover:text-brand-700 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-900/30 rounded-lg transition" title="Edit Jenis Ujian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.cbt.jenis.destroy', $jen->id_jenis) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus jenis ujian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-lg transition" title="Hapus Jenis">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data jenis ujian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jenisList->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $jenisList->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Jenis -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tambah Jenis Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Masukkan kode singkatan dan nama lengkap jenis ujian</p>
            
            <form action="{{ route('admin.cbt.jenis.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Asesmen</label>
                    <input type="text" name="kode_jenis" placeholder="PAS / PTS / USBK / PH" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Jenis Asesmen</label>
                    <input type="text" name="nama_jenis" placeholder="Penilaian Akhir Semester" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Simpan Jenis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Jenis -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Edit Jenis Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Ubah kode singkatan atau nama jenis ujian</p>
            
            <form :action="'{{ url('/admin/cbt/jenis') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Asesmen</label>
                    <input type="text" name="kode_jenis" x-model="editKode" placeholder="PAS / PTS / USBK / PH" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Jenis Asesmen</label>
                    <input type="text" name="nama_jenis" x-model="editNama" placeholder="Penilaian Akhir Semester" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Perbarui Jenis
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
