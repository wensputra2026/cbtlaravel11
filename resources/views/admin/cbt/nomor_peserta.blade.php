@extends('layouts.admin')

@section('title', 'Nomor Peserta Ujian')
@section('page_title', 'Kelola & Generate Nomor Peserta CBT')

@section('content')
<div class="space-y-6" x-data="{ openModalGen: false }">

    <!-- Header Actions & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Nomor Peserta Ujian</h3>
            <p class="text-xs text-slate-500">Kelola dan generate nomor identitas unik peserta yang dicetak pada kartu ujian</p>
        </div>
        <div>
            <button @click="openModalGen = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span>Generate Nomor Peserta Massal</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('admin.cbt.alokasi.nomor') }}" method="GET" class="w-full sm:w-80">
            <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                <option value="">-- Seluruh Rombel Kelas --</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k->id_kelas }}" {{ $kelasId == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">NISN / NIS</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4 font-bold text-brand-600">Nomor Peserta Ujian</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siswas as $idx => $s)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-slate-500">{{ $siswas->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $s->nama }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $s->nisn ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $s->kelasSiswa->first()?->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-base text-brand-600">
                                {{ $s->nomorPeserta?->nomor_peserta ?? 'Belum Digenerate' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($s->nomorPeserta)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/30">
                                        Terdaftar
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-medium rounded-full bg-slate-100 text-slate-500">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Tidak ada data siswa ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
            <div class="pt-4 border-t border-slate-100 mt-4">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Generate Nomor Peserta -->
    <div x-show="openModalGen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModalGen = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-800 mb-1">Generate Nomor Peserta Massal</h3>
            <p class="text-xs text-slate-500 mb-4">Sistem akan membuatkan nomor peserta urut untuk seluruh siswa aktif</p>

            <form action="{{ route('admin.cbt.alokasi.nomor.generate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prefix / Kode Awalan</label>
                    <input type="text" name="prefix" value="26-01" required placeholder="Contoh: 26-01 atau US-26" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 uppercase font-mono focus:outline-none focus:border-brand-500">
                    <p class="text-[11px] text-slate-500 mt-1">Format hasil: [PREFIX]-0001 s/d [PREFIX]-9999</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalGen = false" class="px-4 py-2 text-xs font-semibold text-slate-500">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">Mulai Generate</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
