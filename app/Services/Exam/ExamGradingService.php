<?php

namespace App\Services\Exam;

use App\Models\CbtBankSoal;
use App\Models\CbtSoal;

class ExamGradingService
{
    /**
     * Menghitung nilai ujian secara otomatis berdasarkan 5 tipe soal Garuda CBT.
     *
     * @param CbtBankSoal $bank
     * @param array<int, CbtSoal> $soals
     * @param array<int, mixed> $jawabanSiswa Array of [soal_id => answer_value]
     * @return array
     */
    public function evaluateExam(CbtBankSoal $bank, iterable $soals, array $jawabanSiswa): array
    {
        $stats = [
            'pg' => ['total' => 0, 'benar' => 0, 'skor' => 0.0, 'bobot' => (float) $bank->bobot_pg],
            'kompleks' => ['total' => 0, 'benar' => 0, 'skor' => 0.0, 'bobot' => (float) $bank->bobot_kompleks],
            'jodohkan' => ['total' => 0, 'benar' => 0, 'skor' => 0.0, 'bobot' => (float) $bank->bobot_jodohkan],
            'isian' => ['total' => 0, 'benar' => 0, 'skor' => 0.0, 'bobot' => (float) $bank->bobot_isian],
            'esai' => ['total' => 0, 'benar' => 0, 'skor' => 0.0, 'bobot' => (float) $bank->bobot_esai],
        ];

        $detailSoalSiswa = [];

        foreach ($soals as $soal) {
            $soalId = $soal->id_soal;
            $jenis  = (int) $soal->jenis;
            $userAns = $jawabanSiswa[$soalId] ?? null;
            $kunci   = $soal->jawaban;

            // Unpack jika userAns dibungkus metadata ['jawaban' => ..., 'ragu' => ...]
            if (is_array($userAns) && array_key_exists('jawaban', $userAns)) {
                $userAns = $userAns['jawaban'];
            }

            $isBenar = false;
            $pointMaks = 0.0;
            $pointDidapat = 0.0;

            switch ($jenis) {
                case CbtSoal::JENIS_PG:
                    $stats['pg']['total']++;
                    $pointMaks = $bank->tampil_pg > 0 ? (float) ($bank->bobot_pg / $bank->tampil_pg) : 0.0;
                    $isBenar = $this->checkPg($userAns, $kunci);
                    if ($isBenar) {
                        $stats['pg']['benar']++;
                        $pointDidapat = $pointMaks;
                    }
                    break;

                case CbtSoal::JENIS_KOMPLEKS:
                    $stats['kompleks']['total']++;
                    $pointMaks = $bank->tampil_kompleks > 0 ? (float) ($bank->bobot_kompleks / $bank->tampil_kompleks) : 0.0;
                    $pointDidapat = $this->checkKompleks($userAns, $kunci, $pointMaks);
                    $isBenar = ($pointDidapat >= $pointMaks && $pointMaks > 0);
                    if ($isBenar) {
                        $stats['kompleks']['benar']++;
                    }
                    $stats['kompleks']['skor'] += $pointDidapat;
                    break;

                case CbtSoal::JENIS_JODOHKAN:
                    $stats['jodohkan']['total']++;
                    $pointMaks = $bank->tampil_jodohkan > 0 ? (float) ($bank->bobot_jodohkan / $bank->tampil_jodohkan) : 0.0;
                    $pointDidapat = $this->checkJodohkan($userAns, $kunci, $pointMaks);
                    $isBenar = ($pointDidapat >= $pointMaks && $pointMaks > 0);
                    if ($isBenar) {
                        $stats['jodohkan']['benar']++;
                    }
                    $stats['jodohkan']['skor'] += $pointDidapat;
                    break;

                case CbtSoal::JENIS_ISIAN:
                    $stats['isian']['total']++;
                    $pointMaks = $bank->tampil_isian > 0 ? (float) ($bank->bobot_isian / $bank->tampil_isian) : 0.0;
                    $isBenar = $this->checkIsian($userAns, $kunci);
                    if ($isBenar) {
                        $stats['isian']['benar']++;
                        $pointDidapat = $pointMaks;
                    }
                    break;

                case CbtSoal::JENIS_ESAI:
                    $stats['esai']['total']++;
                    $pointMaks = $bank->tampil_esai > 0 ? (float) ($bank->bobot_esai / $bank->tampil_esai) : 0.0;
                    // Esai memerlukan koreksi manual oleh guru
                    $pointDidapat = 0.0;
                    $isBenar = false;
                    break;
            }

            $detailSoalSiswa[$soalId] = [
                'soal_id'        => $soalId,
                'jenis_soal'     => $jenis,
                'jawaban_siswa'  => $userAns,
                'jawaban_benar'  => $kunci,
                'is_benar'       => $isBenar,
                'point_soal'     => round($pointMaks, 2),
                'nilai_otomatis' => $isBenar ? 1 : 0,
                'nilai_koreksi'  => round($pointDidapat, 2),
            ];
        }

        // Kalkulasi skor PG dan Isian
        if ($stats['pg']['total'] > 0 && $bank->tampil_pg > 0) {
            $stats['pg']['skor'] = round(($stats['pg']['benar'] / $bank->tampil_pg) * $bank->bobot_pg, 2);
        }

        if ($stats['isian']['total'] > 0 && $bank->tampil_isian > 0) {
            $stats['isian']['skor'] = round(($stats['isian']['benar'] / $bank->tampil_isian) * $bank->bobot_isian, 2);
        }

        // Total nilai akhir
        $totalNilai = round(
            $stats['pg']['skor'] +
            $stats['kompleks']['skor'] +
            $stats['jodohkan']['skor'] +
            $stats['isian']['skor'] +
            $stats['esai']['skor'],
            2
        );

        return [
            'pg_benar'        => $stats['pg']['benar'],
            'pg_nilai'        => (string) $stats['pg']['skor'],
            'kompleks_nilai'  => (string) round($stats['kompleks']['skor'], 2),
            'jodohkan_nilai'  => (string) round($stats['jodohkan']['skor'], 2),
            'isian_nilai'     => (string) round($stats['isian']['skor'], 2),
            'essai_nilai'     => (string) round($stats['esai']['skor'], 2),
            'total_nilai'     => (string) $totalNilai,
            'detail_soals'    => $detailSoalSiswa,
        ];
    }

