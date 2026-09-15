@extends('layouts.admin')

@section('title', 'Log Aktivitas Pengguna')
@section('page_title', 'Log Aktivitas Sistem & Pengguna')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Rekam Jejak Aktivitas (Audit Log)</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pencatatan login, perubahan konfigurasi, serta aktivitas operasional CBT secara real-time</p>
        </div>
        <div>
            <form action="{{ route('admin.setting.database.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh riwayat log aktivitas?')">
                @csrf
                <input type="hidden" name="mode" value="logs">
                <button type="submit" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Kosongkan Log
                </button>
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
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Role / Group</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($logs as $idx => $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ is_object($logs) && method_exists($logs, 'firstItem') ? $logs->firstItem() + $idx : $idx + 1 }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-slate-500 text-[11px]">
                                {{ $log->log_time ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">
                                <div>{{ $log->first_name ?? $log->username ?? 'User #' . $log->id_user }}</div>
                                @if($log->username)
                                    <div class="text-[10px] font-mono text-brand-600 dark:text-brand-400">{{ $log->username }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $log->name_group ?? 'User' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-800 dark:text-slate-200 font-medium">{{ $log->log_desc ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px] whitespace-nowrap">
                                {{ $log->address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada riwayat aktivitas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(is_object($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
