@extends('layouts.admin')

@section('title', 'Jurusan & Peminatan')
@section('page_title', 'Jurusan & Peminatan Siswa')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Daftar Jurusan / Peminatan</h3>
            <p class="text-xs text-slate-500">Pengelompokan peminatan jurusan untuk alokasi kelas dan bank soal CBT</p>
        </div>
        <div>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Jurusan</span>
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
                        <th class="py-3 px-4">Kode Jurusan</th>
                        <th class="py-3 px-4">Nama Jurusan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jurusanList as $idx => $jurusan)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-brand-400">{{ $jurusan->kode_jurusan }}</td>
                            <td class="py-3 px-4 font-semibold text-white">{{ $jurusan->nama_jurusan }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">Belum ada data jurusan / peminatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Jurusan -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-sm w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Tambah Jurusan Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Masukkan data kode dan nama peminatan jurusan</p>
            
            <form action="{{ route('admin.master.jurusan.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Jurusan</label>
                    <input type="text" name="kode_jurusan" placeholder="MIPA / IPS / BAHASA" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-white uppercase focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" placeholder="Matematika dan Ilmu Pengetahuan Alam" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">
                        Simpan Jurusan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
