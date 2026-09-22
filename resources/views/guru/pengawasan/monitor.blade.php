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
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('guru.pengawasan.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Pengawasan
                </a>
            </div>
            <h3 class="text-lg font-black text-slate-900" x-text="data.jadwal.nama_ujian">Monitoring Ujian</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Mapel: <span class="text-slate-800 font-bold" x-text="data.jadwal.mapel">-</span> &bull; 
                Durasi: <span class="text-slate-800 font-bold" x-text="data.jadwal.durasi + ' Menit'">-</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs text-slate-600">Token Ujian:</span>
                <span class="font-mono text-base font-black text-emerald-700">{{ $currentToken }}</span>
            </div>
            <button @click="fetchData()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold border border-slate-200 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" :class="{ 'animate-spin': isPolling }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-slate-500 block mb-1">Total Peserta</span>
            <div class="text-2xl font-black text-slate-900" x-text="data.statistik.total">0</div>
        </div>
        <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-amber-600 block mb-1">Sedang Mengerjakan</span>
            <div class="text-2xl font-black text-amber-600" x-text="data.statistik.mengerjakan">0</div>
        </div>
        <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 block mb-1">Telah Selesai</span>
            <div class="text-2xl font-black text-emerald-600" x-text="data.statistik.selesai">0</div>
        </div>
        <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-semibold text-rose-600 block mb-1">Pelanggaran Terdeteksi</span>
            <div class="text-2xl font-black text-rose-600" x-text="data.statistik.pelanggaran">0</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="w-full sm:w-80">
            <input type="text" x-model="searchQuery" placeholder="Cari nama atau NISN peserta..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto">
            <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 transition">
                Semua
            </button>
            <button @click="statusFilter = 'mengerjakan'" :class="statusFilter === 'mengerjakan' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 transition">
                Mengerjakan
            </button>
            <button @click="statusFilter = 'selesai'" :class="statusFilter === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 transition">
                Selesai
            </button>
            <button @click="statusFilter = 'pelanggaran'" :class="statusFilter === 'pelanggaran' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 transition">
                Pelanggaran
            </button>
        </div>
    </div>

    <!-- Student Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="p in filteredPeserta" :key="p.id_siswa">
            <div class="bg-white border rounded-2xl p-4 transition shadow-xs"
                 :class="{
                     'border-rose-300 bg-rose-50/30': p.pelanggaran > 0,
                     'border-amber-300 bg-amber-50/20': p.status_code == 1 && p.pelanggaran == 0,
                     'border-emerald-300 bg-emerald-50/20': p.status_code == 2,
                     'border-slate-200': p.status_code == 0
                 }">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs" x-text="p.nama"></h4>
                        <div class="text-[11px] text-slate-500 font-mono" x-text="p.nisn + ' • ' + p.kelas"></div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                          :class="{
                              'bg-emerald-100 text-emerald-800 border border-emerald-200': p.status_code == 2,
                              'bg-amber-100 text-amber-800 border border-amber-200 animate-pulse': p.status_code == 1,
                              'bg-slate-100 text-slate-600 border border-slate-200': p.status_code == 0
                          }"
                          x-text="p.status_text">
                    </span>
                </div>

                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 my-3 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-slate-500 block">Sisa Waktu:</span>
                        <span class="font-mono font-bold text-slate-900" x-text="p.sisa_waktu_formatted">--:--</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 block">Terjawab:</span>
                        <span class="font-bold text-emerald-700" x-text="p.terjawab + ' / ' + data.jadwal.total_soal">0/0</span>
                    </div>
                    <div x-show="p.pelanggaran > 0">
                        <span class="text-[10px] text-rose-600 block font-semibold">Pelanggaran:</span>
                        <span class="font-bold text-rose-600" x-text="p.pelanggaran + 'x'">0x</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                    <button @click="resetLogin(p)" class="flex-1 py-1 px-2 text-[11px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg transition">
                        Reset Login
                    </button>
                    <button @click="extendTime(p)" class="flex-1 py-1 px-2 text-[11px] font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg transition">
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
        data: config.initialData,
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
            let confirmed = false;
            if (window.Swal) {
                const res = await Swal.fire({
                    title: 'Reset Izin Login?',
                    html: `Apakah Anda yakin ingin membuka kunci login untuk <b>${peserta.nama}</b>?<br><span class="text-xs text-slate-500">Siswa akan dapat login kembali dari perangkat baru.</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Reset Login',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });
                confirmed = res.isConfirmed;
            } else {
                confirmed = confirm(`Reset izin login untuk ${peserta.nama}?`);
            }

            if (!confirmed) return;

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
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Siswa berhasil di-reset.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(data.message || 'Siswa berhasil di-reset');
                }
                this.fetchData();
            } catch(e) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat me-reset login siswa.'
                    });
                } else {
                    alert('Gagal me-reset login');
                }
            }
        },

        async extendTime(peserta) {
            let addMinutes = null;
            if (window.Swal) {
                const res = await Swal.fire({
                    title: 'Tambah Waktu Ujian',
                    html: `Masukkan tambahan durasi pengerjaan untuk <b>${peserta.nama}</b>:`,
                    input: 'number',
                    inputValue: 15,
                    inputAttributes: {
                        min: 1,
                        max: 180,
                        step: 1
                    },
                    inputLabel: 'Durasi (dalam menit):',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Tambahkan Waktu',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    inputValidator: (val) => {
                        if (!val || parseInt(val) <= 0) {
                            return 'Harap masukkan jumlah menit yang valid (minimal 1 menit)!';
                        }
                    }
                });
                if (res.isConfirmed) {
                    addMinutes = res.value;
                }
            } else {
                addMinutes = prompt(`Tambah durasi pengerjaan untuk ${peserta.nama} (dalam menit):`, '15');
            }

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
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Waktu Ditambahkan!',
                        text: data.message || `Waktu pengerjaan bertambah ${addMinutes} menit.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(data.message || 'Waktu berhasil ditambahkan');
                }
                this.fetchData();
            } catch(e) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menambah durasi ujian.'
                    });
                } else {
                    alert('Gagal menambah waktu');
                }
            }
        }
    };
}
</script>
@endsection
