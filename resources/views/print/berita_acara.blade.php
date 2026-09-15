<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pelaksanaan Ujian | {{ $appSetting->nama_aplikasi_tampil }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background: #f1f5f9; padding: 20px; }
        .no-print {
            max-width: 800px;
            margin: 0 auto 20px;
            background: #fff;
            padding: 14px 20px;
            border-radius: 8px;
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
            padding: 20mm 20mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-size: 13px;
            line-height: 1.8;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 10px;
            margin-bottom: 24px;
        }
        .kop-surat h2 { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .kop-surat h3 { font-size: 13px; margin-top: 4px; }
        .fill-table { width: 100%; margin: 12px 0; }
        .fill-table td { padding: 4px 0; vertical-align: top; }
        .fill-table td:first-child { width: 220px; }
        .dotted-line { border-bottom: 1px dotted #000; display: inline-block; min-width: 150px; }
        .notes-box {
            border: 1px solid #000;
            min-height: 100px;
            padding: 10px;
            margin: 16px 0;
        }
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }
        .ttd-space { height: 70px; }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .page-a4 { width: 100%; margin: 0; padding: 0; box-shadow: none; }
            @page { size: A4 portrait; margin: 15mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div><strong>Berita Acara Pelaksanaan Ujian CBT</strong></div>
        <button class="btn-print" onclick="window.print()">🖨 Cetak Berita Acara</button>
    </div>

    <div class="page-a4">
        <div class="kop-surat" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 20px;">
            @if(!empty($appSetting->logo_kiri_url))
                <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" style="height: 55px; width: 55px; object-fit: contain;">
            @else
                <div style="width: 55px;"></div>
            @endif
            <div style="text-align: center; flex: 1; padding: 0 12px;">
                <h2 style="font-size: 15px; font-weight: bold; text-transform: uppercase;">{{ $appSetting->nama_sekolah_tampil }}</h2>
                <h3 style="font-size: 13px; font-weight: bold; margin-top: 2px;">BERITA ACARA PELAKSANAAN UJIAN ({{ $appSetting->nama_aplikasi_tampil }})</h3>
                <p style="font-size: 10px; color: #333; margin-top: 2px;">{{ $appSetting->alamat_lengkap }}</p>
                <p style="font-size: 10px; font-weight: 600; margin-top: 1px;">TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}</p>
            </div>
            @if(!empty($appSetting->logo_kanan_url))
                <img src="{{ $appSetting->logo_kanan_url }}" alt="Logo" style="height: 55px; width: 55px; object-fit: contain;">
            @else
                <div style="width: 55px;"></div>
            @endif
        </div>

        <p>Pada hari ini <strong>{{ date('l') }}</strong> tanggal <strong>{{ date('d F Y') }}</strong>, di ruang ujian telah dilaksanakan Ujian Berbasis Komputer (CBT) untuk:</p>

        <table class="fill-table">
            <tr>
                <td>Mata Pelajaran</td>
                <td>: <strong>{{ $bank->mapel->nama_mapel ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td>Nama Paket Ujian</td>
                <td>: {{ $bank->bank_nama }}</td>
            </tr>
            <tr>
                <td>Ruang / Sesi</td>
                <td>: Ruang 01 / Sesi 01</td>
            </tr>
            <tr>
                <td>Waktu Pelaksanaan</td>
                <td>: Pukul .................... s.d. .................... WIB</td>
            </tr>
        </table>

        <p>Jumlah Peserta:</p>
        <table class="fill-table">
            <tr>
                <td>a. Jumlah Peserta Terdaftar</td>
                <td>: {{ $totalSiswa }} Orang</td>
            </tr>
            <tr>
                <td>b. Jumlah Peserta Hadir</td>
                <td>: ........... Orang</td>
            </tr>
            <tr>
                <td>c. Jumlah Peserta Tidak Hadir</td>
                <td>: ........... Orang</td>
            </tr>
            <tr>
                <td>d. Nomor Peserta yang Tidak Hadir</td>
                <td>: .....................................................................................</td>
            </tr>
        </table>

        <p>Catatan Selama Pelaksanaan Ujian (Gangguan Teknis / Pelanggaran):</p>
        <div class="notes-box">
            <p style="color: #666; font-style: italic;">Ujian berlangsung dengan tertib, aman, dan lancar tanpa kendala jaringan/kelistrikan.</p>
        </div>

        <p>Demikian Berita Acara ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.</p>

        <div class="ttd-grid">
            <div>
                <p>Pengawas Ruang,</p>
                <div class="ttd-space"></div>
                <p><strong>( ................................................... )</strong></p>
                <p>NIP. ...............................................</p>
            </div>
            <div>
                <p>Proktor Ujian,</p>
                <div class="ttd-space"></div>
                <p><strong>( ................................................... )</strong></p>
                <p>NIP. ...............................................</p>
            </div>
        </div>
    </div>
</body>
</html>
