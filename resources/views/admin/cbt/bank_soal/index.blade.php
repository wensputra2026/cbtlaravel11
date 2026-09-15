@extends('layouts.admin')

@section('title', 'Bank Soal Ujian')
@section('page_title', 'Manajemen Paket Bank Soal (5 Tipe Soal)')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions & Filters -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Paket Bank Soal</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola paket soal ujian (Pilihan Ganda, Kompleks, Jodohkan, Isian Singkat, dan Esai)</p>
        </div>
        <div>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Bank Soal Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form action="{{ route('admin.cbt.bank_soal.index') }}" method="GET" class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full sm:w-auto">
            <div class="relative w-full sm:w-56">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode bank..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select name="mapel_id" onchange="this.form.submit()" class="w-full sm:w-56 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                <option value="">-- Seluruh Mata Pelajaran --</option>
                @foreach($mapelList as $m)
                    <option value="{{ $m->id_mapel }}" {{ $mapelId == $m->id_mapel ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                @endforeach
            </select>
            @if($mapelId || request('q'))
                <a href="{{ route('admin.cbt.bank_soal.index') }}" class="p-2 text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
        <div class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block">
            Total: <span class="text-slate-800 dark:text-white font-bold">{{ $banks->total() }}</span> Bank Soal
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Kode & Nama Bank Soal</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Guru Pembuat</th>
                        <th class="py-3 px-4 text-center">Komposisi Butir</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($banks as $idx => $bank)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $banks->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.cbt.bank_soal.show', $bank->id_bank) }}" class="font-bold text-slate-800 dark:text-white hover:text-brand-600 dark:hover:text-brand-300 transition block">
                                    {{ $bank->bank_nama }}
                                </a>
                                <span class="font-mono text-[11px] text-brand-600 dark:text-brand-400">{{ $bank->bank_kode }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-800 dark:text-white font-semibold">{{ $bank->mapel->nama_mapel ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                {{ $bank->guru->nama_guru ?? 'Administrator' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                    <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 text-[10px]" title="Pilihan Ganda">
                                        PG: {{ $bank->tampil_pg }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20 text-[10px]" title="Pilihan Kompleks">
                                        PK: {{ $bank->tampil_kompleks }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 text-[10px]" title="Jodohkan">
                                        JD: {{ $bank->tampil_jodohkan }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-500/20 text-[10px]" title="Isian Singkat">
                                        IS: {{ $bank->tampil_isian }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 text-[10px]" title="Esai Uraian">
                                        ES: {{ $bank->tampil_esai }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.cbt.bank_soal.show', $bank->id_bank) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-xl bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30 hover:bg-brand-600 hover:text-white transition shadow-xs" title="Kelola Butir Soal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Kelola Butir</span>
                                    </a>
                                    <form action="{{ route('admin.cbt.bank_soal.duplicate', $bank->id_bank) }}" method="POST" class="inline-block" onsubmit="return confirm('Duplikasi seluruh butir paket bank soal ini?')">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition shadow-xs" title="Duplikasi Paket">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.cbt.bank_soal.destroy', $bank->id_bank) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus bank soal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800/50 rounded-xl transition shadow-xs" title="Hapus Bank Soal">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada paket Bank Soal dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($banks->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $banks->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Bank Soal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Buat Paket Bank Soal Baru</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Tentukan kode, nama, mata pelajaran, serta jumlah butir per tipe soal</p>
            
            <form action="{{ route('admin.cbt.bank_soal.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Bank Soal</label>
                        <input type="text" name="bank_kode" placeholder="MAT-10-PAS" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran</label>
                        <x-tom-select name="id_mapel" placeholder="Cari & Pilih Mapel..." required>
                            @foreach($mapelList as $m)
                                <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </x-tom-select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Bank Soal</label>
                    <input type="text" name="bank_nama" placeholder="Penilaian Akhir Semester Matematika Wajib Kelas X" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tingkat Kelas</label>
                        <select name="bank_level" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="10">Kelas 10 (X)</option>
                            <option value="11">Kelas 11 (XI)</option>
                            <option value="12">Kelas 12 (XII)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Guru Pengampu / Pembuat</label>
                        <x-tom-select name="bank_guru_id" placeholder="Cari Guru Pengampu...">
                            <option value="1">-- Administrator CBT --</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g->id_guru }}">{{ $g->nama_guru }}</option>
                            @endforeach
                        </x-tom-select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alokasi Rombel Kelas (Multi-Select Tag)</label>
                    <x-tom-select name="bank_kelas[]" multiple placeholder="Pilih beberapa kelas sasaran ujian...">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </x-tom-select>
                </div>

                <!-- Konfigurasi 5 Tipe Soal & Bobot -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <label class="block text-xs font-bold text-slate-800 dark:text-white mb-2">Komposisi Butir Soal Ditampilkan & Bobot (%)</label>
                    <div class="grid grid-cols-5 gap-2 text-[11px]">
                        <div class="p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-center">
                            <span class="font-bold text-blue-600 dark:text-blue-400 block mb-1">PG Biasa</span>
                            <input type="number" name="tampil_pg" value="30" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white mb-1" placeholder="Butir">
                            <input type="number" name="bobot_pg" value="60" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white" placeholder="Bobot %">
                        </div>
                        <div class="p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-center">
                            <span class="font-bold text-purple-600 dark:text-purple-400 block mb-1">PG Kompleks</span>
                            <input type="number" name="tampil_kompleks" value="5" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white mb-1" placeholder="Butir">
                            <input type="number" name="bobot_kompleks" value="10" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white" placeholder="Bobot %">
                        </div>
                        <div class="p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-center">
                            <span class="font-bold text-amber-600 dark:text-amber-400 block mb-1">Jodohkan</span>
                            <input type="number" name="tampil_jodohkan" value="5" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white mb-1" placeholder="Butir">
                            <input type="number" name="bobot_jodohkan" value="10" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white" placeholder="Bobot %">
                        </div>
                        <div class="p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-center">
                            <span class="font-bold text-teal-600 dark:text-teal-400 block mb-1">Isian Singkat</span>
                            <input type="number" name="tampil_isian" value="5" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white mb-1" placeholder="Butir">
                            <input type="number" name="bobot_isian" value="10" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white" placeholder="Bobot %">
                        </div>
                        <div class="p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-center">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-1">Esai Uraian</span>
                            <input type="number" name="tampil_esai" value="5" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white mb-1" placeholder="Butir">
                            <input type="number" name="bobot_esai" value="10" class="w-full text-center px-1 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white" placeholder="Bobot %">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Buat Paket Bank Soal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
