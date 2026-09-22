@extends('layouts.admin')

@section('title', 'Data Guru & Staf')
@section('page_title', 'Master Data Guru & Pengawas CBT')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Daftar Guru & Akun Pengawas</h3>
            <p class="text-xs text-slate-500">Kelola data guru pembuat bank soal dan penugasan pengawas ujian</p>
        </div>
        <div>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Guru & Akun</span>
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
                        <th class="py-3 px-4">Foto</th>
                        <th class="py-3 px-4">Nama Lengkap & NIP</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4">Password Awal</th>
                        <th class="py-3 px-4 text-center">Status Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($guruList as $idx => $guru)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-500">{{ $guruList->firstItem() + $idx }}</td>
                            <td class="py-3 px-4">
                                <img src="{{ $guru->foto_url }}" alt="{{ $guru->nama_guru }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 bg-slate-800" onerror="this.src='{{ asset('assets/img/guru-default.png') }}'">
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $guru->nama_guru }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">NIP: {{ $guru->nip ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-brand-400">{{ $guru->username }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px]">
                                    {{ $guru->password ?? '••••••' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Belum ada data guru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($guruList->hasPages())
            <div class="pt-4 border-t border-slate-200 mt-4">
                {{ $guruList->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Guru -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-sm w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Tambah Guru & Pengawas Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Akun login guru dan hak akses CBT akan dibuat secara otomatis</p>
            
            <form action="{{ route('admin.master.guru.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="nama_guru" placeholder="Dr. Budi Santoso, M.Pd" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIP (Nomor Induk Pegawai)</label>
                    <input type="text" name="nip" placeholder="198001012005011001 atau -" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username Login</label>
                        <input type="text" name="username" placeholder="budisantoso" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                        <input type="text" name="password" placeholder="guru123" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">
                        Simpan Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
