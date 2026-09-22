<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Monitoring: {{ $jadwal->bankSoal->bank_nama ?? 'Ujian' }} | {{ $appSetting->nama_aplikasi_tampil }}</title>
    <link rel="icon" type="image/png" href="{{ $appSetting->favicon_url ?? asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ $appSetting->favicon_url ?? asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2.min.css') }}">
    <script src="{{ asset('assets/vendor/sweetalert2.all.min.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/alpine.min.js') }}"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        body { background-color: #f8fafc; color: #0f172a; min-height: 100vh; }
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .container { max-width: 1300px; margin: 28px auto; padding: 0 20px; }
        
        /* Stat Cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .stat-label { font-size: 13px; color: #64748b; margin-bottom: 6px; font-weight: 600; }
        .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; }

        /* Filter & Search Bar */
        .toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .search-input {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 14px;
            color: #0f172a;
            font-size: 13px;
            outline: none;
            width: 280px;
        }
        .search-input:focus { border-color: #2563eb; }
        .filter-group { display: flex; gap: 8px; }
        .filter-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .filter-btn.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        /* Monitoring Table */
        .table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; padding: 12px 14px; background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        tr:hover td { background: #f8fafc; }

        .badge-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .status-0 { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .status-1 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-2 { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }

        .badge-violation {
            background: #fee2e2;
            color: #b91c1c;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
        }

        /* Action Buttons */
        .action-btns { display: flex; gap: 6px; }
        .btn-action {
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .btn-action:hover { opacity: 0.85; }
        .btn-reset { background: #0284c7; color: white; }
        .btn-extra { background: #16a34a; color: white; }
        .btn-force { background: #dc2626; color: white; }
    </style>
</head>
<body x-data="proctorMonitor({{ $jadwalId }})" x-init="init()">
    <nav class="navbar">
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="{{ route('proctor.index') }}" style="color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 600;">&larr; Kembali ke Jadwal</a>
            <h1 style="font-size: 16px; font-weight: 700; color: #fff;">
                {{ $jadwal->bankSoal->bank_nama ?? 'Live Monitoring' }} &bull; {{ $jadwal->bankSoal->mapel->nama_mapel ?? '' }}
            </h1>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 12px; color: #94a3b8;">Status Pembaruan:</span>
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
            <span style="font-size: 12px; color: #34d399; font-weight: 600;" x-text="lastUpdatedText">Live</span>
        </div>
    </nav>

    <div class="container">
        <!-- Statistik Peserta -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">TOTAL PESERTA</div>
                <div class="stat-value" x-text="summary.total_peserta">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label" style="color: #94a3b8;">BELUM MULAI</div>
                <div class="stat-value" style="color: #94a3b8;" x-text="summary.belum_mulai">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label" style="color: #fbbf24;">SEDANG MENGERJAKAN</div>
                <div class="stat-value" style="color: #fbbf24;" x-text="summary.sedang_ujian">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label" style="color: #34d399;">TELAH SELESAI</div>
                <div class="stat-value" style="color: #34d399;" x-text="summary.selesai">0</div>
            </div>
        </div>

        <!-- Toolbar Pencarian & Filter -->
        <div class="toolbar">
            <input type="text" class="search-input" placeholder="Cari Nama / NISN Siswa..." x-model="searchQuery">

            <div class="filter-group">
                <button class="filter-btn" :class="{ 'active': currentFilter === 'all' }" @click="currentFilter = 'all'">Semua</button>
                <button class="filter-btn" :class="{ 'active': currentFilter === 'sedang' }" @click="currentFilter = 'sedang'">Sedang Ujian</button>
                <button class="filter-btn" :class="{ 'active': currentFilter === 'selesai' }" @click="currentFilter = 'selesai'">Selesai</button>
                <button class="filter-btn" :class="{ 'active': currentFilter === 'belum' }" @click="currentFilter = 'belum'">Belum Mulai</button>
            </div>
        </div>

        <!-- Tabel Monitoring Peserta -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>NISN / NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Jawaban</th>
                        <th>Sisa Waktu</th>
                        <th>Pelanggaran</th>
                        <th>Aksi Proktor</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="p in filteredPeserta" :key="p.id_siswa">
                        <tr>
                            <td>
                                <strong style="color: #0f172a;" x-text="p.nisn || '-'"></strong><br>
                                <span style="font-size: 11px; color: #64748b;" x-text="p.nis || ''"></span>
                            </td>
                            <td>
                                <strong style="color: #0f172a;" x-text="p.nama"></strong>
                                <template x-if="p.device_locked">
                                    <span style="display: block; font-size: 10px; color: #0284c7; font-weight: 600;">🔒 Device Terkunci</span>
                                </template>
                            </td>
                            <td x-text="p.kelas"></td>
                            <td>
                                <span class="badge-status" :class="'status-' + p.status_code" x-text="p.status_text"></span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #2563eb;" x-text="p.terjawab"></span>
                                <span style="color: #64748b;"> / </span>
                                <span x-text="p.total_soal"></span>
                            </td>
                            <td>
                                <span :style="{ color: p.sisa_menit < 10 ? '#ef4444' : '#0f172a', fontWeight: 600 }" x-text="p.sisa_menit + ' Menit'"></span>
                            </td>
                            <td>
                                <template x-if="p.pelanggaran > 0">
                                    <span class="badge-violation" x-text="p.pelanggaran + 'x Ganti Tab'"></span>
                                </template>
                                <template x-if="p.pelanggaran === 0">
                                    <span style="color: #64748b;">-</span>
                                </template>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-action btn-reset" title="Reset Kunci Login Siswa" @click="resetLogin(p.id_siswa)">
                                        Reset Login
                                    </button>
                                    <button class="btn-action btn-extra" title="Tambah 10 Menit" @click="addExtraTime(p.id_siswa)">
                                        +10m
                                    </button>
                                    <template x-if="p.status_code === 1">
                                        <button class="btn-action btn-force" title="Paksa Kumpulkan Jawaban" @click="forceSubmit(p.id_siswa)">
                                            Paksa Selesai
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function proctorMonitor(jadwalId) {
            return {
                jadwalId: jadwalId,
                peserta: @json($initialData['peserta'] ?? []),
                summary: @json($initialData['summary'] ?? []),
                searchQuery: '',
                currentFilter: 'all',
                pollInterval: null,
                lastUpdatedText: 'Baru saja',

                init() {
                    this.startPolling();
                },

                get filteredPeserta() {
                    return this.peserta.filter(p => {
                        // Search query
                        const q = this.searchQuery.toLowerCase();
                        const matchSearch = p.nama.toLowerCase().includes(q) || (p.nisn && p.nisn.includes(q));

                        // Status filter
                        let matchFilter = true;
                        if (this.currentFilter === 'sedang') matchFilter = (p.status_code === 1);
                        else if (this.currentFilter === 'selesai') matchFilter = (p.status_code === 2);
                        else if (this.currentFilter === 'belum') matchFilter = (p.status_code === 0);

                        return matchSearch && matchFilter;
                    });
                },

                startPolling() {
                    this.pollInterval = setInterval(async () => {
                        try {
                            const res = await fetch(`/proctor/api/live/${this.jadwalId}`);
                            const data = await res.json();
                            if (data.success) {
                                this.peserta = data.data.peserta;
                                this.summary = data.data.summary;
                                const now = new Date();
                                this.lastUpdatedText = now.toLocaleTimeString();
                            }
                        } catch (e) {}
                    }, 6000); // Polling setiap 6 detik
                },

                async resetLogin(siswaId) {
                    const res = await Swal.fire({
                        title: 'Reset Login Siswa?',
                        text: 'Yakin ingin mereset login siswa ini agar dapat masuk kembali dari perangkat lain?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0284c7',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Ya, Reset Login',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    });
                    if (!res.isConfirmed) return;

                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch(`/proctor/api/reset-login/${this.jadwalId}/${siswaId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } catch (e) {
                        Swal.fire('Gagal!', 'Gagal mereset login: ' + e.message, 'error');
                    }
                },

                async addExtraTime(siswaId) {
                    const { value: extra } = await Swal.fire({
                        title: 'Tambah Waktu Ujian',
                        input: 'number',
                        inputValue: 10,
                        inputLabel: 'Durasi tambahan (menit):',
                        inputAttributes: { min: 1, max: 180, step: 1 },
                        showCancelButton: true,
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Tambahkan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        inputValidator: (val) => {
                            if (!val || parseInt(val) <= 0) return 'Masukkan jumlah menit yang valid (minimal 1)!';
                        }
                    });
                    if (!extra) return;

                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch(`/proctor/api/extra-time/${this.jadwalId}/${siswaId}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ minutes: parseInt(extra) })
                        });
                        const data = await response.json();
                        Swal.fire({
                            title: 'Waktu Ditambahkan!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } catch (e) {
                        Swal.fire('Gagal!', 'Gagal menambah waktu: ' + e.message, 'error');
                    }
                },

                async forceSubmit(siswaId) {
                    const res = await Swal.fire({
                        title: 'Paksa Selesai Ujian?',
                        text: 'PENTING: Yakin ingin memaksa siswa ini menyelesaikan ujian sekarang juga? Lembar ujian akan langsung dikirim dan ditutup.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Ya, Paksa Selesai',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    });
                    if (!res.isConfirmed) return;

                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch(`/proctor/api/force-submit/${this.jadwalId}/${siswaId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        Swal.fire({
                            title: 'Ujian Selesai!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } catch (e) {
                        Swal.fire('Gagal!', 'Gagal menyelesaikan ujian: ' + e.message, 'error');
                    }
                }
            }
        }
    </script>
</body>
</html>
