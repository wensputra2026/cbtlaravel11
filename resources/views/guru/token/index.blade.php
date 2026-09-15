@extends('layouts.guru')

@section('title', 'Token Ujian')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="guruTokenHandler({{ $tokenTtl }}, '{{ $currentToken }}')">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-wide">Token Ujian Aktif</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Gunakan kode token ini untuk mengizinkan peserta memasuki ruang ujian.</p>
        </div>
        <div>
            <button @click="fetchToken()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-white text-xs font-bold rounded-xl transition flex items-center gap-2 border border-slate-200 dark:border-slate-700 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Perbarui Token</span>
            </button>
        </div>
    </div>

    <!-- Card Token Utama -->
    <div class="bg-gradient-to-br from-emerald-50/60 via-white to-teal-50/40 dark:from-slate-900 dark:via-slate-900 dark:to-emerald-950/30 border border-emerald-200 dark:border-emerald-500/30 rounded-3xl p-8 sm:p-12 shadow-sm dark:shadow-2xl text-center relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center justify-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            KODE TOKEN RESMI CBT HARI INI
        </div>

        <div class="font-mono text-5xl sm:text-7xl font-black text-slate-900 dark:text-white tracking-widest my-6 select-all drop-shadow-xs" x-text="token">
            {{ $currentToken }}
        </div>

        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 shadow-xs">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Masa Berlaku:</span>
            <strong class="font-mono text-emerald-600 dark:text-emerald-400 font-bold" x-text="formatTtl(ttl)">-</strong>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
            Bacakan atau tuliskan kode token 6 digit di papan tulis ruang ujian setelah seluruh peserta siap di meja masing-masing.
        </div>
    </div>

    <!-- Petunjuk Pengawas -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-3">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Panduan Pengawas Ruang Terkait Token:
        </h3>
        <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
            <li>Siswa hanya membutuhkan token saat pertama kali memulai ujian pada halaman konfirmasi tes.</li>
            <li>Jika koneksi siswa terputus atau berganti perangkat, siswa dapat melanjutkan pengerjaan tanpa perlu meminta token kembali selama sesi masih valid.</li>
            <li>Token dapat diperbarui sewaktu-waktu oleh proktor utama. Jika ada peserta mengalami kendala token tidak valid, tekan tombol <strong>Perbarui Token</strong> di atas untuk mendapatkan kode terbaru.</li>
        </ul>
    </div>
</div>

<script>
    function guruTokenHandler(initialTtl, initialToken) {
        return {
            token: initialToken,
            ttl: initialTtl,
            loading: false,
            timer: null,
            init() {
                this.startCountdown();
            },
            startCountdown() {
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => {
                    if (this.ttl > 0) {
                        this.ttl--;
                    } else {
                        this.fetchToken();
                    }
                }, 1000);
            },
            formatTtl(seconds) {
                if (seconds <= 0) return '00:00';
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
            },
            async fetchToken() {
                this.loading = true;
                try {
                    const res = await fetch('{{ route('guru.token.api') }}');
                    const data = await res.json();
                    if (data.token) {
                        this.token = data.token;
                        this.ttl = data.ttl || 900;
                    }
                } catch (e) {
                    console.error('Failed to sync token', e);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection
