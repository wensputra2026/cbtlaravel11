@extends('layouts.admin')

@section('title', 'Manajemen User Administrator')
@section('page_title', 'Manajemen User Administrator')

@section('content')
<div class="space-y-6" x-data="{ openAddModal: false, openResetModal: false, selectedUserId: null, selectedUsername: '' }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Daftar Akun Administrator</h3>
            <p class="text-xs text-slate-500">Pengguna dengan hak akses penuh terhadap konfigurasi sistem, data master, dan ujian CBT</p>
        </div>
        <div>
            <button @click="openAddModal = true" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Admin Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4">Role Akses</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($admins as $idx => $admin)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-600 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($admin->first_name ?? $admin->username, 0, 1)) }}
                                </div>
                                <span>{{ $admin->first_name ?? 'Administrator' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-600">{{ $admin->username }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200">
                                    Super Admin
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($admin->active)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-50 text-rose-600 border border-rose-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="selectedUserId = {{ $admin->id }}; selectedUsername = '{{ $admin->username }}'; openResetModal = true" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition" title="Reset Password">
                                        Reset Password
                                    </button>
                                    @if($admin->id !== 1 && $admin->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle_active', $admin->id) }}" method="POST" class="inline" onsubmit="return confirm('Ubah status aktif akun ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 {{ $admin->active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} rounded-lg text-[11px] font-semibold transition">
                                                {{ $admin->active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Belum ada data administrator.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Admin -->
    <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;" x-cloak>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 w-full max-w-md shadow-xl" @click.away="openAddModal = false">
            <h4 class="text-sm font-bold text-slate-800 mb-1">Tambah Akun Administrator</h4>
            <p class="text-xs text-slate-500 mb-4">Buat akun pengelola CBT dengan akses penuh sistem.</p>
            <form action="{{ route('admin.users.admin.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="first_name" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username Login</label>
                    <input type="text" name="username" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kata Sandi (Password)</label>
                    <input type="password" name="password" required minlength="4" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold">Simpan Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div x-show="openResetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;" x-cloak>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 w-full max-w-sm shadow-xl" @click.away="openResetModal = false">
            <h4 class="text-sm font-bold text-slate-800 mb-1">Reset Password Akun</h4>
            <p class="text-xs text-slate-500 mb-4">Ubah password untuk user <span class="font-bold text-brand-500" x-text="selectedUsername"></span></p>
            <form :action="'/admin/users/reset-password/' + selectedUserId" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru</label>
                    <input type="text" name="new_password" required minlength="4" placeholder="Masukkan password baru..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openResetModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
