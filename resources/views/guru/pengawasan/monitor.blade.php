@extends('layouts.guru')

@section('title', 'Live Monitoring Pengawasan')
@section('page_title', 'Live Monitoring Ruang Ujian')

@section('content')
<div class="space-y-6" x-data="proctorMonitoring({
    jadwalId: {{ $jadwalId }},
    initialData: {{ Js::from($initialData) }},
    routes: {
        poll: '{{ route('proctor.api.poll', $jadwalId) }}',
        resetLogin: '{{ url('/proctor/action/reset-login') }}',
        extendTime: '{{ url('/proctor/action/extend-time') }}',
        forceSubmit: '{{ url('/proctor/action/force-submit') }}'
    }
})">

    <!-- Header Actions & Token -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('guru.pengawasan.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Pengawasan
                </a>
            </div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="data.jadwal.nama_ujian">Monitoring Ujian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Mapel: <span class="text-slate-700 dark:text-slate-200 font-semibold" x-text="data.jadwal.mapel">-</span> &bull; 
                Durasi: <span class="text-slate-700 dark:text-slate-200 font-semibold" x-text="data.jadwal.durasi + ' Menit'">-</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-slate-50 dark:bg-slate-950 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Token:</span>
                <span class="font-mono text-base font-black text-emerald-600 dark:text-emerald-400">{{ $currentToken }}</span>
            </div>
            <button @click="fetchData()" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-white rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" :class="{ 'animate-spin': isPolling }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">Total Peserta</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="data.statistik.total">0</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 block mb-1">Sedang Mengerjakan</span>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-300" x-text="data.statistik.mengerjakan">0</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 block mb-1">Telah Selesai</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-300" x-text="data.statistik.selesai">0</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 block mb-1">Pelanggaran Terdeteksi</span>
            <div class="text-2xl font-black text-rose-600 dark:text-rose-400" x-text="data.statistik.pelanggaran">0</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="w-full sm:w-80">
            <input type="text" x-model="searchQuery" placeholder="Cari nama atau NISN peserta..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto">
            <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition">
                Semua
            </button>
            <button @click="statusFilter = 'mengerjakan'" :class="statusFilter === 'mengerjakan' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition">
                Mengerjakan
            </button>
            <button @click="statusFilter = 'selesai'" :class="statusFilter === 'selesai' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition">
                Selesai
            </button>
            <button @click="statusFilter = 'pelanggaran'" :class="statusFilter === 'pelanggaran' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition">
                Pelanggaran
            </button>
        </div>
    </div>

    <!-- Student Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="p in filteredPeserta" :key="p.id_siswa">
            <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 transition shadow-sm"
                 :class="{
                     'border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/10': p.pelanggaran > 0,
                     'border-amber-500/40': p.status_code == 1 && p.pelanggaran == 0,
                     'border-emerald-500/30': p.status_code == 2,
                     'border-slate-200 dark:border-slate-800': p.status_code == 0
                 }">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-xs" x-text="p.nama"></h4>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" x-text="p.nisn + ' • ' + p.kelas"></div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                          :class="{
                              'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30': p.status_code == 2,
                              'bg-amber-50 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 animate-pulse': p.status_code == 1,
                              'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400': p.status_code == 0
                          }"
                          x-text="p.status_text">
                    </span>
                </div>

                <div class="bg-slate-50 dark:bg-slate-950/80 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/80 my-3 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Sisa Waktu:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-white" x-text="p.sisa_waktu_formatted">--:--</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Terjawab:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="p.terjawab + ' / ' + data.jadwal.total_soal">0/0</span>
                    </div>
                    <div x-show="p.pelanggaran > 0">
                        <span class="text-[10px] text-rose-600 dark:text-rose-400 block">Pelanggaran:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400" x-text="p.pelanggaran + 'x'">0x</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                    <button @click="resetLogin(p)" class="flex-1 py-1 px-2 text-[11px] font-semibold bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 rounded-lg transition">
                        Reset Login
                    </button>
                    <button @click="extendTime(p)" class="flex-1 py-1 px-2 text-[11px] font-semibold bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/30 rounded-lg transition">
                        +15 Menit
                    </button>
                </div>
            </div>
        </template>
    </div>

</div>

<script>
function proctorMonitoring(config) {
    return {
        jadwalId: config.jadwalId,
        data: Object.assign({
            jadwal: { nama_ujian: 'Monitoring Ujian', mapel: '-', durasi: 0 },
            statistik: { total: 0, mengerjakan: 0, selesai: 0, pelanggaran: 0 },
            summary: { total_peserta: 0, belum_mulai: 0, sedang_ujian: 0, selesai: 0, pelanggaran: 0 },
            peserta: []
        }, config.initialData || {}),
        routes: config.routes,
        searchQuery: '',
        statusFilter: 'all',
        isPolling: false,
        pollInterval: null,

        init() {
            this.startPolling();
        },

        startPolling() {
            this.pollInterval = setInterval(() => {
                this.fetchData();
            }, 6000);
        },

        async fetchData() {
            this.isPolling = true;
            try {
                let res = await fetch(this.routes.poll);
                let json = await res.json();
                if (json.success) {
                    this.data = json.data;
                }
            } catch(e) {}
            this.isPolling = false;
        },

        get filteredPeserta() {
            let list = this.data.peserta || [];
            if (this.searchQuery) {
                let q = this.searchQuery.toLowerCase();
                list = list.filter(p => p.nama.toLowerCase().includes(q) || (p.nisn && p.nisn.includes(q)));
            }
            if (this.statusFilter === 'mengerjakan') {
                list = list.filter(p => p.status_code == 1);
            } else if (this.statusFilter === 'selesai') {
                list = list.filter(p => p.status_code == 2);
            } else if (this.statusFilter === 'pelanggaran') {
                list = list.filter(p => p.pelanggaran > 0);
            }
            return list;
        },

        async resetLogin(peserta) {
            if (!confirm(`Reset izin login untuk ${peserta.nama}?`)) return;
            try {
                let res = await fetch(this.routes.resetLogin, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ id_siswa: peserta.id_siswa, id_jadwal: this.jadwalId })
                });
                let data = await res.json();
                alert(data.message || 'Siswa berhasil di-reset');
                this.fetchData();
            } catch(e) {
                alert('Gagal me-reset login');
            }
        },

        async extendTime(peserta) {
            let addMinutes = prompt(`Tambah durasi pengerjaan untuk ${peserta.nama} (dalam menit):`, '15');
            if (!addMinutes) return;
            try {
                let res = await fetch(this.routes.extendTime, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ id_siswa: peserta.id_siswa, id_jadwal: this.jadwalId, minutes: parseInt(addMinutes) })
                });
                let data = await res.json();
                alert(data.message || 'Waktu berhasil ditambahkan');
                this.fetchData();
            } catch(e) {
                alert('Gagal menambah waktu');
            }
        }
    };
}
</script>
@endsection