    /**
     * Evaluasi Pilihan Ganda (Tipe 1): Perbandingan opsi tunggal (A/B/C/D/E).
     */
    protected function checkPg(mixed $userAnswer, mixed $key): bool
    {
        if (empty($userAnswer) || empty($key)) {
            return false;
        }

        $ans = is_array($userAnswer) ? ($userAnswer[0] ?? '') : $userAnswer;
        $kunci = is_array($key) ? ($key[0] ?? '') : $key;

        return strtoupper(trim((string) $ans)) === strtoupper(trim((string) $kunci));
    }

    /**
     * Evaluasi PG Kompleks (Tipe 2): Checkbox multi-jawaban.
     * Menggunakan metode proporsional: poin penuh jika semua benar dan tidak memilih yang salah.
     */
    protected function checkKompleks(mixed $userAnswer, mixed $key, float $pointMaks): float
    {
        if (empty($userAnswer) || empty($key)) {
            return 0.0;
        }

        $userList = is_array($userAnswer) ? $userAnswer : explode(',', (string) $userAnswer);
        $keyList  = is_array($key) ? $key : explode(',', (string) $key);

        $userList = array_values(array_unique(array_map('trim', array_map('strtoupper', $userList))));
        $keyList  = array_values(array_unique(array_map('trim', array_map('strtoupper', $keyList))));

        sort($userList);
        sort($keyList);

        // Jika persis sama
        if ($userList === $keyList) {
            return $pointMaks;
        }

        // Hitung proporsi jawaban benar tanpa penalti negatif
        $benarTerpilih = count(array_intersect($userList, $keyList));
        $salahTerpilih = count(array_diff($userList, $keyList));

        if ($benarTerpilih > 0 && count($keyList) > 0) {
            $rasio = max(0, ($benarTerpilih - $salahTerpilih) / count($keyList));
            return round($rasio * $pointMaks, 2);
        }

        return 0.0;
    }

    /**
     * Evaluasi Menjodohkan (Tipe 3): Pasangan kunci => nilai.
     */
    protected function checkJodohkan(mixed $userAnswer, mixed $key, float $pointMaks): float
    {
        if (empty($userAnswer) || empty($key)) {
            return 0.0;
        }

        $userPairs = is_array($userAnswer) ? $userAnswer : (json_decode($userAnswer, true) ?? []);
        $keyPairs  = is_array($key) ? $key : (json_decode($key, true) ?? []);

        if (empty($keyPairs) || !is_array($keyPairs)) {
            return 0.0;
        }

        $totalPairs = count($keyPairs);
        $benar = 0;

        foreach ($keyPairs as $left => $right) {
            if (isset($userPairs[$left]) && trim((string) $userPairs[$left]) === trim((string) $right)) {
                $benar++;
            }
        }

        if ($totalPairs > 0) {
            return round(($benar / $totalPairs) * $pointMaks, 2);
        }

        return 0.0;
    }

    /**
     * Evaluasi Isian Singkat (Tipe 4): Teks pendek case-insensitive & whitespace-trimmed.
     */
    protected function checkIsian(mixed $userAnswer, mixed $key): bool
    {
        if ($userAnswer === null || $key === null) {
            return false;
        }

        $cleanUser = strtolower(trim((string) (is_array($userAnswer) ? ($userAnswer[0] ?? '') : $userAnswer)));
        
        // Kunci jawaban isian bisa memiliki alternatif pemisah semicolon (;) atau koma
        $kunciList = is_array($key) ? $key : explode(';', (string) $key);
        foreach ($kunciList as $k) {
            if ($cleanUser === strtolower(trim((string) $k))) {
                return true;
            }
        }

        return false;
    }
}
