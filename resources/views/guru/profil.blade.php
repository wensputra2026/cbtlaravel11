@extends('layouts.guru')

@section('title', 'Profil & Keamanan')
@section('page_title', 'Profil Guru & Pengaturan Akun')

@section('content')
<div class="space-y-6 w-full">

    <!-- Grid Full Width: Profil (Left) & Ganti Password (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">
        
        <!-- Profile Overview Card (Col 1) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col items-center text-center justify-between">
            <div class="space-y-4 flex flex-col items-center">
                <div class="relative">
                    <img src="{{ $guru?->foto_url ?? asset('assets/img/guru-default.png') }}" alt="{{ $guru?->nama_guru }}" class="w-28 h-28 rounded-2xl object-cover border-4 border-emerald-500/20 shadow-xs">
                    <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white" title="Akun Aktif"></span>
                </div>
                
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Guru & Pengawas CBT
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">{{ $guru?->nama_guru ?? Auth::user()->first_name ?? Auth::user()->username }}</h3>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">NIP: {{ $guru?->nip ?? '-' }}</p>
                </div>
            </div>

            <div class="w-full mt-6 pt-4 border-t border-slate-100 space-y-2 text-xs text-left">
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Username Login</span>
                    <strong class="font-mono text-emerald-600 font-bold">{{ Auth::user()->username }}</strong>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Status Akun</span>
                    <span class="text-emerald-600 font-bold">&check; Aktif</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Akses Ruangan</span>
                    <span class="text-slate-800 font-semibold">Pengawas Ruang</span>
                </div>
            </div>
        </div>

        <!-- Change Password Form Card (Col 2 & 3) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-xs">
            <div class="pb-4 border-b border-slate-100 mb-6">
                <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">🔒</span>
                    <span>Perbarui Kata Sandi Akun Guru</span>
                </h4>
                <p class="text-xs text-slate-500 mt-1">
                    Gunakan kata sandi yang kuat dan mudah Anda ingat untuk mengamankan bank soal dan rekaman nilai siswa.
                </p>
            </div>

            <form action="{{ route('guru.profil.password') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                        <input type="password" name="password_baru" required minlength="4" placeholder="Minimal 4 karakter" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-emerald-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_baru_confirmation" required minlength="4" placeholder="Ulangi kata sandi baru" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-emerald-500 transition">
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs leading-relaxed">
                    <strong>Catatan Keamanan:</strong> Setelah mengganti kata sandi, gunakan kata sandi baru ini saat login ke aplikasi CBT pada sesi berikutnya.
                </div>

                <div class="pt-3 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
