@extends('layouts.admin')

@section('title', 'Manajer Database & Pemeliharaan')
@section('page_title', 'Manajemen Database & Pemeliharaan Sistem')

@section('content')
<div class="space-y-6">

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Nama Database</span>
                    <h3 class="text-lg font-mono font-black text-slate-800 dark:text-white mt-1">{{ $dbName }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Koneksi aktif MySQL / MariaDB</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Tabel</span>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mt-1">{{ $tableCount }} <span class="text-xs font-normal text-slate-400">tabel</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Struktur skema CBT murni</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ukuran Penyimpanan</span>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mt-1">{{ $sizeMb }} <span class="text-xs font-normal text-slate-400">MB</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Data & indeks gabungan</p>
        </div>
    </div>

    <!-- Maintenance Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Backup Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Backup Database Lengkap (.SQL)</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ekspor seluruh tabel, data master, bank soal, dan hasil ujian</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Unduh salinan cadangan database dalam format SQL terkompresi. File ini dapat langsung diimpor ke database MySQL baru atau digunakan untuk restore pemulihan darurat.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.setting.database.backup') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download SQL Backup Sekarang
                </a>
            </div>
        </div>

        <!-- Cleanup Routine Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">Pembersihan Sesi & Pemeliharaan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Optimalisasi performa server dan pembersihan data berkala</p>
                </div>
            </div>

            <div class="space-y-3 pt-1">
                <form action="{{ route('admin.setting.database.clear') }}" method="POST" onsubmit="return confirm('Reset seluruh kunci perangkat (device lock) siswa?')">
                    @csrf
                    <input type="hidden" name="mode" value="locks">
                    <button type="submit" class="w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 transition">
                        <span>🔓 Reset Kunci Perangkat Siswa (Device Lock)</span>
                        <span class="text-[10px] text-brand-600 dark:text-brand-400 font-bold">Eksekusi &rarr;</span>
                    </button>
                </form>

                <form action="{{ route('admin.setting.database.clear') }}" method="POST" onsubmit="return confirm('Kosongkan riwayat log aktivitas pengguna?')">
                    @csrf
                    <input type="hidden" name="mode" value="logs">
                    <button type="submit" class="w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 transition">
                        <span>🧹 Kosongkan Tabel Log Aktivitas</span>
                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">Eksekusi &rarr;</span>
                    </button>
                </form>

                <form action="{{ route('admin.setting.database.clear') }}" method="POST" onsubmit="return confirm('Hapus seluruh sesi ujian yang berstatus SELESAI? Data nilai yang sudah direkap tetap aman.')">
                    @csrf
                    <input type="hidden" name="mode" value="completed">
                    <button type="submit" class="w-full flex items-center justify-between px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 border border-rose-200 dark:border-rose-800/60 rounded-xl text-xs font-semibold text-rose-700 dark:text-rose-300 transition">
                        <span>🗑️ Bersihkan Sesi Ujian Berstatus Selesai</span>
                        <span class="text-[10px] text-rose-600 dark:text-rose-400 font-bold">Eksekusi &rarr;</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Table Status Details -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <h4 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Daftar Status Tabel Database</h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Pemeriksaan ukuran record data dan engine database MySQL saat ini</p>
        
        <div class="overflow-x-auto max-h-96">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800 sticky top-0">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Tabel</th>
                        <th class="py-3 px-4">Engine</th>
                        <th class="py-3 px-4 text-right">Estimasi Baris</th>
                        <th class="py-3 px-4 text-right">Ukuran Data</th>
                        <th class="py-3 px-4 text-right">Ukuran Indeks</th>
                        <th class="py-3 px-4">Collation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($tables as $idx => $t)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-4 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-800 dark:text-white">{{ $t->Name }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $t->Engine }}</td>
                            <td class="py-2.5 px-4 text-right font-mono">{{ number_format($t->Rows ?? 0) }}</td>
                            <td class="py-2.5 px-4 text-right font-mono">{{ round(($t->Data_length ?? 0) / 1024, 1) }} KB</td>
                            <td class="py-2.5 px-4 text-right font-mono">{{ round(($t->Index_length ?? 0) / 1024, 1) }} KB</td>
                            <td class="py-2.5 px-4 text-slate-400 text-[11px]">{{ $t->Collation }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
