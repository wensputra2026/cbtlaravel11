@extends('layouts.admin')

@section('title', 'Manajemen User Administrator')
@section('page_title', 'Manajemen User Administrator')

@section('content')
<div class="space-y-6" x-data="{ openAddModal: false, openResetModal: false, selectedUserId: null, selectedUsername: '' }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Daftar Akun Administrator</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pengguna dengan hak akses penuh terhadap konfigurasi sistem, data master, dan ujian CBT</p>
        </div>
        <div>
            <button @click="openAddModal = true" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Admin Baru
            </button>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form action="{{ route('admin.users.admin') }}" method="GET" class="flex items-center gap-2 w-full sm:w-80">
            <div class="relative w-full">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau username admin..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if(request('q'))
                <a href="{{ route('admin.users.admin') }}" class="p-2 text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
        <span class="text-xs text-slate-500 dark:text-slate-400">Total: <strong class="text-slate-800 dark:text-white">{{ $admins->total() }}</strong> Akun Admin</span>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4">Role Akses</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($admins as $idx => $admin)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ $admins->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($admin->first_name ?? $admin->username, 0, 1)) }}
                                </div>
                                <span>{{ $admin->first_name ?? 'Administrator' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-600 dark:text-brand-400">{{ $admin->username }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    Super Admin
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($admin->active)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="selectedUserId = {{ $admin->id }}; selectedUsername = '{{ $admin->username }}'; openResetModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[11px] font-semibold transition" title="Reset Password">
                                        Reset Password
                                    </button>
                                    @if($admin->id !== 1 && $admin->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle_active', $admin->id) }}" method="POST" class="inline" onsubmit="return confirm('Ubah status aktif akun ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 {{ $admin->active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400' }} rounded-lg text-[11px] font-semibold transition">
                                                {{ $admin->active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data administrator.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admins->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $admins->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Admin -->
    <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;" x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 w-full max-w-md shadow-xl" @click.away="openAddModal = false">
            <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Tambah Akun Administrator</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Buat akun pengelola CBT dengan akses penuh sistem.</p>
            <form action="{{ route('admin.users.admin.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="first_name" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Username Login</label>
                    <input type="text" name="username" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kata Sandi (Password)</label>
                    <input type="password" name="password" required minlength="4" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold">Simpan Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div x-show="openResetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;" x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-xl" @click.away="openResetModal = false">
            <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Reset Password Akun</h4>
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
