<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\CbtSoal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CbtAnalisisController extends Controller
{
    /**
     * Halaman Analisis Butir Soal & Rekap Hasil Ujian.
     */
    public function index(Request $request): View
    {
        $jadwalId = $request->input('jadwal_id');
        $jadwalList = CbtJadwal::with('bankSoal.mapel')->orderBy('id_jadwal', 'desc')->get();

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
                $kkm = 75; // Standar KKM

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

                // Analisis butir soal
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
                        'jenis'        => $soal->jenis_soal,
                        'kunci'        => $soal->jawaban,
                        'peserta'      => $dijawab,
                        'benar'        => $benar,
                        'persen_benar' => $persenBenar,
                        'tingkat'      => $kategori,
                    ];
                }
            }
        }

        return view('admin.cbt.analisis', compact('jadwalList', 'selectedJadwal', 'analisisSoal', 'rekapStat', 'jadwalId'));
    }
}
