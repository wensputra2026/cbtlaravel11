@extends('layouts.admin')

@section('title', 'Identitas Sekolah & Aplikasi')
@section('page_title', 'Pengaturan Identitas Sekolah & Logo CBT')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Konfigurasi Identitas Lembaga & Branding</h3>
            <p class="text-xs text-slate-500">Pembaruan nama sekolah, logo, dan nama aplikasi akan otomatis tampil di seluruh halaman, kartu ujian, berita acara, dan daftar hadir</p>
        </div>
    </div>

    <!-- Main Setting Form Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <form action="{{ route('admin.setting.identitas.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Identitas Lembaga & Aplikasi -->
            <div>
                <h4 class="font-bold text-sm text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <span class="text-brand-600">🏫</span> Informasi Umum Lembaga
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Resmi Sekolah / Madrasah</label>
                        <input type="text" name="sekolah" value="{{ old('sekolah', $setting->sekolah ?? 'SMA Negeri Benlutu') }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Aplikasi CBT</label>
                        <input type="text" name="nama_aplikasi" value="{{ old('nama_aplikasi', $setting->nama_aplikasi ?? 'CBT-SMANBEN') }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-brand-600 font-bold focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NPSN</label>
                        <input type="text" name="npsn" value="{{ old('npsn', $setting->npsn ?? '50309196') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / Kontak</label>
                        <input type="text" name="telp" value="{{ old('telp', $setting->telp ?? '-') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kepala Sekolah / Penanggung Jawab</label>
                        <input type="text" name="kepsek" value="{{ old('kepsek', $setting->kepsek ?? 'Getreda Yosmi Oematan,S.Pd,M.Pd') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIP Kepala Sekolah</label>
                        <input type="text" name="nip" value="{{ old('nip', $setting->nip ?? '-') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kota / Kabupaten</label>
                        <input type="text" name="kota" value="{{ old('kota', $setting->kota ?? 'Timor Tengah Selatan') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                        <input type="text" name="alamat" value="{{ old('alamat', $setting->alamat ?? 'Jl. Timor Raya Km.11 Soe-Kupang') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500">
                    </div>
                </div>
            </div>

            <!-- Section 2: Logo & Branding Berkas -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="font-bold text-sm text-slate-900 mb-1 flex items-center gap-2">
                    <span class="text-amber-500">🖼️</span> Berkas Logo, Wallpaper, & Tanda Tangan
                </h4>
                <p class="text-xs text-slate-500 mb-4">Aset grafis resmi sekolah untuk kop dokumen, background login, dan stempel kartu ujian</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Logo Kiri -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col justify-between items-center text-center">
                        <span class="text-xs font-bold text-slate-700 mb-2">Logo Kiri (Sekolah / Tut Wuri)</span>
                        <div class="w-20 h-20 bg-white border border-slate-200 rounded-xl flex items-center justify-center p-2 mb-3 shadow-xs">
                            <img src="{{ $setting->logo_kiri_url }}" alt="Logo Kiri" class="max-h-full max-w-full object-contain">
                        </div>
                        <label class="w-full">
                            <input type="file" name="logo_kiri" accept="image/*" class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 w-full cursor-pointer">
                        </label>
                    </div>

                    <!-- Logo Kanan -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col justify-between items-center text-center">
                        <span class="text-xs font-bold text-slate-700 mb-2">Logo Kanan (Provinsi / Dinas)</span>
                        <div class="w-20 h-20 bg-white border border-slate-200 rounded-xl flex items-center justify-center p-2 mb-3 shadow-xs">
                            <img src="{{ $setting->logo_kanan_url }}" alt="Logo Kanan" class="max-h-full max-w-full object-contain">
                        </div>
                        <label class="w-full">
                            <input type="file" name="logo_kanan" accept="image/*" class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 w-full cursor-pointer">
                        </label>
                    </div>

                    <!-- Background Login -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col justify-between items-center text-center">
                        <span class="text-xs font-bold text-slate-700 mb-2">Wallpaper Background Login</span>
                        <div class="w-20 h-20 bg-white border border-slate-200 rounded-xl flex items-center justify-center p-1 mb-3 overflow-hidden shadow-xs">
                            <img src="{{ $setting->background_url }}" alt="Background" class="w-full h-full object-cover rounded-lg">
                        </div>
                        <label class="w-full">
                            <input type="file" name="background" accept="image/*" class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 w-full cursor-pointer">
                        </label>
                    </div>

                    <!-- Tanda Tangan & Cap -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col justify-between items-center text-center">
                        <span class="text-xs font-bold text-slate-700 mb-2">Tanda Tangan & Cap Stempel</span>
                        <div class="w-20 h-20 bg-white border border-slate-200 rounded-xl flex items-center justify-center p-2 mb-3 shadow-xs">
                            <img src="{{ $setting->tandatangan_url }}" alt="Tandatangan" class="max-h-full max-w-full object-contain">
                        </div>
                        <label class="w-full">
                            <input type="file" name="tandatangan" accept="image/*" class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 w-full cursor-pointer">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Terapkan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
