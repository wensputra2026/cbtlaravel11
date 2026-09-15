@extends('layouts.admin')

@section('title', 'Token Ujian Dinamis')
@section('page_title', 'Pengelolaan Token Ujian Dinamis (Redis Cache)')

@section('content')
<div class="space-y-6" x-data="{
    token: '{{ $currentToken }}',
    ttl: {{ $tokenTtl }},
    timer: null,
    init() {
        this.startTimer();
    },
    startTimer() {
        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => {
            if (this.ttl > 0) {
                this.ttl--;
            } else {
                this.refreshToken();
            }
        }, 1000);
    },
    async refreshToken() {
        try {
            let res = await fetch('{{ route('admin.cbt.token.api_get') }}');
            let data = await res.json();
            this.token = data.token;
            this.ttl = data.ttl;
        } catch(e) {}
    },
    formatTime(sec) {
        let m = Math.floor(sec / 60);
        let s = sec % 60;
        return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
    }
}">

    <!-- Big Token Display Card -->
    <div class="bg-gradient-to-br from-white via-slate-50 to-indigo-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-brand-950/40 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-sm dark:shadow-xl text-center relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="max-w-md mx-auto space-y-4 relative z-10">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30 tracking-wider uppercase">
                Token Ujian Saat Ini
            </span>
            
            <div class="py-4">
                <div class="font-mono text-6xl sm:text-7xl font-black tracking-widest text-slate-800 dark:text-transparent dark:bg-clip-text dark:bg-gradient-to-r dark:from-white dark:via-indigo-200 dark:to-brand-300 select-all" x-text="token">
                    {{ $currentToken }}
                </div>
            </div>

            <div class="flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <svg class="w-4 h-4 text-amber-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Masa Berlaku Token: <strong class="text-amber-600 dark:text-amber-300 font-mono" x-text="formatTime(ttl)">--:--</strong></span>
            </div>

            <div class="pt-4 flex items-center justify-center gap-3">
                <form action="{{ route('admin.cbt.token.generate') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Rilis Token Baru Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Token Information & Instructions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                <span class="text-brand-600 dark:text-brand-400">💡</span> Cara Kerja Token Dinamis
            </h4>
            <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-2 leading-relaxed">
                <li>&bull; Token disimpan di <strong>Redis Cache</strong> dengan masa kedaluwarsa 15 menit.</li>
                <li>&bull; Siswa yang akan memulai ujian wajib memasukkan token ini pada antarmuka pengerjaan ujian.</li>
                <li>&bull; Guru pengawas dapat melihat token yang sama secara serentak di <em>Dashboard Pengawas</em>.</li>
                <li>&bull; Jika masa kedaluwarsa habis atau tombol <em>Rilis Token Baru</em> ditekan, token baru otomatis di-broadcast.</li>
            </ul>
        </div>

        <!-- History Log Token Terakhir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="text-slate-400">📜</span> Riwayat 10 Token Terakhir
            </h4>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60 max-h-48 overflow-y-auto">
                @forelse($history as $h)
                    <div class="py-2 flex items-center justify-between text-xs">
                        <span class="font-mono font-bold text-brand-600 dark:text-brand-300">{{ $h->token }}</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">{{ $h->updated }}</span>
                    </div>
                @empty
                    <div class="py-4 text-center text-xs text-slate-400">Belum ada riwayat token.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
