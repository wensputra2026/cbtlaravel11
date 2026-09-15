@extends('layouts.admin')

@section('title', 'Jadwal Pelaksanaan Ujian')
@section('page_title', 'Jadwal & Sesi Pelaksanaan CBT')

@section('content')
<div class="space-y-6" x-data="{ 
    openModal: false, 
    openEditModal: false, 
    editData: {
        id: null,
        id_jenis: '',
        id_bank: '',
        durasi_ujian: 90,
        tgl_mulai: '',
        tgl_selesai: '',
        acak_soal: 1,
        acak_opsi: 1,
        token: 1,
        hasil_tampil: 0,
        reset_login: 0
    },
    openEdit(item) {
        this.editData = { ...item };
        this.openEditModal = true;
    }
}">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Jadwal Ujian Aktif</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Atur rentang waktu pembukaan ujian, durasi pengerjaan, dan acak soal/opsi</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cbt.jenis.index') }}" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span>Kelola Jenis Ujian</span>
            </a>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Jadwal Ujian</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form action="{{ route('admin.cbt.jadwal.index') }}" method="GET" class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <div class="relative w-full sm:w-56">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari mapel, jenis, ID..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select name="tahun_ajaran_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                @if(isset($allYears))
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ (isset($selectedYear) && $selectedYear->id == $y->id) ? 'selected' : '' }}>
                            {{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }} {{ $y->is_active ? '★' : '' }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if(request('q') || (isset($selectedYear) && isset($activeTahunAjaran) && $selectedYear->id != $activeTahunAjaran?->id))
                <a href="{{ route('admin.cbt.jadwal.index') }}" class="p-1.5 text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
        <span class="text-xs text-slate-500 dark:text-slate-400">Total: <strong class="text-slate-800 dark:text-white">{{ $jadwals->total() }}</strong> Jadwal Ujian</span>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Jenis Ujian</th>
                        <th class="py-3 px-4">Bank Soal / Mapel</th>
                        <th class="py-3 px-4">Rentang Waktu</th>
                        <th class="py-3 px-4 text-center">Durasi</th>
                        <th class="py-3 px-4 text-center">Fitur</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($jadwals as $idx => $j)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $jadwals->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30">
                                    {{ $j->jenis->kode_jenis ?? 'CBT' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $j->bankSoal->bank_nama ?? 'Bank Soal' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Mapel: {{ $j->bankSoal->mapel->nama_mapel ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <div class="text-slate-800 dark:text-slate-200 font-semibold">{{ \Carbon\Carbon::parse($j->tgl_mulai)->format('d M Y H:i') }}</div>
                                <div class="text-slate-500 dark:text-slate-400">s/d {{ \Carbon\Carbon::parse($j->tgl_selesai)->format('d M Y H:i') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-white text-[11px] border border-slate-200 dark:border-slate-700">
                                    {{ $j->durasi_ujian }} Menit
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1 text-[10px]">
                                    @if($j->acak_soal)<span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20" title="Acak Soal">Acak Soal</span>@endif
                                    @if($j->acak_opsi)<span class="px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20" title="Acak Opsi">Acak Opsi</span>@endif
                                    @if($j->token)<span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20" title="Token Diperlukan">Token</span>@endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.cbt.jadwal.toggle', $j->id_jadwal) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-[10px] font-bold rounded-full transition {{ $j->status ? 'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-200' }}">
                                        {{ $j->status ? 'AKTIF' : 'NONAKTIF' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('proctor.monitor', $j->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-sky-50 dark:bg-sky-600/20 text-sky-600 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30 hover:bg-sky-100 transition flex items-center gap-1" title="Buka Monitor di Tab Baru">
                                        <span>Monitor</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <button 
                                        @click="openEdit({
                                            id: {{ $j->id_jadwal }},
                                            id_jenis: {{ $j->id_jenis }},
                                            id_bank: {{ $j->id_bank }},
                                            durasi_ujian: {{ $j->durasi_ujian }},
                                            tgl_mulai: '{{ \Carbon\Carbon::parse($j->tgl_mulai)->format('Y-m-d\TH:i') }}',
                                            tgl_selesai: '{{ \Carbon\Carbon::parse($j->tgl_selesai)->format('Y-m-d\TH:i') }}',
                                            acak_soal: {{ $j->acak_soal ? 1 : 0 }},
                                            acak_opsi: {{ $j->acak_opsi ? 1 : 0 }},
                                            token: {{ $j->token ? 1 : 0 }},
                                            hasil_tampil: {{ $j->hasil_tampil ? 1 : 0 }},
                                            reset_login: {{ $j->reset_login ? 1 : 0 }}
                                        })"
                                        type="button" 
                                        class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-amber-50 dark:bg-amber-600/20 text-amber-600 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 hover:bg-amber-100 transition flex items-center gap-1" 
                                        title="Edit Jadwal Ujian"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('admin.cbt.jadwal.destroy', $j->id_jadwal) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus jadwal ujian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-lg transition" title="Hapus Jadwal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada jadwal pelaksanaan ujian dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwals->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Jadwal Ujian -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Jadwalkan Pelaksanaan Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Pilih paket bank soal, tentukan jenis ujian, dan durasi pengerjaan</p>
            
            <form action="{{ route('admin.cbt.jadwal.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Ujian</label>
                        <select name="id_jenis" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            @foreach($jenisList as $jen)
                                <option value="{{ $jen->id_jenis }}">{{ $jen->kode_jenis }} - {{ $jen->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="durasi_ujian" value="90" min="10" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Paket Bank Soal (Pencarian Dinamis)</label>
                    <x-tom-select name="id_bank" placeholder="Ketik atau cari paket bank soal..." required>
                        @foreach($bankList as $b)
                            <option value="{{ $b->id_bank }}">{{ $b->bank_kode }} - {{ $b->bank_nama }} ({{ $b->mapel->nama_mapel ?? '-' }})</option>
                        @endforeach
                    </x-tom-select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Guru Pengawas Ruang (Multi-Select Tag)</label>
                    <x-tom-select name="pengawas[]" multiple placeholder="Pilih satu atau beberapa guru pengawas...">
                        @foreach($guruList as $g)
                            <option value="{{ $g->id_guru }}">{{ $g->nama_guru }}</option>
                        @endforeach
                    </x-tom-select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu Mulai Akses</label>
                        <input type="datetime-local" name="tgl_mulai" value="{{ date('Y-m-d\TH:i') }}" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu Selesai Akses</label>
                        <input type="datetime-local" name="tgl_selesai" value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <!-- Opsi Acak & Token -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="acak_soal" value="1" checked class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Acak Urutan Butir Soal untuk Tiap Siswa</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="acak_opsi" value="1" checked class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Acak Urutan Opsi Jawaban (A-E)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="token" value="1" checked class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Wajibkan Token Ujian Dinamis Sebelum Mulai</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="hasil_tampil" value="1" class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Tampilkan Skor / Nilai Langsung ke Siswa Setelah Selesai</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Rilis Jadwal Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Jadwal Ujian -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Edit Jadwal Pelaksanaan Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Ubah pengaturan durasi, rentang waktu pengerjaan, acak butir soal, atau status token</p>
            
            <form :action="'{{ url('admin/cbt/jadwal') }}/' + editData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Ujian</label>
                        <select name="id_jenis" x-model="editData.id_jenis" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            @foreach($jenisList as $jen)
                                <option value="{{ $jen->id_jenis }}">{{ $jen->kode_jenis }} - {{ $jen->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="durasi_ujian" x-model="editData.durasi_ujian" min="10" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Paket Bank Soal</label>
                    <select name="id_bank" x-model="editData.id_bank" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach($bankList as $b)
                            <option value="{{ $b->id_bank }}">{{ $b->bank_kode }} - {{ $b->bank_nama }} ({{ $b->mapel->nama_mapel ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu Mulai Akses</label>
                        <input type="datetime-local" name="tgl_mulai" x-model="editData.tgl_mulai" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu Selesai Akses</label>
                        <input type="datetime-local" name="tgl_selesai" x-model="editData.tgl_selesai" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <!-- Opsi Acak & Token -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="acak_soal" value="1" :checked="editData.acak_soal == 1" @change="editData.acak_soal = $event.target.checked ? 1 : 0" class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Acak Urutan Butir Soal untuk Tiap Siswa</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="acak_opsi" value="1" :checked="editData.acak_opsi == 1" @change="editData.acak_opsi = $event.target.checked ? 1 : 0" class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Acak Urutan Opsi Jawaban (A-E)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="token" value="1" :checked="editData.token == 1" @change="editData.token = $event.target.checked ? 1 : 0" class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Wajibkan Token Ujian Dinamis Sebelum Mulai</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="hasil_tampil" value="1" :checked="editData.hasil_tampil == 1" @change="editData.hasil_tampil = $event.target.checked ? 1 : 0" class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Tampilkan Skor / Nilai Langsung ke Siswa Setelah Selesai</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="reset_login" value="1" :checked="editData.reset_login == 1" @change="editData.reset_login = $event.target.checked ? 1 : 0" class="rounded border-slate-300 dark:border-slate-800 text-brand-600 focus:ring-0">
                        <span>Izinkan Reset Login Siswa Otomatis saat Terputus</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Simpan Perubahan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
