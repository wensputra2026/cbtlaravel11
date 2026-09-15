<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Monitoring: {{ $jadwal->bankSoal->bank_nama ?? 'Ujian' }} | {{ $appSetting->nama_aplikasi_tampil }}</title>
    <script defer src="{{ asset('assets/vendor/alpine.min.js') }}"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        body { background-color: #0b1120; color: #f8fafc; min-height: 100vh; }
        .navbar {
            background: #1e293b;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
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
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 18px 20px;
        }
        .stat-label { font-size: 13px; color: #94a3b8; margin-bottom: 6px; font-weight: 600; }
        .stat-value { font-size: 28px; font-weight: 800; color: #fff; }

        /* Filter & Search Bar */
        .toolbar {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .search-input {
            background: #0f172a;
            border: 1.5px solid #334155;
            border-radius: 8px;
            padding: 8px 14px;
            color: #fff;
            font-size: 13px;
            outline: none;
            width: 280px;
        }
        .search-input:focus { border-color: #38bdf8; }
        .filter-group { display: flex; gap: 8px; }
        .filter-btn {
            background: #0f172a;
            border: 1px solid #334155;
            color: #94a3b8;
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
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 20px;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; padding: 12px 14px; background: #0f172a; color: #94a3b8; font-weight: 600; }
        td { padding: 12px 14px; border-bottom: 1px solid #334155; color: #cbd5e1; vertical-align: middle; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        .badge-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .status-0 { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
        .status-1 { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-2 { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

        .badge-violation {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
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
                                <strong style="color: #f1f5f9;" x-text="p.nisn || '-'"></strong><br>
                                <span style="font-size: 11px; color: #64748b;" x-text="p.nis || ''"></span>
                            </td>
                            <td>
                                <strong style="color: #fff;" x-text="p.nama"></strong>
                                <template x-if="p.device_locked">
                                    <span style="display: block; font-size: 10px; color: #38bdf8;">🔒 Device Terkunci</span>
                                </template>
                            </td>
                            <td x-text="p.kelas"></td>
                            <td>
                                <span class="badge-status" :class="'status-' + p.status_code" x-text="p.status_text"></span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #60a5fa;" x-text="p.terjawab"></span>
                                <span style="color: #64748b;"> / </span>
                                <span x-text="p.total_soal"></span>
                            </td>
                            <td>
                                <span :style="{ color: p.sisa_menit < 10 ? '#ef4444' : '#f1f5f9', fontWeight: 600 }" x-text="p.sisa_menit + ' Menit'"></span>
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
                    if (!confirm('Yakin ingin mereset login siswa ini agar dapat masuk di perangkat lain?')) return;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const res = await fetch(`/proctor/api/reset-login/${this.jadwalId}/${siswaId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        alert(data.message);
                    } catch (e) {
                        alert('Gagal mereset login: ' + e.message);
                    }
                },

                async addExtraTime(siswaId) {
                    const extra = prompt('Masukkan jumlah menit tambahan untuk siswa ini:', '10');
                    if (!extra || isNaN(extra)) return;

                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const res = await fetch(`/proctor/api/extra-time/${this.jadwalId}/${siswaId}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ minutes: parseInt(extra) })
                        });
                        const data = await res.json();
                        alert(data.message);
                    } catch (e) {
                        alert('Gagal menambah waktu: ' + e.message);
                    }
                },

                async forceSubmit(siswaId) {
                    if (!confirm('PENTING: Yakin ingin memaksa siswa ini menyelesaikan ujian sekarang juga?')) return;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const res = await fetch(`/proctor/api/force-submit/${this.jadwalId}/${siswaId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        alert(data.message);
                    } catch (e) {
                        alert('Gagal menyelesaikan ujian: ' + e.message);
                    }
                }
            }
        }
    </script>
</body>
</html>
