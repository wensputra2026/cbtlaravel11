@extends('layouts.admin')

@section('title', 'Manajemen User Siswa')
@section('page_title', 'Manajemen Akun Siswa CBT')

@section('content')
<div class="space-y-6" x-data="{ openResetModal: false, selectedUserId: null, selectedUsername: '' }">

    <!-- Header Actions & Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Daftar Akun Peserta Ujian (Siswa)</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola akun kredensial login peserta CBT, cetak kartu massal, dan reset kata sandi</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('print.kartu_login', ['kelas_id' => $kelasId]) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Kartu Login Massal
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('admin.users.siswa') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Rombel / Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $kelasId == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Pencarian Nama / Username / NISN</label>
                <div class="flex gap-2">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Ketik nama, username atau NISN siswa..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition">Cari</button>
                    @if($search || $kelasId)
                        <a href="{{ route('admin.users.siswa') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Peserta</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4">Password Tersimpan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($siswas as $idx => $s)
                        @php 
                            $u = $s->user; 
                            $kelasNama = $s->kelasSiswa->first()?->kelas?->nama_kelas ?? '-';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $siswas->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $s->nama }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">NISN: {{ $s->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $kelasNama }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">{{ $s->username }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400 font-semibold">{{ $s->password ?? '123456' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u && $u->active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @elseif($u)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                        Nonaktif
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        Tersimpan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u)
                                    <button type="button" @click="selectedUserId = {{ $u->id }}; selectedUsername = '{{ $s->username }}'; openResetModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[11px] font-semibold transition" title="Reset Password">
                                        Reset
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada akun peserta ujian ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Reset Password -->
    <div x-show="openResetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;" x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-xl" @click.away="openResetModal = false">
            <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Reset Password Akun Siswa</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Ubah password untuk user <span class="font-bold text-brand-500" x-text="selectedUsername"></span></p>
            <form :action="'/admin/users/reset-password/' + selectedUserId" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Password Baru</label>
                    <input type="text" name="new_password" required minlength="4" placeholder="Masukkan password baru..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openResetModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
