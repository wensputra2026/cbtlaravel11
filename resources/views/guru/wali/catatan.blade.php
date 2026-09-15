@extends('layouts.guru')

@section('title', 'Catatan Pembinaan Wali Kelas ' . ($waliKelas->nama_kelas ?? ''))
@section('page_title', 'Catatan Bimbingan Kelas ' . ($waliKelas->nama_kelas ?? ''))

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false, targetType: 1 }">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-cyan-700 via-teal-600 to-emerald-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/20 text-white backdrop-blur-sm border border-white/20">
                        BIMBINGAN & KONSELING KELAS
                    </span>
                    <span class="text-xs text-cyan-100 font-medium">
                        TP {{ $assignment['active_tp']->tahun ?? '-' }} • Smt {{ $assignment['active_smt']->smt ?? '-' }}
                    </span>
                </div>
                <h2 class="text-2xl font-black tracking-tight">Catatan Bimbingan {{ $waliKelas->nama_kelas }}</h2>
                <p class="text-sm text-cyan-100 mt-1">
                    Rekam catatan pembinaan, perkembangan akademik, kedisiplinan, maupun prestasi siswa.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="addModalOpen = true"
                    class="px-4 py-2 bg-white text-emerald-700 hover:bg-emerald-50 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Catatan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter & Statistics -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
            <a href="{{ route('guru.wali.catatan') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ !request('level') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                Semua Kategori
            </a>
            <a href="{{ route('guru.wali.catatan', ['level' => 1]) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ request('level') == '1' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' }}">
                Prestasi (Tingkat 1)
            </a>
            <a href="{{ route('guru.wali.catatan', ['level' => 2]) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ request('level') == '2' ? 'bg-sky-600 text-white' : 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' }}">
                Perkembangan (Tingkat 2)
            </a>
            <a href="{{ route('guru.wali.catatan', ['level' => 3]) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ request('level') == '3' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                Peringatan (Tingkat 3)
            </a>
            <a href="{{ route('guru.wali.catatan', ['level' => 4]) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ request('level') == '4' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }}">
                Perhatian Khusus (Tingkat 4)
            </a>
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium shrink-0">
            Total Catatan: <strong>{{ $catatans->total() }}</strong> data
        </div>
    </div>

    <!-- Notes List / Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4 w-32">Tanggal</th>
                        <th class="py-3.5 px-4 w-52">Sasaran Siswa</th>
                        <th class="py-3.5 px-4 w-36">Kategori</th>
                        <th class="py-3.5 px-4">Uraian Catatan Pembinaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($catatans as $idx => $c)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $catatans->firstItem() + $idx }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-mono text-[11px]">
                                {{ date('d M Y, H:i', strtotime($c->tgl)) }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">
                                @if($c->type == 1 || empty($c->id_siswa))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold text-[11px]">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Seluruh Kelas
                                    </span>
                                @else
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $c->nama_siswa }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">NIS: {{ $c->nis ?? '-' }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($c->level == 1)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        ★ Prestasi
                                    </span>
                                @elseif($c->level == 2)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300">
                                        ● Perkembangan
                                    </span>
                                @elseif($c->level == 3)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        ▲ Peringatan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        ✖ Perhatian Khusus
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">
                                {{ $c->text }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <p class="text-sm font-semibold">Belum ada catatan pembinaan.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "+ Tambah Catatan" untuk membuat rekam bimbingan baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($catatans->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $catatans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Catatan -->
    <div 
        x-show="addModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="addModalOpen = false"
    >
        <div 
            @click.away="addModalOpen = false"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150"
        >
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Tambah Catatan Bimbingan
                </h3>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg">&times;</button>
            </div>

            <form action="{{ route('guru.wali.catatan.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <!-- Sasaran Catatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Sasaran Catatan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition text-xs font-semibold" :class="targetType === 1 ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'">
                                <input type="radio" name="type" value="1" x-model.number="targetType" class="text-emerald-600">
                                <span>Seluruh Kelas (Umum)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition text-xs font-semibold" :class="targetType === 2 ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'">
                                <input type="radio" name="type" value="2" x-model.number="targetType" class="text-emerald-600">
                                <span>Siswa Tertentu (Personal)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Pilih Siswa (jika individu) -->
                    <div x-show="targetType === 2" x-transition>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Siswa</label>
                        <select name="id_siswa" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih Nama Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id_siswa }}">{{ $s->nama }} (NIS: {{ $s->nis ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kategori Tingkat Catatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Kategori Catatan</label>
                        <select name="level" required class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="1">★ Tingkat 1: Prestasi / Penghargaan / Keberhasilan</option>
                            <option value="2" selected>● Tingkat 2: Perkembangan Belajar Positif / Sikap</option>
                            <option value="3">▲ Tingkat 3: Peringatan Ringan / Kedisiplinan / Absensi</option>
                            <option value="4">✖ Tingkat 4: Perhatian Khusus / Rekomendasi Bimbingan Konseling (BK)</option>
                        </select>
                    </div>

                    <!-- Isi Catatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Uraian Catatan</label>
                        <textarea 
                            name="text" 
                            rows="4" 
                            required 
                            placeholder="Tuliskan catatan kejadian, apresiasi, atau pengarahan yang diberikan..."
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
