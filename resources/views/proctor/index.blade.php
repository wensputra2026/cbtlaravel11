<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Proktor & Pengawas | {{ $appSetting->nama_aplikasi_tampil }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="{{ asset('assets/vendor/alpine.min.js') }}"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        body { background-color: #0b1120; color: #f8fafc; min-height: 100vh; }
        .navbar {
            background: #1e293b;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand h2 { font-size: 16px; font-weight: 700; color: #fff; }
        .brand p { font-size: 12px; color: #94a3b8; }
        .container { max-width: 1200px; margin: 32px auto; padding: 0 24px; }
        .grid-layout { display: grid; grid-template-columns: 320px 1fr; gap: 24px; }
        
        /* Token Widget */
        .token-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            height: fit-content;
        }
        .token-title { font-size: 14px; font-weight: 600; color: #94a3b8; margin-bottom: 12px; }
        .token-badge {
            background: #0f172a;
            border: 2px dashed #38bdf8;
            border-radius: 12px;
            padding: 16px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #38bdf8;
            font-family: monospace;
            margin-bottom: 16px;
        }
        .token-ttl { font-size: 12px; color: #94a3b8; margin-bottom: 20px; }
        .btn-gen-token {
            width: 100%;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-gen-token:hover { opacity: 0.9; }

        /* Quick Print Links */
        .print-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
        }
        .print-card h3 { font-size: 14px; font-weight: 700; margin-bottom: 12px; color: #e2e8f0; }
        .print-links { display: flex; flex-direction: column; gap: 8px; }
        .print-link {
            color: #93c5fd;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .print-link:hover { background: rgba(59, 130, 246, 0.2); }

        /* Jadwal List Table */
        .table-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 24px;
            overflow-x: auto;
        }
        .table-card h2 { font-size: 18px; font-weight: 700; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 12px 14px; background: #0f172a; color: #94a3b8; font-weight: 600; border-radius: 6px; }
        td { padding: 14px; border-bottom: 1px solid #334155; color: #cbd5e1; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        .btn-monitor {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }
        .btn-monitor:hover { background: #1d4ed8; }
    </style>
</head>
<body x-data="proctorIndex()">
    <nav class="navbar">
        <div class="brand" style="display: flex; align-items: center; gap: 12px;">
            @if(!empty($appSetting->logo_kiri_url))
                <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" style="height: 38px; width: 38px; object-fit: contain; border-radius: 8px;">
            @endif
            <div>
                <h2>{{ $appSetting->nama_aplikasi_tampil }} &bull; PANEL PENGAWAS & PROKTOR</h2>
                <p>{{ $appSetting->nama_sekolah_tampil }} &bull; Pengawasan Ujian Real-Time &bull; Manajemen Token Dinamis</p>
            </div>
        </div>
        <div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600;">Keluar</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <div class="grid-layout">
            <!-- Sidebar: Token & Cetak -->
            <div>
                <div class="token-card">
                    <div class="token-title">TOKEN UJIAN AKTIF (REDIS)</div>
                    <div class="token-badge" x-text="token">
                        {{ $currentToken }}
                    </div>
                    <div class="token-ttl">
                        Masa berlaku: <span x-text="formatTtl(ttl)"></span>
                    </div>
                    <button class="btn-gen-token" @click="generateNewToken()">
                        ⚡ Buat Token Baru (15 Menit)
                    </button>
                </div>

                <div class="print-card">
                    <h3>Cetak Dokumen Ujian</h3>
                    <div class="print-links">
                        <a href="{{ route('print.kartu_peserta') }}" target="_blank" class="print-link">
                            <span>🪪 Kartu Peserta Ujian</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('print.denah_ruang') }}" target="_blank" class="print-link">
                            <span>🏛 Denah Tempat Duduk</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Konten Utama: Daftar Jadwal Ujian -->
            <div class="table-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="margin-bottom: 4px;">Jadwal Ujian Aktif</h2>
                        <span style="font-size: 12px; color: #94a3b8;">Total: <strong style="color: #fff;">{{ $jadwals->total() }}</strong> Jadwal Ujian</span>
                    </div>
                    <form action="{{ route('proctor.index') }}" method="GET" style="display: flex; align-items: center; gap: 8px;">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari mapel, bank, atau ID..." style="padding: 8px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #fff; font-size: 13px; outline: none; width: 220px;">
                        @if(request('q'))
                            <a href="{{ route('proctor.index') }}" style="padding: 8px 12px; background: #334155; color: #cbd5e1; border-radius: 8px; font-size: 12px; text-decoration: none;">Reset</a>
                        @endif
                    </form>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mata Pelajaran & Bank</th>
                            <th>Durasi</th>
                            <th>Token</th>
                            <th>Aksi Pengawasan</th>
                            <th>Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwals as $jadwal)
                            <tr>
                                <td>#{{ $jadwal->id_jadwal }}</td>
                                <td>
                                    <strong style="color: #fff;">{{ $jadwal->bankSoal->bank_nama ?? 'Bank Soal' }}</strong><br>
                                    <span style="font-size: 12px; color: #60a5fa;">{{ $jadwal->bankSoal->mapel->nama_mapel ?? '-' }}</span>
                                </td>
                                <td>{{ $jadwal->durasi_ujian }} Menit</td>
                                <td>
                                    <span style="color: {{ $jadwal->token ? '#34d399' : '#94a3b8' }}; font-weight: 600;">
                                        {{ $jadwal->token ? 'Wajib' : 'Tidak' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('proctor.monitor', $jadwal->id_jadwal) }}" target="_blank" rel="noopener noreferrer" class="btn-monitor">
                                        👁 Live Monitor ↗
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('print.daftar_hadir', $jadwal->id_jadwal) }}" target="_blank" style="color: #93c5fd; font-size: 12px; margin-right: 8px;">Daftar Hadir</a>
                                    <a href="{{ route('print.berita_acara', $jadwal->id_jadwal) }}" target="_blank" style="color: #93c5fd; font-size: 12px;">Berita Acara</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada jadwal ujian di sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($jadwals->hasPages())
                    <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid #334155;">
                        {{ $jadwals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function proctorIndex() {
            return {
                token: '{{ $currentToken }}',
                ttl: {{ $tokenTtl }},
                timer: null,

                init() {
                    this.timer = setInterval(() => {
                        if (this.ttl > 0) {
                            this.ttl--;
                        } else {
                            this.refreshToken();
                        }
                    }, 1000);
                },

                formatTtl(sec) {
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;
                    return `${m}m ${s < 10 ? '0' : ''}${s}s`;
                },

                async generateNewToken() {
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]').content;
                        const res = await fetch('/proctor/api/token/generate', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ minutes: 15 })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.token = data.token;
                            this.ttl = data.ttl;
                        }
                    } catch (e) {
                        alert('Gagal membuat token: ' + e.message);
                    }
                },

                async refreshToken() {
                    try {
                        const res = await fetch('/proctor/api/token/get');
                        const data = await res.json();
                        if (data.success) {
                            this.token = data.token;
                            this.ttl = data.ttl;
                        }
                    } catch (e) {}
                }
            }
        }
    </script>
</body>
</html>
