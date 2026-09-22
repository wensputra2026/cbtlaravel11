@extends('layouts.admin')

@section('title', 'Kelas & Rombel')
@section('page_title', 'Kelas & Rombongan Belajar')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Daftar Kelas & Rombel</h3>
            <p class="text-xs text-slate-500">Daftar tingkatan kelas, rombongan belajar, dan penugasan jurusan peserta ujian</p>
        </div>
        <div>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kelas</span>
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
                        <th class="py-3 px-4">Tingkat / Level</th>
                        <th class="py-3 px-4">Kode Kelas</th>
                        <th class="py-3 px-4">Nama Kelas</th>
                        <th class="py-3 px-4">Jurusan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kelasList as $idx => $kelas)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-700 border border-slate-200">
                                    Kelas {{ $kelas->level_id }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-brand-400">{{ $kelas->kode_kelas }}</td>
                            <td class="py-3 px-4 font-semibold text-white">{{ $kelas->nama_kelas }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $kelas->jurusan->nama_jurusan ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Belum ada data kelas / rombel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Kelas -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-sm w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Tambah Kelas Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Masukkan data rombel kelas dan tingkatan</p>
            
            <form action="{{ route('admin.master.kelas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Level</label>
                        <select name="level_id" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="10">Kelas 10 (X)</option>
                            <option value="11">Kelas 11 (XI)</option>
                            <option value="12">Kelas 12 (XII)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jurusan</label>
                        <select name="jurusan_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="">-- Tanpa Jurusan --</option>
                            @foreach($jurusanList as $jur)
                                <option value="{{ $jur->id_jurusan }}">{{ $jur->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Kelas</label>
                    <input type="text" name="kode_kelas" placeholder="X-MIPA-1" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-white uppercase focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kelas</label>
                    <input type="text" name="nama_kelas" placeholder="X MIPA 1" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">
                        Simpan Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
