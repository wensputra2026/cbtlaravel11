<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Denah Ruang Ujian | {{ $appSetting->nama_aplikasi_tampil }}</title>
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
            padding: 15mm 15mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .kop-surat {
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 24px;
        }
        .kop-surat h2 { font-size: 16px; font-weight: bold; }
        .papan-tulis {
            border: 2px solid #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 30px;
            background: #f8fafc;
        }
        .meja-pengawas {
            border: 2px dashed #000;
            padding: 12px;
            text-align: center;
            width: 180px;
            margin: 0 auto 40px;
            font-weight: bold;
            font-size: 13px;
        }
        .seating-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px 24px;
            margin-bottom: 40px;
        }
        .seat-box {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 16px 10px;
            text-align: center;
        }
        .seat-number { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .seat-label { font-size: 11px; color: #555; }
        .pintu-masuk {
            border-top: 3px solid #000;
            width: 100px;
            text-align: center;
            padding-top: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 20px;
        }

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
        <div><strong>Denah Tempat Duduk: {{ $ruang }}</strong> (Kapasitas: {{ $kapasitas }} Siswa)</div>
        <button class="btn-print" onclick="window.print()">🖨 Cetak Denah</button>
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
                <h3 style="font-size: 13px; font-weight: bold; margin-top: 2px;">DENAH TEMPAT DUDUK PESERTA UJIAN ({{ $appSetting->nama_aplikasi_tampil }})</h3>
                <p style="font-size: 10px; color: #333; margin-top: 2px;">{{ strtoupper($ruang) }} &bull; TAHUN AJARAN {{ date('Y') }}/{{ date('Y')+1 }}</p>
            </div>
            @if(!empty($appSetting->logo_kanan_url))
                <img src="{{ $appSetting->logo_kanan_url }}" alt="Logo" style="height: 55px; width: 55px; object-fit: contain;">
            @else
                <div style="width: 55px;"></div>
            @endif
        </div>

        <div class="papan-tulis">
            PAPAN TULIS / LAYAR PROYEKTOR
        </div>

        <div class="meja-pengawas">
            MEJA PENGAWAS & PROKTOR
        </div>

        <div class="seating-grid">
            @for($i = 1; $i <= $kapasitas; $i++)
                <div class="seat-box">
                    <div class="seat-number">MEJA {{ sprintf('%02d', $i) }}</div>
                    <div class="seat-label">Peserta Ujian</div>
                </div>
            @endfor
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div class="pintu-masuk">
                PINTU MASUK
            </div>
            <div style="text-align: center; font-size: 12px;">
                <p>{{ date('d F Y') }}</p>
                <p style="margin-top: 4px;">Penanggung Jawab Ruang,</p>
                <div style="height: 50px;"></div>
                <p><strong>( ............................................ )</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
