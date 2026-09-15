<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir Peserta Ujian | {{ $appSetting->nama_aplikasi_tampil }}</title>
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
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .kop-surat h2 { font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .kop-surat h3 { font-size: 13px; font-weight: bold; margin-top: 4px; }
        .info-table { width: 100%; font-size: 12px; margin-bottom: 16px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-table td:first-child { width: 130px; font-weight: bold; }
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
            font-size: 12px;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .ttd-item { text-align: center; width: 200px; }
        .ttd-space { height: 60px; }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .page-a4 { width: 100%; margin: 0; padding: 0; box-shadow: none; }
            @page { size: A4 portrait; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div><strong>Daftar Hadir Peserta Ujian</strong> &bull; {{ $bank->bank_nama }}</div>
        <button class="btn-print" onclick="window.print()">🖨 Cetak Daftar Hadir</button>
    </div>

    <div class="page-a4">
        <div class="kop-surat" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 16px;">
            @if(!empty($appSetting->logo_kiri_url))
                <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" style="height: 55px; width: 55px; object-fit: contain;">
            @else
                <div style="width: 55px;"></div>
            @endif
            <div style="text-align: center; flex: 1; padding: 0 12px;">
                <h2 style="font-size: 15px; font-weight: bold; text-transform: uppercase;">{{ $appSetting->nama_sekolah_tampil }}</h2>
                <h3 style="font-size: 13px; font-weight: bold; margin-top: 2px;">DAFTAR HADIR PESERTA UJIAN ({{ $appSetting->nama_aplikasi_tampil }})</h3>
                <p style="font-size: 10px; color: #333; margin-top: 2px;">{{ $appSetting->alamat_lengkap }}</p>
                <p style="font-size: 10px; font-weight: 600; margin-top: 1px;">TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}</p>
            </div>
            @if(!empty($appSetting->logo_kanan_url))
                <img src="{{ $appSetting->logo_kanan_url }}" alt="Logo" style="height: 55px; width: 55px; object-fit: contain;">
            @else
                <div style="width: 55px;"></div>
            @endif
        </div>

        <table class="info-table">
            <tr>
                <td>Mata Pelajaran</td>
                <td>: {{ $bank->mapel->nama_mapel ?? '-' }}</td>
                <td style="width: 100px; font-weight: bold;">Ruang / Sesi</td>
                <td>: Ruang 01 / Sesi 01</td>
            </tr>
            <tr>
                <td>Nama Ujian</td>
                <td>: {{ $bank->bank_nama }}</td>
                <td style="font-weight: bold;">Hari / Tanggal</td>
                <td>: {{ date('d F Y') }}</td>
            </tr>
            <tr>
                <td>Alokasi Waktu</td>
                <td>: {{ $jadwal->durasi_ujian }} Menit</td>
                <td style="font-weight: bold;">Pukul</td>
                <td>: {{ $jadwal->tgl_mulai ? substr($jadwal->tgl_mulai, 11, 5) : '08:00' }} s.d Selesai</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th style="width: 90px;">NISN</th>
                    <th>Nama Peserta</th>
                    <th style="width: 80px;">Kelas</th>
                    <th style="width: 140px;" colspan="2">Tanda Tangan</th>
                    <th style="width: 60px;">Ket</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswas as $idx => $siswa)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="text-align: center;">{{ $siswa->nisn }}</td>
                        <td><strong>{{ $siswa->nama }}</strong></td>
                        <td style="text-align: center;">{{ $siswa->kelasSiswa->first()->kelas->nama_kelas ?? '-' }}</td>
                        @if(($idx + 1) % 2 != 0)
                            <td style="height: 26px; vertical-align: top; width: 70px;">{{ $idx + 1 }}. ............</td>
                            <td style="width: 70px;"></td>
                        @else
                            <td style="width: 70px;"></td>
                            <td style="height: 26px; vertical-align: top; width: 70px;">{{ $idx + 1 }}. ............</td>
                        @endif
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="ttd-box">
            <div class="ttd-item">
                <p>Pengawas 1,</p>
                <div class="ttd-space"></div>
                <p><strong>( ............................................ )</strong></p>
                <p>NIP. .....................................</p>
            </div>
            <div class="ttd-item">
                <p>Pengawas 2,</p>
                <div class="ttd-space"></div>
                <p><strong>( ............................................ )</strong></p>
                <p>NIP. .....................................</p>
            </div>
        </div>
    </div>
</body>
</html>
