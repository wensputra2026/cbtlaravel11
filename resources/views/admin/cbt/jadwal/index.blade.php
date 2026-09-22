@extends('layouts.admin')

@section('title', 'Jadwal Pelaksanaan Ujian')
@section('page_title', 'Jadwal & Sesi Pelaksanaan CBT')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Daftar Jadwal Ujian Aktif</h3>
            <p class="text-xs text-slate-500">Atur rentang waktu pembukaan ujian, durasi pengerjaan, dan acak soal/opsi</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cbt.jenis.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-800 rounded-xl text-xs font-semibold border border-slate-200 transition flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span>Kelola Jenis Ujian</span>
            </a>
            <button @click="openModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Jadwal Ujian</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 shadow-sm flex items-center justify-between gap-4 flex-wrap">
        <form action="{{ route('admin.cbt.jadwal.index') }}" method="GET" class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Periode Tahun Ajaran:</span>
            <select name="tahun_ajaran_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                @if(isset($allYears))
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ (isset($selectedYear) && $selectedYear->id == $y->id) ? 'selected' : '' }}>
                            {{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }} {{ $y->is_active ? '★' : '' }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if(isset($selectedYear) && isset($activeTahunAjaran) && $selectedYear->id != $activeTahunAjaran?->id)
                <a href="{{ route('admin.cbt.jadwal.index') }}" class="px-2.5 py-1.5 text-xs text-amber-400 hover:text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                    Reset ke Aktif
                </a>
            @endif
        </form>
        <span class="text-xs text-slate-500">Total: <strong>{{ $jadwals->total() }}</strong> Jadwal Ujian</span>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($jadwals as $idx => $j)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-500">{{ $jadwals->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-brand-600/20 text-brand-300 border border-brand-500/30">
                                    {{ $j->jenis->kode_jenis ?? 'CBT' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white">{{ $j->bankSoal->bank_nama ?? 'Bank Soal' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Mapel: {{ $j->bankSoal->mapel->nama_mapel ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <div class="text-slate-800">{{ \Carbon\Carbon::parse($j->tgl_mulai)->format('d M Y H:i') }}</div>
                                <div class="text-slate-500">s/d {{ \Carbon\Carbon::parse($j->tgl_selesai)->format('d M Y H:i') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 font-bold text-white text-[11px]">
                                    {{ $j->durasi_ujian }} Menit
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1 text-[10px]">
                                    @if($j->acak_soal)<span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20" title="Acak Soal">Acak Soal</span>@endif
                                    @if($j->acak_opsi)<span class="px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20" title="Acak Opsi">Acak Opsi</span>@endif
                                    @if($j->token)<span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20" title="Token Diperlukan">Token</span>@endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.cbt.jadwal.toggle', $j->id_jadwal) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-[10px] font-bold rounded-full transition {{ $j->status ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-500/30' : 'bg-slate-800 text-slate-500 hover:bg-slate-700' }}">
                                        {{ $j->status ? 'AKTIF' : 'NONAKTIF' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('proctor.monitor', $j->id_jadwal) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-sky-600/20 text-sky-300 border border-sky-500/30 hover:bg-sky-600 hover:text-white transition">
                                        Monitor
                                    </a>
                                    <form action="{{ route('admin.cbt.jadwal.destroy', $j->id_jadwal) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus jadwal ujian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 rounded-lg transition" title="Hapus Jadwal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">Belum ada jadwal pelaksanaan ujian dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwals->hasPages())
            <div class="pt-4 border-t border-slate-200 mt-4">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Jadwal Ujian -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-sm w-full max-w-xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-white mb-1">Jadwalkan Pelaksanaan Ujian</h3>
            <p class="text-xs text-slate-500 mb-4">Pilih paket bank soal, tentukan jenis ujian, dan durasi pengerjaan</p>
            
            <form action="{{ route('admin.cbt.jadwal.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Ujian</label>
                        <select name="id_jenis" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            @foreach($jenisList as $jen)
                                <option value="{{ $jen->id_jenis }}">{{ $jen->kode_jenis }} - {{ $jen->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="durasi_ujian" value="90" min="10" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Paket Bank Soal</label>
                    <select name="id_bank" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        @foreach($bankList as $b)
                            <option value="{{ $b->id_bank }}">{{ $b->bank_kode }} - {{ $b->bank_nama }} ({{ $b->mapel->nama_mapel ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Mulai Akses</label>
                        <input type="datetime-local" name="tgl_mulai" value="{{ date('Y-m-d\TH:i') }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Selesai Akses</label>
                        <input type="datetime-local" name="tgl_selesai" value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>

                <!-- Opsi Acak & Token -->
                <div class="pt-2 border-t border-slate-200 space-y-2">
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="acak_soal" value="1" checked class="rounded border-slate-200 text-brand-600 focus:ring-0">
                        <span>Acak Urutan Butir Soal untuk Tiap Siswa</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="acak_opsi" value="1" checked class="rounded border-slate-200 text-brand-600 focus:ring-0">
                        <span>Acak Urutan Opsi Jawaban (A-E)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="token" value="1" checked class="rounded border-slate-200 text-brand-600 focus:ring-0">
                        <span>Wajibkan Token Ujian Dinamis Sebelum Mulai</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="hasil_tampil" value="1" class="rounded border-slate-200 text-brand-600 focus:ring-0">
                        <span>Tampilkan Skor / Nilai Langsung ke Siswa Setelah Selesai</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-brand-600/30">
                        Rilis Jadwal Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
