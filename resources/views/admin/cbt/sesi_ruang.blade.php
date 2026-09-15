@extends('layouts.admin')

@section('title', 'Sesi & Ruang Ujian')
@section('page_title', 'Manajemen Ruang, Sesi, & Pengawas Ujian')

@section('content')
<div class="space-y-6" x-data="{ openModalRuang: false, openModalSesi: false, openModalPengawas: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Konfigurasi Ruangan, Sesi, & Penugasan Pengawas</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Atur laboratorium / ruang ujian, shift sesi waktu, serta penugasan guru pengawas</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('print.denah_ruang') }}" target="_blank" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"/></svg>
                <span>Cetak Denah Ruang</span>
            </a>
            <button @click="openModalRuang = true" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-white rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <span>+ Tambah Ruang</span>
            </button>
            <button @click="openModalSesi = true" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-white rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <span>+ Tambah Sesi</span>
            </button>
            <button @click="openModalPengawas = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-1.5">
                <span>+ Tugaskan Pengawas</span>
            </button>
        </div>
    </div>

    <!-- Grid: Ruang (Col 1) & Sesi (Col 2) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Ruangan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400">🚪</span>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Ruang / Laboratorium</h4>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($ruangList) }} Ruang</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($ruangList as $ruang)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-white text-xs">{{ $ruang->nama_ruang }}</span>
                            <span class="ml-2 font-mono text-[11px] text-brand-600 dark:text-brand-400">({{ $ruang->kode_ruang }})</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                            Tersedia
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">Belum ada ruang ujian terdaftar.</div>
                @endforelse
            </div>
        </div>

        <!-- 2. Sesi Waktu -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400">⏰</span>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Shift / Sesi Ujian</h4>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($sesiList) }} Sesi</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($sesiList as $sesi)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-white text-xs">{{ $sesi->nama_sesi }}</span>
                            <span class="ml-2 font-mono text-[11px] text-slate-500 dark:text-slate-400">({{ $sesi->kode_sesi }})</span>
                        </div>
                        <div class="font-mono text-xs text-amber-600 dark:text-amber-400 font-semibold">
                            {{ $sesi->waktu_mulai }} - {{ $sesi->waktu_selesai }}
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">Belum ada sesi waktu terdaftar.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Table Penugasan Pengawas -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white">Jadwal & Penugasan Pengawas Ruang</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Guru yang ditugaskan mengawasi jalannya ujian di setiap ruang & sesi</p>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($pengawas) }} Penugasan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Guru Pengawas</th>
                        <th class="py-3 px-4">Jadwal Ujian</th>
                        <th class="py-3 px-4">Ruang</th>
                        <th class="py-3 px-4">Sesi Waktu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($pengawas as $idx => $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 text-slate-400">
                                {{ $pengawas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pengawas->firstItem() + $idx : $idx + 1 }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $p->guru->nama_guru ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">NIP: {{ $p->guru->nip ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-brand-600 dark:text-brand-300">{{ $p->jadwal->bankSoal->bank_nama ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                                    {{ $p->ruang->nama_ruang ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                    {{ $p->sesi->nama_sesi ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.cbt.sesi_ruang.delete_pengawas', $p->id_pengawas) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus penugasan pengawas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-lg transition" title="Hapus Penugasan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada penugasan guru pengawas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengawas instanceof \Illuminate\Pagination\LengthAwarePaginator && $pengawas->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $pengawas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Ruang -->
    <div x-show="openModalRuang" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalRuang = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tambah Ruang Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Contoh: Lab Komputer 1, Ruang Kelas X-1</p>
            <form action="{{ route('admin.cbt.sesi_ruang.ruang.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Ruang</label>
                    <input type="text" name="kode_ruang" placeholder="LAB-1 / R-01" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Ruangan</label>
                    <input type="text" name="nama_ruang" placeholder="Laboratorium Komputer 1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalRuang = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">Simpan Ruang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Sesi -->
    <div x-show="openModalSesi" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalSesi = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tambah Sesi / Shift Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Tentukan rentang jam mulai dan selesai</p>
            <form action="{{ route('admin.cbt.sesi_ruang.sesi.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Sesi</label>
                        <input type="text" name="kode_sesi" placeholder="SESI-1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sesi</label>
                        <input type="text" name="nama_sesi" placeholder="Sesi Pagi 1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu Mulai</label>
                        <input type="time" name="waktu_mulai" value="07:30" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu Selesai</label>
                        <input type="time" name="waktu_selesai" value="09:30" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalSesi = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">Simpan Sesi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tugaskan Pengawas -->
    <div x-show="openModalPengawas" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalPengawas = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tugaskan Guru Pengawas</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Pilih jadwal ujian, ruang, sesi, dan nama guru</p>
            <form action="{{ route('admin.cbt.sesi_ruang.assign_pengawas') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Guru Pengawas</label>
                    <select name="id_guru" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach($guruList as $g)
                            <option value="{{ $g->id_guru }}">{{ $g->nama_guru }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jadwal Ujian</label>
                    <select name="id_jadwal" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach($jadwalList as $jl)
                            <option value="{{ $jl->id_jadwal }}">{{ $jl->bankSoal->bank_nama ?? 'Jadwal' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ruangan</label>
                        <select name="id_ruang" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            @foreach($ruangList as $r)
                                <option value="{{ $r->id_ruang }}">{{ $r->nama_ruang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sesi</label>
                        <select name="id_sesi" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            @foreach($sesiList as $s)
                                <option value="{{ $s->id_sesi }}">{{ $s->nama_sesi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalPengawas = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

