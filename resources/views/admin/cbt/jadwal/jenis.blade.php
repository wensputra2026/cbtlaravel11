@extends('layouts.admin')

@section('title', 'Jenis Ujian')
@section('page_title', 'Master Jenis Ujian CBT')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.cbt.jadwal.index') }}" class="text-xs text-brand-400 hover:text-brand-300 flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Jadwal Ujian
                </a>
            </div>
            <h3 class="text-base font-bold text-slate-900">Daftar Jenis Penilaian & Asesmen</h3>
            <p class="text-xs text-slate-500">Klasifikasi asesmen seperti PAS, PTS, PAT, USBK, Penilaian Harian, Simulasi, dll.</p>
        </div>
        <div>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Jenis Ujian</span>
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Kode Asesmen</th>
                        <th class="py-3 px-4">Nama Jenis Asesmen</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jenisList as $idx => $jen)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-brand-600/20 text-brand-300 border border-brand-500/30">
                                    {{ $jen->kode_jenis }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-white">{{ $jen->nama_jenis }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('admin.cbt.jenis.destroy', $jen->id_jenis) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus jenis ujian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 rounded-lg transition" title="Hapus Jenis">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Belum ada data jenis ujian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Jenis -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-sm w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Tambah Jenis Ujian</h3>
            <p class="text-xs text-slate-500 mb-4">Masukkan kode singkatan dan nama lengkap jenis ujian</p>
            
            <form action="{{ route('admin.cbt.jenis.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Asesmen</label>
                    <input type="text" name="kode_jenis" placeholder="PAS / PTS / USBK / PH" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-white uppercase focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Jenis Asesmen</label>
                    <input type="text" name="nama_jenis" placeholder="Penilaian Akhir Semester" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">
                        Simpan Jenis
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
