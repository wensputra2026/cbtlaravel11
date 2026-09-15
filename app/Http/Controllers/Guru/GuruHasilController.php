<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\CbtSoal;
use App\Models\MasterGuru;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruHasilController extends Controller
{
    protected function getGuru(): ?MasterGuru
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Rekapitulasi perolehan nilai siswa per jadwal ujian yang diampu guru.
     */
    public function index(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;
        $jadwalId = $request->input('jadwal_id');

        // Jadwal ujian milik guru ini
        $jadwalList = CbtJadwal::with('bankSoal.mapel')
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('bank_guru_id', $guruId);
                }
            })
            ->orderBy('id_jadwal', 'desc')
            ->get();

        $selectedJadwal = null;
        $pesertaList = [];
        $stats = [
            'total' => 0,
            'tertinggi' => 0,
            'terendah' => 0,
            'rata_rata' => 0,
            'tuntas' => 0,
            'belum_tuntas' => 0,
        ];

        if ($jadwalId) {
            $selectedJadwal = CbtJadwal::with('bankSoal.mapel')->find($jadwalId);
            if ($selectedJadwal) {
                // Statistik global dihitung dari semua peserta jadwal ini
                $allPeserta = CbtSiswa::where('id_jadwal', $jadwalId)->get();
                $stats['total'] = count($allPeserta);
                $nilaiArr = [];
                $kkm = 75;

                foreach ($allPeserta as $p) {
                    $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                    $pg = (float)($input['pg_nilai'] ?? 0);
                    $esai = (float)($input['essai_nilai'] ?? 0);
                    $total = $pg + $esai;
                    $nilaiArr[] = $total;

                    if ($total >= $kkm) {
                        $stats['tuntas']++;
                    } else {
                        $stats['belum_tuntas']++;
                    }
                }

                if (!empty($nilaiArr)) {
                    $stats['tertinggi'] = max($nilaiArr);
                    $stats['terendah'] = min($nilaiArr);
                    $stats['rata_rata'] = round(array_sum($nilaiArr) / count($nilaiArr), 1);
                }

                // Paginate daftar peserta untuk tampilan tabel
                $pesertaQuery = CbtSiswa::with(['siswa.kelasSiswa.kelas', 'siswa.nomorPeserta'])
                    ->where('id_jadwal', $jadwalId);

                if ($request->filled('q')) {
                    $search = $request->input('q');
                    $pesertaQuery->whereHas('siswa', function ($sq) use ($search) {
                        $sq->where('nama', 'like', "%{$search}%")
                           ->orWhere('nisn', 'like', "%{$search}%");
                    });
                }

                $pesertaList = $pesertaQuery->orderBy('id_cbt_siswa', 'asc')
                    ->paginate(10)
                    ->withQueryString();

                foreach ($pesertaList as $p) {
                    $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                    $p->nilai_pg = (float)($input['pg_nilai'] ?? 0);
                    $p->nilai_esai = (float)($input['essai_nilai'] ?? 0);
                    $p->skor_total = $p->nilai_pg + $p->nilai_esai;
                }
            }
        }

        return view('guru.hasil.index', compact('guru', 'jadwalList', 'selectedJadwal', 'pesertaList', 'stats', 'jadwalId'));
    }

    /**
     * Ekspor Nilai ke format Microsoft Excel (.xls) untuk jadwal yang diampu.
     */
    public function export(int $jadwalId): Response
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;

        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);

        if ($jadwal->bankSoal?->bank_guru_id != $guruId && !Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak mengekspor nilai ujian ini.');
        }

        $peserta = CbtSiswa::with('siswa.kelasSiswa.kelas', 'siswa.nomorPeserta')
            ->where('id_jadwal', $jadwalId)
            ->get();

        $namaBank = $jadwal->bankSoal->bank_nama ?? 'Ujian';
        $filename = 'Rekap_Nilai_Guru_' . preg_replace('/[^A-Za-z0-9_]/', '_', $namaBank) . '_' . date('Ymd_His') . '.xls';

        $headers = ['No', 'Nomor Peserta', 'NISN', 'Nama Siswa', 'Kelas', 'Status Ujian', 'Waktu Selesai', 'Nilai PG', 'Nilai Esai', 'Nilai Akhir'];
        $rows = [];

        foreach ($peserta as $idx => $p) {
            $siswa = $p->siswa;
            $nama = $siswa->nama ?? '-';
            $nisn = $siswa->nisn ?? '-';
            $nomorPeserta = $siswa->nomorPeserta?->nomor_peserta ?? '-';
            $kelas = $siswa->kelasSiswa->first()?->kelas->nama_kelas ?? '-';
            $status = $p->status == 2 ? 'Selesai' : ($p->status == 1 ? 'Sedang Mengerjakan' : 'Belum Mulai');

            $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
            $pg = (float)($input['pg_nilai'] ?? 0);
            $esai = (float)($input['essai_nilai'] ?? 0);
            $akhir = round($pg + $esai, 2);

            $rows[] = [
                $idx + 1,
                $nomorPeserta,
                $nisn,
                $nama,
                $kelas,
                $status,
                $p->selesai ?? '-',
                $pg,
                $esai,
                $akhir,
            ];
        }

        return \App\Services\Export\ExcelExportService::download(
            'Nilai ' . mb_substr($namaBank, 0, 20),
            $headers,
            $rows,
            $filename
        );
    }

    /**
     * Analisis butir soal milik guru pengampu.
     */
    public function analisis(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;
        $jadwalId = $request->input('jadwal_id');

        $jadwalList = CbtJadwal::with('bankSoal.mapel')
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('bank_guru_id', $guruId);
                }
            })
            ->orderBy('id_jadwal', 'desc')
            ->get();

        $selectedJadwal = null;
        $analisisSoal = [];
        $rekapStat = [
            'total_peserta' => 0,
            'nilai_tertinggi' => 0,
            'nilai_terendah' => 0,
            'nilai_rata' => 0,
            'tuntas' => 0,
            'belum_tuntas' => 0,
        ];

        if ($jadwalId) {
            $selectedJadwal = CbtJadwal::with(['bankSoal.mapel', 'bankSoal.soals'])->find($jadwalId);

            if ($selectedJadwal) {
                $peserta = CbtSiswa::where('id_jadwal', $jadwalId)->get();
                $rekapStat['total_peserta'] = count($peserta);

                $nilaiList = [];
                $kkm = 75;

                foreach ($peserta as $p) {
                    $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                    $pg = (float)($input['pg_nilai'] ?? 0);
                    $esai = (float)($input['essai_nilai'] ?? 0);
                    $total = $pg + $esai;
                    $nilaiList[] = $total;

                    if ($total >= $kkm) {
                        $rekapStat['tuntas']++;
                    } else {
                        $rekapStat['belum_tuntas']++;
                    }
                }

                if (!empty($nilaiList)) {
                    $rekapStat['nilai_tertinggi'] = max($nilaiList);
                    $rekapStat['nilai_terendah']  = min($nilaiList);
                    $rekapStat['nilai_rata']      = round(array_sum($nilaiList) / count($nilaiList), 1);
                }

                $soals = CbtSoal::where('bank_id', $selectedJadwal->id_bank)->orderBy('nomor_soal', 'asc')->get();
                foreach ($soals as $soal) {
                    $benar = 0;
                    $dijawab = 0;

                    foreach ($peserta as $p) {
                        $jawaban = is_string($p->jawaban) ? json_decode($p->jawaban, true) : ($p->jawaban ?? []);
                        if (isset($jawaban[$soal->nomor_soal])) {
                            $dijawab++;
                            $ans = strtoupper(trim((string)$jawaban[$soal->nomor_soal]));
                            $key = strtoupper(trim((string)$soal->jawaban));
                            if ($ans === $key) {
                                $benar++;
                            }
                        }
                    }

                    $persenBenar = $dijawab > 0 ? round(($benar / $dijawab) * 100, 1) : 0;
                    $kategori = 'Sedang';
                    if ($persenBenar >= 75) {
                        $kategori = 'Mudah';
                    } elseif ($persenBenar < 35) {
                        $kategori = 'Sukar';
                    }

                    $analisisSoal[] = [
                        'nomor'        => $soal->nomor_soal,
                        'jenis'        => $soal->jenis ?? $soal->jenis_soal ?? 1,
                        'kunci'        => $soal->jawaban,
                        'peserta'      => $dijawab,
                        'benar'        => $benar,
                        'persen_benar' => $persenBenar,
                        'tingkat'      => $kategori,
                    ];
                }
            }
        }

        return view('guru.analisis.index', compact('guru', 'jadwalList', 'selectedJadwal', 'analisisSoal', 'rekapStat', 'jadwalId'));
    }
}
