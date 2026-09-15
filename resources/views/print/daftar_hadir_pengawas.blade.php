<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir Pengawas Ujian | {{ $appSetting->nama_aplikasi_tampil }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background: #f1f5f9; padding: 20px; }
        .no-print {
            max-width: 900px;
            margin: 0 auto 20px;
            background: #fff;
            padding: 14px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .page-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            padding: 15mm 15mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
            gap: 15px;
        }
        .kop-logo {
            width: 55px;
            height: 55px;
            object-fit: contain;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text h2 { font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .kop-text h3 { font-size: 13px; font-weight: bold; margin-top: 4px; }
        .kop-text p { font-size: 11px; color: #333; margin-top: 2px; }
        .info-table { width: 100%; font-size: 11px; margin-bottom: 16px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-table td:first-child { width: 140px; font-weight: bold; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 24px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .data-table th { background: #f1f5f9; text-align: center; }
        .ttd-box {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-top: 30px;
        }
        .ttd-item {
            text-align: center;
            width: 200px;
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .page-a4 { box-shadow: none; padding: 10mm; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div>
            <strong>Daftar Hadir Pengawas Ujian Ruang</strong>
            <div style="font-size: 12px; color: #64748b;">{{ $appSetting->nama_sekolah }}</div>
        </div>
        <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>
    </div>

    <div class="page-a4">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            @if($appSetting->logo_kanan)
                <img src="{{ asset($appSetting->logo_kanan) }}" alt="Logo" class="kop-logo">
            @endif
            <div class="kop-text">
                <h2>DAFTAR HADIR PENGAWAS RUANG UJIAN BERBASIS KOMPUTER</h2>
                <h3>{{ strtoupper($appSetting->nama_sekolah) }}</h3>
                <p>Tahun Pelajaran {{ $appSetting->tahun_ajaran }} - Semester {{ $appSetting->semester }}</p>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>Nama Pengawas & NIP</th>
                    <th>Mata Pelajaran</th>
                    <th style="width: 80px;">Ruang</th>
                    <th style="width: 70px;">Sesi</th>
                    <th style="width: 120px;">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengawas as $idx => $p)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $p->guru?->nama_guru ?? 'Pengawas Ruang' }}</strong>
                            <div style="font-size: 9px; color: #555;">NIP: {{ $p->guru?->nip ?? '-' }}</div>
                        </td>
                        <td>{{ $p->jadwal?->bankSoal?->mapel?->nama_mapel ?? 'Ujian CBT' }}</td>
                        <td style="text-align: center;">{{ $p->ruang?->nama_ruang ?? 'Ruang 01' }}</td>
                        <td style="text-align: center;">{{ $p->sesi?->nama_sesi ?? 'Sesi 1' }}</td>
                        <td style="height: 35px; vertical-align: bottom;">
                            <span style="font-size: 9px; color: #888;">{{ $idx + 1 }}. ....................</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #888;">
                            Belum ada jadwal penugasan pengawas ruang yang dialokasikan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN -->
        <div class="ttd-box">
            <div class="ttd-item">
                <p>Mengetahui,</p>
                <p>Kepala Sekolah</p>
                <div style="height: 60px;"></div>
                <p style="font-weight: bold; text-decoration: underline;">{{ $appSetting->kepala_sekolah ?? '..............................' }}</p>
                <p>NIP. {{ $appSetting->nip_kepala_sekolah ?? '..............................' }}</p>
            </div>
            <div class="ttd-item">
                <p>{{ $appSetting->kota ?? 'Bantaeng' }}, {{ date('d F Y') }}</p>
                <p>Ketua Pelaksana CBT</p>
                <div style="height: 60px;"></div>
                <p style="font-weight: bold; text-decoration: underline;">........................................</p>
                <p>NIP. ........................................</p>
            </div>
        </div>
    </div>

</body>
</html>
