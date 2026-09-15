<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Login Siswa CBT | {{ $appSetting->nama_aplikasi_tampil }}</title>
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
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 12px;
        }

        /* Lembar A4 Cetak */
        .page-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 15mm;
            background: white;
            padding: 8mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 6mm;
            align-content: start;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-box {
            border: 1.5px solid #000;
            border-radius: 6px;
            padding: 8px 12px;
            height: 64mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 11px;
            page-break-inside: avoid;
            background: #fff;
        }

        .card-kop {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }
        .card-kop img { width: 26px; height: 26px; object-fit: contain; }
        .card-kop h3 { font-size: 10.5px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .card-kop p { font-size: 8.5px; margin: 1px 0 0; }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-table {
            width: 100%;
            font-size: 10px;
        }
        .card-table td { padding: 2px 0; vertical-align: top; }
        .card-table td:first-child { width: 75px; color: #333; font-weight: 500; }
        .card-table td:nth-child(2) { width: 8px; }

        .cred-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 1px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            color: #0f172a;
        }

        .card-note {
            font-size: 8px;
            color: #64748b;
            font-style: italic;
            border-top: 1px dashed #cbd5e1;
            padding-top: 3px;
            margin-top: 2px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #334155;
            padding-top: 2px;
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
                margin: 6mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div>
                <strong>Cetak Kartu Login Akun Peserta CBT</strong> &bull; (8 Kartu / Lembar A4)
                <div style="font-size: 11px; color: #64748b;">Total: {{ $siswas->count() }} kartu siswa</div>
            </div>
            <form action="{{ route('print.kartu_login') }}" method="GET" style="display: inline-flex; align-items: center; gap: 6px;">
                <select name="kelas_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $kelasId == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <button class="btn-print" onclick="window.print()">🖨 Cetak Dokumen</button>
    </div>

    @php
        $chunks = $siswas->chunk(8);
    @endphp

    @forelse($chunks as $chunk)
        <div class="page-a4">
            @foreach($chunk as $s)
                <div class="card-box">
                    <div class="card-kop">
                        @if($appSetting->logo_kanan)
                            <img src="{{ asset($appSetting->logo_kanan) }}" alt="Logo">
                        @endif
                        <div style="text-align: center;">
                            <h3>KARTU LOGIN UJIAN CBT</h3>
                            <p style="font-weight: bold;">{{ strtoupper($appSetting->nama_sekolah) }}</p>
                            <p>T.A. {{ $appSetting->tahun_ajaran }} &bull; Semester {{ $appSetting->semester }}</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="card-table">
                            <tr>
                                <td>Nama Siswa</td>
                                <td>:</td>
                                <td><strong>{{ $s->nama }}</strong></td>
                            </tr>
                            <tr>
                                <td>NISN / NIS</td>
                                <td>:</td>
                                <td>{{ $s->nisn ?? '-' }} / {{ $s->nis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Kelas / Rombel</td>
                                <td>:</td>
                                <td>{{ $s->kelasSiswa->first()?->kelas?->nama_kelas ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Username Login</td>
                                <td>:</td>
                                <td><span class="cred-badge">{{ $s->username }}</span></td>
                            </tr>
                            <tr>
                                <td>Kata Sandi</td>
                                <td>:</td>
                                <td><span class="cred-badge">{{ $s->password ?: '123456' }}</span></td>
                            </tr>
                        </table>
                        <div class="card-note">
                            * Simpan kartu ini dengan baik. Jangan beritahukan password kepada peserta lain.
                        </div>
                    </div>

                    <div class="card-footer">
                        <span>{{ $appSetting->nama_aplikasi_tampil }}</span>
                        <span>Panitia Pelaksana CBT</span>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div style="text-align: center; padding: 50px; color: #64748b;">
            Tidak ada data peserta ujian untuk dicetak.
        </div>
    @endforelse

</body>
</html>
