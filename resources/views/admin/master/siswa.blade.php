@extends('layouts.admin')

@section('title', 'Data Siswa & Akun')
@section('page_title', 'Master Data Siswa & Akun CBT')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions & Filters -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Daftar Peserta Ujian</h3>
            <p class="text-xs text-slate-500">Kelola akun login siswa, rombel kelas, dan nomor peserta ujian CBT</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('print.kartu_peserta') }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-800 rounded-xl text-xs font-semibold border border-slate-200 transition flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Kartu Peserta</span>
            </a>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Siswa</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 shadow-sm">
        <form action="{{ route('admin.master.siswa') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama siswa, NISN, atau username login..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <div>
                <select name="tahun_ajaran_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    @if(isset($allYears))
                        @foreach($allYears as $y)
                            <option value="{{ $y->id }}" {{ (isset($selectedYearId) && $selectedYearId == $y->id) ? 'selected' : '' }}>
                                {{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }} {{ $y->is_active ? '★' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="flex items-center gap-2">
                <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <option value="">-- Semua Rombel Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $kelasId == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                @if($search || $kelasId || (isset($selectedYearId) && isset($activeTahunAjaran) && $selectedYearId != $activeTahunAjaran?->id))
                    <a href="{{ route('admin.master.siswa') }}" class="p-2 text-xs text-slate-500 hover:text-white bg-slate-800 rounded-xl shrink-0" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Peserta</th>
                        <th class="py-3 px-4">NISN / NIS</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4">Password Awal</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siswas as $idx => $siswa)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-500">{{ $siswas->firstItem() + $idx }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $siswa->nama }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">
                                <div>{{ $siswa->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $rombel = $siswa->kelasSiswa->first()?->kelas->nama_kelas ?? '-';
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-700 border border-slate-200">
                                    {{ $rombel }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-brand-400">{{ $siswa->username }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px]">
                                    {{ $siswa->password ?? '••••••' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Siap Ujian
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Tidak ada data siswa ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
            <div class="pt-4 border-t border-slate-200 mt-4">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Siswa -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-sm w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Tambah Siswa Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Akun login siswa dan alokasi rombel kelas akan dibuat secara langsung</p>
            
            <form action="{{ route('admin.master.siswa.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="nama" placeholder="Ahmad Rizky Pratama" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NISN</label>
                        <input type="text" name="nisn" placeholder="0051234567" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Kelas</label>
                        <select name="id_kelas" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username Login</label>
                        <input type="text" name="username" placeholder="0051234567" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                        <input type="text" name="password" placeholder="123456" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
