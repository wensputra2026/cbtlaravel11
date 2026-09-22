@extends('layouts.admin')

@section('title', 'Alokasi Peserta Ujian')
@section('page_title', 'Penempatan Sesi & Ruang Peserta CBT')

@section('content')
<div class="space-y-6" x-data="{ openModalAuto: false, openModalEdit: false, editSiswa: {} }">

    <!-- Header Actions & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Alokasi Sesi & Ruang Siswa</h3>
            <p class="text-xs text-slate-500">Tentukan pembagian ruangan ujian dan shift sesi pengerjaan siswa</p>
        </div>
        <div>
            <button @click="openModalAuto = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Alokasi Cepat per Kelas</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('admin.cbt.alokasi.sesi') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <option value="">-- Seluruh Rombel Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $kelasId == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="ruang_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <option value="">-- Seluruh Ruangan --</option>
                    @foreach($ruangList as $r)
                        <option value="{{ $r->id_ruang }}" {{ $ruangId == $r->id_ruang ? 'selected' : '' }}>{{ $r->nama_ruang }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="sesi_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <option value="">-- Seluruh Shift Sesi --</option>
                    @foreach($sesiList as $s)
                        <option value="{{ $s->id_sesi }}" {{ $sesiId == $s->id_sesi ? 'selected' : '' }}>{{ $s->nama_sesi }} ({{ $s->waktu_mulai }}-{{ $s->waktu_selesai }})</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Peserta</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Ruang Ujian</th>
                        <th class="py-3 px-4">Sesi Waktu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siswas as $idx => $s)
                        @php
                            $rombel = $s->kelasSiswa->first()?->kelas->nama_kelas ?? '-';
                            $ruangName = $s->sesiSiswa?->ruang?->nama_ruang ?? 'Belum Diatur';
                            $sesiName = $s->sesiSiswa?->sesi?->nama_sesi ?? 'Belum Diatur';
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-slate-500">{{ $siswas->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $s->nama }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $rombel }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold {{ $s->sesiSiswa ? 'bg-blue-500/10 text-blue-600 border border-blue-500/20' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $ruangName }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold {{ $s->sesiSiswa ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $sesiName }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button @click="editSiswa = { id: {{ $s->id_siswa }}, nama: '{{ addslashes($s->nama) }}', ruang: '{{ $s->sesiSiswa?->ruang_id }}', sesi: '{{ $s->sesiSiswa?->sesi_id }}' }; openModalEdit = true;" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-brand-50 text-brand-600 border border-brand-200 hover:bg-brand-600 hover:text-white transition">
                                    Ubah Ruang/Sesi
                                </button>
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

    <!-- Modal Edit Sesi Siswa Satuan -->
    <div x-show="openModalEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModalEdit = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-800 mb-1">Ubah Alokasi Ruang & Sesi</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="editSiswa.nama"></p>

            <form action="{{ route('admin.cbt.alokasi.sesi.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="siswa_id" :value="editSiswa.id">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Ruangan</label>
                    <select name="ruang_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        @foreach($ruangList as $r)
                            <option value="{{ $r->id_ruang }}">{{ $r->nama_ruang }} ({{ $r->kode_ruang }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Sesi</label>
                    <select name="sesi_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        @foreach($sesiList as $s)
                            <option value="{{ $s->id_sesi }}">{{ $s->nama_sesi }} ({{ $s->waktu_mulai }}-{{ $s->waktu_selesai }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalEdit = false" class="px-4 py-2 text-xs font-semibold text-slate-500">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">Simpan Alokasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Alokasi Cepat per Kelas -->
    <div x-show="openModalAuto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModalAuto = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-800 mb-1">Alokasi Cepat per Kelas</h3>
            <p class="text-xs text-slate-500 mb-4">Tempatkan seluruh siswa satu rombel ke ruang & sesi sekaligus</p>

            <form action="{{ route('admin.cbt.alokasi.sesi.auto') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Kelas</label>
                    <select name="id_kelas" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Ruangan</label>
                    <select name="ruang_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        @foreach($ruangList as $r)
                            <option value="{{ $r->id_ruang }}">{{ $r->nama_ruang }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Sesi</label>
                    <select name="sesi_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        @foreach($sesiList as $s)
                            <option value="{{ $s->id_sesi }}">{{ $s->nama_sesi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalAuto = false" class="px-4 py-2 text-xs font-semibold text-slate-500">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">Alokasikan Kelas Ini</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
