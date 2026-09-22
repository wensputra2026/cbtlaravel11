@extends('layouts.admin')

@section('title', 'Pemeliharaan Sistem & Database')
@section('page_title', 'Pemeliharaan Sistem, Cache, & Reset Kunci Sesi')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div>
        <h3 class="text-base font-bold text-slate-900">Status Infrastruktur & Utilitas Server CBT</h3>
        <p class="text-xs text-slate-500">Kelola pembersihan cache, reset kunci login multi-perangkat (single-device lock), dan monitoring kapasitas</p>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <span class="text-xs font-semibold text-slate-500 block mb-1">Database Aktif</span>
            <div class="text-lg font-mono font-black text-brand-600 truncate">{{ $dbName }}</div>
            <div class="text-[11px] text-slate-500 mt-1">MySQL Pure CBT</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <span class="text-xs font-semibold text-slate-500 block mb-1">Rekaman Sesi Ujian</span>
            <div class="text-2xl font-black text-slate-900">{{ number_format($totalSessions) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Tabel cbt_siswa</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <span class="text-xs font-semibold text-slate-500 block mb-1">Sisa Ruang Harddisk</span>
            <div class="text-2xl font-black text-emerald-600">{{ $freeDisk }} GB</div>
            <div class="text-[11px] text-slate-500 mt-1">Dari total {{ $totalDisk }} GB</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <span class="text-xs font-semibold text-slate-500 block mb-1">Status Offline LAN</span>
            <div class="text-2xl font-black text-teal-600">100% Ready</div>
            <div class="text-[11px] text-slate-500 mt-1">Zero External CDN</div>
        </div>
    </div>

    <!-- Actions Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. Reset Single-Device Locks -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-200">
                        🔓
                    </span>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Reset Seluruh Kunci Login Perangkat</h4>
                        <p class="text-xs text-slate-500">Single-device lock cache di Redis</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    Gunakan fitur ini jika ada banyak siswa yang terkendala login karena pesan <em>"Akun sedang aktif di perangkat lain"</em> setelah mati lampu, reboot client, atau pergantian unit PC/laptop di lab.
                </p>
            </div>
            <div>
                <form action="{{ route('admin.setting.reset_device_locks') }}" method="POST" onsubmit="return confirm('Buka dan reset seluruh kunci perangkat siswa sekarang?')">
                    @csrf
                    <button type="submit" class="w-full py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        <span>Reset Kunci Login Perangkat</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. Bersihkan Riwayat Ujian yang Telah Selesai -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg border border-rose-200">
                        🧹
                    </span>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Bersihkan Riwayat Ujian Selesai</h4>
                        <p class="text-xs text-slate-500">Pembersihan berkala database pengerjaan</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    Menghapus data sesi pengerjaan ujian yang sudah berstatus <strong>Selesai (Status 2)</strong>. Gunakan menjelang semester baru atau setelah seluruh nilai telah diekspor ke format CSV.
                </p>
            </div>
            <div>
                <form action="{{ route('admin.setting.clear_completed_sessions') }}" method="POST" onsubmit="return confirm('PERINGATAN: Pastikan Anda telah mengekspor nilai siswa. Hapus riwayat ujian selesai?')">
                    @csrf
                    <button type="submit" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Bersihkan Sesi Selesai</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
