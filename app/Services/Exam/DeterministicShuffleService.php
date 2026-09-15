<?php

namespace App\Services\Exam;

use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtSoal;
use Illuminate\Support\Collection;

class DeterministicShuffleService
{
    /**
     * Hitung seed numerik deterministik berbasis ID Siswa dan ID Jadwal Ujian.
     * Menggunakan unsigned 32-bit CRC32 hash string.
     */
    public function generateSeed(int $jadwalId, int $siswaId): int
    {
        return (int) sprintf('%u', crc32("cbt_{$jadwalId}_{$siswaId}_seed"));
    }

    /**
     * Algoritma Fisher-Yates Shuffle menggunakan Linear Congruential Generator (LCG).
     * Menjamin hasil pengacakan 100% konsisten & identik di berbagai versi PHP & OS (Linux/Windows).
     *
     * @param array $items Array yang akan diacak (by reference)
     * @param int $seed Seed integer
     */
    public function seededShuffle(array &$items, int $seed): void
    {
        $count = count($items);
        if ($count <= 1) {
            return;
        }

        $state = $seed & 0x7FFFFFFF;

        for ($i = $count - 1; $i > 0; $i--) {
            // LCG formula: glibc parameters
            $state = ($state * 1103515245 + 12345) & 0x7FFFFFFF;
            $j = $state % ($i + 1);

            // Swap elemen
            $temp = $items[$i];
            $items[$i] = $items[$j];
            $items[$j] = $temp;
        }
    }

    /**
     * Bangun paket soal ujian siswa dengan pengacakan deterministik dan sanitasi kunci jawaban.
     * ZERO MySQL Random Table: Seluruh pengacakan dieksekusi di memori CPU (sub-millisecond).
     *
     * @param CbtJadwal $jadwal Model jadwal ujian
     * @param CbtBankSoal $bank Model bank soal
     * @param int $siswaId ID siswa peserta
     * @return array Paket soal terformat yang aman dikirim ke browser siswa
     */
    public function buildStudentExamPackage(CbtJadwal $jadwal, CbtBankSoal $bank, int $siswaId): array
    {
        $seed = $this->generateSeed($jadwal->id_jadwal, $siswaId);
        $soals = $bank->soals ?? collect([]);

        // Konversi koleksi ke array murni
        $soalList = $soals instanceof Collection ? $soals->all() : (array) $soals;

        // 1. Acak Butir Soal jika opsi acak_soal aktif
        if ((bool) $jadwal->acak_soal) {
            $this->seededShuffle($soalList, $seed);
        }

        $formattedPackage = [];
        $noUrut = 1;

        foreach ($soalList as $soal) {
            $soalId = (int) $soal->id_soal;
            $jenisSoal = (int) ($soal->jenis ?? $soal->jenis_soal ?? 1);

            $opsiArray = [];

            // 2. Acak Opsi Jawaban untuk Pilihan Ganda
            if ($jenisSoal === CbtSoal::JENIS_PG) {
                $rawOpsis = [
                    'A' => $soal->opsi_a,
                    'B' => $soal->opsi_b,
                    'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d,
                    'E' => $soal->opsi_e,
                ];

                // Batasi jumlah opsi sesuai jenjang (misal 5 opsi A-E)
                $opsiCount = (int) ($bank->opsi > 0 ? $bank->opsi : 5);
                $keys = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $opsiCount);

                $available = [];
                foreach ($keys as $k) {
                    if (!empty($rawOpsis[$k])) {
                        $available[$k] = $rawOpsis[$k];
                    }
                }

                $items = [];
                foreach ($available as $label => $content) {
                    $items[] = [
                        'label'   => $label,
                        'content' => $content,
                    ];
                }

                if ((bool) $jadwal->acak_opsi) {
                    // Seed unik per butir soal: seed utama + id_soal
                    $itemSeed = ($seed + $soalId) & 0x7FFFFFFF;
                    $this->seededShuffle($items, $itemSeed);
                }

                $opsiArray = $items;
            } elseif ($jenisSoal === CbtSoal::JENIS_KOMPLEKS) {
                $opsiArray = [
                    ['label' => 'A', 'content' => $soal->opsi_a],
                    ['label' => 'B', 'content' => $soal->opsi_b],
                    ['label' => 'C', 'content' => $soal->opsi_c],
                    ['label' => 'D', 'content' => $soal->opsi_d],
                    ['label' => 'E', 'content' => $soal->opsi_e],
                ];
                $opsiArray = array_values(array_filter($opsiArray, fn($o) => !empty($o['content'])));
            } elseif ($jenisSoal === CbtSoal::JENIS_JODOHKAN) {
                $opsiArray = [
                    'left'  => $soal->opsi_a,
                    'right' => $soal->opsi_b,
                ];
            }

            // 3. SANITASI KUNCI JAWABAN (Zero-Leakage Guarantee)
            // Kunci jawaban (jawaban, bobot, point_soal) DILARANG KELUAR ke sisi klien.
            $formattedPackage[] = [
                'id_soal'      => $soalId,
                'nomor_urut'   => $noUrut++,
                'jenis_soal'   => $jenisSoal,
                'soal'         => $soal->soal,
                'file'         => $soal->file,
                'tipe_file'    => $soal->tipe_file,
                'opsi'         => $opsiArray,
                // Pastikan TIDAK ADA kolom: 'jawaban', 'kunci', 'point_soal'
            ];
        }

        return $formattedPackage;
    }

    /**
     * Dapatkan mapping urutan ID soal deterministik untuk rekap dan pengawasan proktor.
     */
    public function getQuestionOrderMap(CbtJadwal $jadwal, CbtBankSoal $bank, int $siswaId): array
    {
        $seed = $this->generateSeed($jadwal->id_jadwal, $siswaId);
        $soals = $bank->soals ?? collect([]);
        $soalList = $soals instanceof Collection ? $soals->all() : (array) $soals;

        if ((bool) $jadwal->acak_soal) {
            $this->seededShuffle($soalList, $seed);
        }

        $orderMap = [];
        $no = 1;
        foreach ($soalList as $s) {
            $orderMap[(int)$s->id_soal] = $no++;
        }

        return $orderMap;
    }
}
