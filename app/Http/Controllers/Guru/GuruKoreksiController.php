<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtNomorPeserta;
use App\Models\CbtSesiSiswa;
use App\Models\CbtSiswa;
use App\Models\CbtSoal;
use App\Models\MasterGuru;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruKoreksiController extends Controller
{
    protected function getGuru()
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Halaman daftar jadwal yang perlu dikoreksi.
     * Jika menerima query id_siswa & id_jadwal (seperti format GarudaCBT), langsung arahkan ke form koreksi.
     */
    public function index(Request $request)
    {
        if ($request->filled('id_siswa') && $request->filled('id_jadwal')) {
            $cbtSiswa = CbtSiswa::where('id_siswa', $request->input('id_siswa'))
                ->where('id_jadwal', $request->input('id_jadwal'))
                ->first();

            if ($cbtSiswa) {
                return redirect()->route('guru.koreksi.form', $cbtSiswa->id_cbt_siswa);
            }
        }

        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;

        // Ambil jadwal yang bank soalnya dibuat oleh guru ini
        $jadwalList = CbtJadwal::with('bankSoal.mapel')
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                $q->where('bank_guru_id', $guruId);
            })
            ->orderBy('id_jadwal', 'desc')
            ->get();

        return view('guru.koreksi.index', compact('jadwalList', 'guru'));
    }

    /**
     * Legacy URL redirect untuk URL /cbtnilai/koreksi?id_siswa=X&id_jadwal=Y
     */
    public function legacyKoreksiRedirect(Request $request): RedirectResponse
    {
        $idSiswa = $request->input('id_siswa');
        $idJadwal = $request->input('id_jadwal');

        $cbtSiswa = CbtSiswa::where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->first();

        if ($cbtSiswa) {
            return redirect()->route('guru.koreksi.form', $cbtSiswa->id_cbt_siswa);
        }

        return redirect()->route('guru.koreksi.index')->with('error', 'Data peserta ujian tidak ditemukan.');
    }

    public function showPeserta(int $jadwalId): View
    {
        $guru = $this->getGuru();
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);

        $pesertaList = CbtSiswa::with('siswa.kelasSiswa.kelas')
            ->where('id_jadwal', $jadwalId)
            ->orderBy('id_cbt_siswa', 'asc')
            ->get();

        return view('guru.koreksi.peserta', compact('jadwal', 'pesertaList', 'guru'));
    }

    /**
     * Halaman koreksi lembar jawaban siswa lengkap (Paritas GarudaCBT).
     */
    public function showFormKoreksi(int $cbtSiswaId): View
    {
        $guru = $this->getGuru();
        $cbtSiswa = CbtSiswa::with([
            'siswa.kelasSiswa.kelas',
            'jadwal.bankSoal.mapel',
            'jadwal.bankSoal.guru',
            'jadwal.jenis',
            'jadwal.tp'
        ])->findOrFail($cbtSiswaId);

        $bank = $cbtSiswa->jadwal?->bankSoal;
        $noPeserta = CbtNomorPeserta::where('id_siswa', $cbtSiswa->id_siswa)->value('nomor_peserta') ?? '-';
        $sesiSiswa = CbtSesiSiswa::with(['sesi', 'ruang'])->where('siswa_id', $cbtSiswa->id_siswa)->first();

        $rawJawaban = is_string($cbtSiswa->jawaban) ? json_decode($cbtSiswa->jawaban, true) : ($cbtSiswa->jawaban ?? []);
        $rawNilai = is_string($cbtSiswa->nilai) ? json_decode($cbtSiswa->nilai, true) : ($cbtSiswa->nilai ?? []);
        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);

        // Petakan jawaban dan nilai berdasarkan id_soal
        $mapJawaban = [];
        if (is_array($rawJawaban)) {
            foreach ($rawJawaban as $j) {
                if (isset($j['id_soal'])) {
                    $mapJawaban[(string) $j['id_soal']] = $j;
                }
            }
        }

        $mapNilai = [];
        if (is_array($rawNilai)) {
            foreach ($rawNilai as $n) {
                if (isset($n['id_soal'])) {
                    $mapNilai[(string) $n['id_soal']] = $n;
                }
            }
        }

        // Ambil semua soal dari bank
        $soals = $bank ? CbtSoal::where('bank_id', $bank->id_bank)->orderBy('nomor_soal')->get() : collect();

        $bobotPg = (float) ($bank->bobot_pg ?? 0);
        $tampilPg = (int) ($bank->tampil_pg ?? 0);
        $maxPointPg = $tampilPg > 0 ? $bobotPg / $tampilPg : 0;

        $bobotKomp = (float) ($bank->bobot_kompleks ?? 0);
        $tampilKomp = (int) ($bank->tampil_kompleks ?? 0);
        $maxPointKomp = $tampilKomp > 0 ? $bobotKomp / $tampilKomp : 0;

        $bobotJodoh = (float) ($bank->bobot_jodohkan ?? 0);
        $tampilJodoh = (int) ($bank->tampil_jodohkan ?? 0);
        $maxPointJodoh = $tampilJodoh > 0 ? $bobotJodoh / $tampilJodoh : 0;

        $bobotIsian = (float) ($bank->bobot_isian ?? 0);
        $tampilIsian = (int) ($bank->tampil_isian ?? 0);
        $maxPointIsian = $tampilIsian > 0 ? $bobotIsian / $tampilIsian : 0;

        $bobotEsai = (float) ($bank->bobot_esai ?? 0);
        $tampilEsai = (int) ($bank->tampil_esai ?? 0);
        $maxPointEsai = $tampilEsai > 0 ? $bobotEsai / $tampilEsai : 0;

        $grouped = [
            'pg' => [],
            'kompleks' => [],
            'jodohkan' => [],
            'isian' => [],
            'esai' => []
        ];

        $benarPg = 0;
        $skorPg = 0.0;
        $skorKomp = 0.0;
        $skorJodoh = 0.0;
        $skorIsian = 0.0;
        $skorEsai = 0.0;

        foreach ($soals as $idx => $s) {
            $idSoal = (string) $s->id_soal;
            $jwb = $mapJawaban[$idSoal] ?? null;
            $nil = $mapNilai[$idSoal] ?? null;
            $jenis = (int) $s->jenis;
            $nomor = $s->nomor_soal ?: ($idx + 1);

            if ($jenis === 1) {
                // I. Pilihan Ganda
                $userAns = strtoupper(trim($jwb['jawaban_siswa'] ?? ''));
                $kunciAns = strtoupper(trim($s->jawaban ?? ($jwb['jawaban_benar'] ?? '')));
                $isBenar = ($userAns !== '' && $userAns === $kunciAns);
                if ($isBenar) {
                    $benarPg++;
                }
                $point = $isBenar ? $maxPointPg : 0.0;
                $skorPg += $point;

                $grouped['pg'][] = [
                    'id_soal' => $s->id_soal,
                    'nomor' => $nomor,
                    'soal' => $s->soal,
                    'opsi' => [
                        'A' => $s->opsi_a,
                        'B' => $s->opsi_b,
                        'C' => $s->opsi_c,
                        'D' => $s->opsi_d,
                        'E' => $s->opsi_e,
                    ],
                    'jawaban_benar' => $kunciAns,
                    'jawaban_siswa' => $userAns ?: '-',
                    'is_benar' => $isBenar,
                    'skor' => round($point, 2),
                    'max_point' => round($maxPointPg, 2)
                ];
            } elseif ($jenis === 2) {
                // II. PG Kompleks
                $userAns = $jwb['jawaban_siswa'] ?? [];
                $kunciAns = $s->jawaban ?? ($jwb['jawaban_benar'] ?? '');
                $point = isset($nil['nilai_koreksi']) ? (float) $nil['nilai_koreksi'] : (isset($jwb['point_soal']) ? (float) $jwb['point_soal'] : 0.0);
                $skorKomp += $point;

                $grouped['kompleks'][] = [
                    'id_soal' => $s->id_soal,
                    'nomor' => $nomor,
                    'soal' => $s->soal,
                    'opsi' => [
                        'A' => $s->opsi_a,
                        'B' => $s->opsi_b,
                        'C' => $s->opsi_c,
                        'D' => $s->opsi_d,
                        'E' => $s->opsi_e,
                    ],
                    'jawaban_benar' => is_array($kunciAns) ? implode(', ', $kunciAns) : $kunciAns,
                    'jawaban_siswa' => is_array($userAns) ? implode(', ', $userAns) : ($userAns ?: '-'),
                    'skor' => round($point, 2),
                    'max_point' => round($maxPointKomp, 2)
                ];
            } elseif ($jenis === 3) {
                // III. Menjodohkan
                $userAns = $jwb['jawaban_siswa'] ?? [];
                $kunciAns = $s->jawaban ?? ($jwb['jawaban_benar'] ?? '');
                $point = isset($nil['nilai_koreksi']) ? (float) $nil['nilai_koreksi'] : 0.0;
                $skorJodoh += $point;

                $grouped['jodohkan'][] = [
                    'id_soal' => $s->id_soal,
                    'nomor' => $nomor,
                    'soal' => $s->soal,
                    'jawaban_benar' => is_array($kunciAns) ? json_encode($kunciAns) : $kunciAns,
                    'jawaban_siswa' => is_array($userAns) ? json_encode($userAns) : ($userAns ?: '-'),
                    'skor' => round($point, 2),
                    'max_point' => round($maxPointJodoh, 2)
                ];
            } elseif ($jenis === 4) {
                // IV. Isian Singkat
                $userAns = trim(strip_tags($jwb['jawaban_siswa'] ?? ''));
                $kunciAns = trim(strip_tags($s->jawaban ?? ($jwb['jawaban_benar'] ?? '')));
                $isBenar = (strtolower($userAns) === strtolower($kunciAns) && $userAns !== '');
                $point = isset($nil['nilai_koreksi']) ? (float) $nil['nilai_koreksi'] : ($isBenar ? $maxPointIsian : 0.0);
                $skorIsian += $point;

                $grouped['isian'][] = [
                    'id_soal' => $s->id_soal,
                    'nomor' => $nomor,
                    'soal' => $s->soal,
                    'jawaban_benar' => $kunciAns ?: '--',
                    'jawaban_siswa' => $userAns ?: '-',
                    'is_benar' => $isBenar,
                    'skor' => round($point, 2),
                    'max_point' => round($maxPointIsian, 2)
                ];
            } elseif ($jenis === 5) {
                // V. Uraian / Esai
                $userAns = $jwb['jawaban_siswa'] ?? '';
                $kunciAns = $s->jawaban ?? ($jwb['jawaban_benar'] ?? '');
                $point = isset($nil['nilai_koreksi']) ? (float) $nil['nilai_koreksi'] : 0.0;
                $skorEsai += $point;

                $grouped['esai'][] = [
                    'id_soal' => $s->id_soal,
                    'nomor' => $nomor,
                    'soal' => $s->soal,
                    'jawaban_benar' => $kunciAns ?: '--',
                    'jawaban_siswa' => $userAns ?: '-',
                    'skor' => round($point, 2),
                    'max_point' => round($maxPointEsai, 2)
                ];
            }
        }

        // Kalkulasi skor PG Garuda CBT: (benar / tampil) * bobot
        if ($tampilPg > 0 && $bobotPg > 0) {
            $skorPg = round(($benarPg / $tampilPg) * $bobotPg, 2);
        }

        // Jika nilai sudah pernah disimpan di nilai_input, utamakan yang tersimpan
        if (isset($nilaiInput['pg_nilai']) && is_numeric($nilaiInput['pg_nilai']) && (float) $nilaiInput['pg_nilai'] > 0) {
            $skorPg = round((float) $nilaiInput['pg_nilai'], 2);
        }
        if (isset($nilaiInput['essai_nilai']) && is_numeric($nilaiInput['essai_nilai']) && (float) $nilaiInput['essai_nilai'] > 0) {
            $skorEsai = round((float) $nilaiInput['essai_nilai'], 2);
        }

        $totalNilai = round($skorPg + $skorKomp + $skorJodoh + $skorIsian + $skorEsai, 2);

        $hanyaPG = count($grouped['pg']) > 0 && count($grouped['kompleks']) === 0 &&
            count($grouped['jodohkan']) === 0 && count($grouped['isian']) === 0 && count($grouped['esai']) === 0;

        $isDikoreksi = !empty($nilaiInput['dikoreksi']) && $nilaiInput['dikoreksi'] == '1';

        $scores = [
            'pg' => round($skorPg, 2),
            'kompleks' => round($skorKomp, 2),
            'jodohkan' => round($skorJodoh, 2),
            'isian' => round($skorIsian, 2),
            'esai' => round($skorEsai, 2),
            'total' => round($totalNilai, 2),
        ];

        return view('guru.koreksi.form', compact(
            'cbtSiswa',
            'bank',
            'noPeserta',
            'sesiSiswa',
            'grouped',
            'scores',
            'hanyaPG',
            'isDikoreksi',
            'maxPointEsai',
            'maxPointPg',
            'guru'
        ));
    }

    /**
     * Simpan hasil koreksi skor esai dan tandai status koreksi.
     */
    public function storeKoreksi(Request $request, int $cbtSiswaId): RedirectResponse
    {
        $cbtSiswa = CbtSiswa::findOrFail($cbtSiswaId);
        $bank = $cbtSiswa->jadwal?->bankSoal;

        $rawNilai = is_string($cbtSiswa->nilai) ? json_decode($cbtSiswa->nilai, true) : ($cbtSiswa->nilai ?? []);
        if (!is_array($rawNilai)) {
            $rawNilai = [];
        }

        $mapNilai = [];
        foreach ($rawNilai as $n) {
            if (isset($n['id_soal'])) {
                $mapNilai[(string) $n['id_soal']] = $n;
            }
        }

        // Ambil input per-butir esai jika tersedia
        $skorSoal = $request->input('skor_soal', []);
        $totalEsai = 0.0;

        if (is_array($skorSoal) && count($skorSoal) > 0) {
            foreach ($skorSoal as $idSoal => $skor) {
                $val = (float) $skor;
                $totalEsai += $val;

                if (!isset($mapNilai[(string) $idSoal])) {
                    $mapNilai[(string) $idSoal] = [
                        'id_bank' => (string) ($bank?->id_bank ?? ''),
                        'id_jadwal' => (string) $cbtSiswa->id_jadwal,
                        'id_soal' => (string) $idSoal,
                        'nilai_otomatis' => 0,
                    ];
                }
                $mapNilai[(string) $idSoal]['nilai_koreksi'] = $val;
            }
        } else {
            $totalEsai = (float) $request->input('nilai_esai', 0);
        }

        $cbtSiswa->nilai = array_values($mapNilai);

        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);
        if (!is_array($nilaiInput)) {
            $nilaiInput = [];
        }

        $nilaiInput['dikoreksi'] = '1';
        $nilaiInput['essai_nilai'] = $totalEsai;

        $pgNilai = (float) ($nilaiInput['pg_nilai'] ?? 0);
        $kompNilai = (float) ($nilaiInput['kompleks_nilai'] ?? 0);
        $jodNilai = (float) ($nilaiInput['jodohkan_nilai'] ?? 0);
        $isNilai = (float) ($nilaiInput['isian_nilai'] ?? 0);

        $nilaiInput['skor_total'] = round($pgNilai + $kompNilai + $jodNilai + $isNilai + $totalEsai, 2);

        $cbtSiswa->nilai_input = json_encode($nilaiInput);
        $cbtSiswa->save();

        return back()->with('success', 'Hasil penilaian esai berhasil disimpan.');
    }

    /**
     * Tandai siswa sudah dikoreksi via tombol / AJAX.
     */
    public function tandaiDikoreksi(int $cbtSiswaId): JsonResponse|RedirectResponse
    {
        $cbtSiswa = CbtSiswa::findOrFail($cbtSiswaId);

        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);
        if (!is_array($nilaiInput)) {
            $nilaiInput = [];
        }

        $nilaiInput['dikoreksi'] = '1';
        $cbtSiswa->nilai_input = json_encode($nilaiInput);
        $cbtSiswa->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil ditandai sudah dikoreksi.'
            ]);
        }

        return back()->with('success', 'Jawaban berhasil ditandai sudah dikoreksi.');
    }
}

