@extends('layouts.admin')

@section('title', 'Manajemen User Guru')
@section('page_title', 'Manajemen Akun Guru')

@section('content')
<div class="space-y-6" x-data="{ openResetModal: false, selectedUserId: null, selectedUsername: '' }">

    <!-- Header Actions & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Daftar Akun Guru & Pengawas</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pengelolaan hak akses portal guru, pembuatan bank soal, dan pengawas ujian</p>
        </div>
        <div class="w-full sm:w-80">
            <form action="{{ route('admin.users.guru') }}" method="GET">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, NIP, atau username guru..." class="w-full px-3.5 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500 shadow-sm">
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Lengkap & NIP</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4">Password Tersimpan</th>
                        <th class="py-3 px-4 text-center">Status Login</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($gurus as $idx => $g)
                        @php $u = $g->user; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $gurus->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $g->nama_guru }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">NIP: {{ $g->nip ?: '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">{{ $g->username }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $g->password ?: '••••••' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u && $u->active)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @elseif($u)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                        Nonaktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        Belum Sinkron
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($u)
                                        <button type="button" @click="selectedUserId = {{ $u->id }}; selectedUsername = '{{ $g->username }}'; openResetModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[11px] font-semibold transition" title="Reset Password">
                                            Reset Password
                                        </button>
                                        <form action="{{ route('admin.users.toggle_active', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Ubah status aktif akun ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 {{ $u->active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400' }} rounded-lg text-[11px] font-semibold transition">
                                                {{ $u->active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data akun guru ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gurus->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $gurus->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Reset Password -->
    <div x-show="openResetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;" x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-xl" @click.away="openResetModal = false">
            <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Reset Password Akun Guru</h4>
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
