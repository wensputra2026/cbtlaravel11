<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Peserta Ujian | {{ $appSetting->nama_aplikasi_tampil }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background: #f1f5f9; padding: 20px; }
        
        .no-print {
            max-width: 900px;
            margin: 0 auto 20px;
            background: #fff;
            padding: 16px 20px;
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

        /* Lembar A4 Cetak */
        .page-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20mm;
            background: white;
            padding: 10mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 8mm;
            align-content: start;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-box {
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 8px 10px;
            height: 62mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 11px;
            page-break-inside: avoid;
        }

        .card-kop {
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .card-kop h3 { font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .card-kop p { font-size: 9px; }

        .card-body {
            display: flex;
            gap: 8px;
            flex: 1;
        }
        .card-photo {
            width: 20mm;
            height: 26mm;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #888;
            flex-shrink: 0;
            background: #fafafa;
        }

        .card-table {
            width: 100%;
            font-size: 10px;
        }
        .card-table td { padding: 2px 0; vertical-align: top; }
        .card-table td:first-child { width: 65px; color: #333; }
        .card-table td:nth-child(2) { width: 8px; }

        .card-footer {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            border-top: 1px dashed #aaa;
            padding-top: 4px;
            margin-top: 4px;
        }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .page-a4 {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                page-break-after: always;
            }
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong>Cetak Kartu Peserta Ujian</strong> &bull; Format A4 (8 Kartu / Lembar)
        </div>
        <button class="btn-print" onclick="window.print()">🖨 Cetak Dokumen</button>
    </div>

    @php
        $chunks = $siswas->chunk(8);
    @endphp

    @forelse($chunks as $chunk)
        <div class="page-a4">
            @foreach($chunk as $siswa)
                <div class="card-box">
                    <div class="card-kop" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        @if(!empty($appSetting->logo_kiri_url))
                            <img src="{{ $appSetting->logo_kiri_url }}" alt="Logo" style="height: 28px; width: 28px; object-fit: contain;">
                        @endif
                        <div style="text-align: center;">
                            <h3 style="margin: 0; font-size: 11px;">KARTU PESERTA UJIAN {{ $appSetting->nama_aplikasi_tampil }}</h3>
                            <p style="margin: 1px 0; font-weight: bold; font-size: 10px;">{{ $appSetting->nama_sekolah_tampil }}</p>
                            <p style="margin: 0; font-size: 8px; color: #555;">TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="card-photo" style="overflow: hidden;">
                            @if(!empty($siswa->foto) && file_exists(public_path('uploads/foto_siswa/' . $siswa->foto)))
                                <img src="{{ asset('uploads/foto_siswa/' . $siswa->foto) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span>Foto 2x3</span>
                            @endif
                        </div>

                        <table class="card-table">
                            <tr>
                                <td>Nama</td>
                                <td>:</td>
                                <td><strong>{{ $siswa->nama }}</strong></td>
                            </tr>
                            <tr>
                                <td>NISN / NIS</td>
                                <td>:</td>
                                <td>{{ $siswa->nisn }} / {{ $siswa->nis }}</td>
                            </tr>
                            <tr>
                                <td>Kelas</td>
                                <td>:</td>
                                <td>{{ $siswa->kelasSiswa->first()->kelas->nama_kelas ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Username</td>
                                <td>:</td>
                                <td><code style="background: #f1f5f9; padding: 1px 4px; font-weight: bold;">{{ $siswa->username }}</code></td>
                            </tr>
                            <tr>
                                <td>Password</td>
                                <td>:</td>
                                <td><code style="background: #f1f5f9; padding: 1px 4px;">{{ $siswa->password ?? '******' }}</code></td>
                            </tr>
                        </table>
                    </div>

                    <div class="card-footer">
                        <span>Ruang: 01 &bull; Sesi: 01</span>
                        <span>{{ $appSetting->kepsek_tampil }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div style="text-align: center; padding: 40px; color: #64748b;">
            Tidak ada data siswa untuk dicetak.
        </div>
    @endforelse
</body>
</html>
