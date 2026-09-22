<?php

namespace App\Services\Exam;

use App\Models\CbtBankSoal;
use App\Models\CbtDurasiSiswa;
use App\Models\CbtJadwal;
use App\Models\CbtNilai;
use App\Models\CbtSoal;
use App\Models\CbtSoalSiswa;
use App\Models\KelasSiswa;
use App\Models\MasterSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ExamSessionService
{
    public function __construct(
        protected CbtTokenService $tokenService,
        protected ExamGradingService $gradingService
    ) {}

    /**
     * Mendapatkan daftar jadwal ujian yang tersedia dan memenuhi syarat untuk siswa.
     */
    public function getAvailableExamsForStudent(int $siswaId): array
    {
        $siswa = MasterSiswa::with('kelasSiswa')->find($siswaId);
        if (!$siswa) {
            return [];
        }

        // Ambil ID kelas aktif siswa
        $kelasIds = $siswa->kelasSiswa->pluck('id_kelas')->toArray();

        // Ambil jadwal-jadwal ujian aktif
        $jadwals = CbtJadwal::with(['bankSoal.mapel', 'jenis'])
            ->where('status', 1)
            ->get();

        $result = [];
        $now = date('Y-m-d H:i:s');

        foreach ($jadwals as $jadwal) {
            $bank = $jadwal->bankSoal;
            if (!$bank) {
                continue;
            }

            // Cek apakah kelas siswa diizinkan untuk bank soal ini
            $bankKelas = $bank->kelas_ids;
            if (!empty($bankKelas) && empty(array_intersect($kelasIds, $bankKelas))) {
                continue;
            }

            // Ambil status pengerjaan siswa untuk jadwal ini
            $durasiKey = CbtDurasiSiswa::generateId($siswaId, $jadwal->id_jadwal);
            $durasi = CbtDurasiSiswa::find($durasiKey);

            $statusExam = 'belum';
            $sisaDetik = $jadwal->durasi_ujian * 60;

            if ($durasi) {
                if ($durasi->status === CbtDurasiSiswa::STATUS_SELESAI) {
                    $statusExam = 'selesai';
                } elseif ($durasi->status === CbtDurasiSiswa::STATUS_SEDANG) {
                    $statusExam = 'sedang';
                    $sisaDetik = $this->calculateRemainingSeconds($jadwal, $durasi);
                    if ($sisaDetik <= 0) {
                        $statusExam = 'habis';
                    }
                }
            }

            $namaJenis = $jadwal->jenis?->nama_jenis ?? null;
            $kodeJenis = $jadwal->jenis?->kode_jenis ?? 'CBT';

            $result[] = [
                'id_jadwal'       => $jadwal->id_jadwal,
                'nama_ujian'      => ($namaJenis ? $namaJenis . ' - ' : '') . ($bank->bank_nama ?? 'Ujian CBT'),
                'jenis_ujian'     => $namaJenis ?? 'Ujian CBT',
                'kode_jenis'      => $kodeJenis,
                'kode_bank'       => $bank->bank_kode,
                'mapel'           => $bank->mapel->nama_mapel ?? 'Mata Pelajaran',
                'durasi_menit'    => (int) $jadwal->durasi_ujian,
                'tgl_mulai'       => $jadwal->tgl_mulai,
                'tgl_selesai'     => $jadwal->tgl_selesai,
                'is_token'        => $jadwal->isTokenRequired(),
                'status_exam'     => $statusExam,
                'sisa_detik'      => max(0, $sisaDetik),
                'total_soal'      => (int) ($bank->tampil_pg + $bank->tampil_kompleks + $bank->tampil_jodohkan + $bank->tampil_isian + $bank->tampil_esai),
            ];
        }

        return $result;
    }

    /**
     * Memulai sesi ujian siswa:
     * - Verifikasi token (jika jadwal mewajibkan token).
     * - Inisialisasi durasi siswa di MySQL / Redis.
     * - Mengacak butir soal & opsi jawaban menggunakan Deterministic Seeded Shuffle.
     * - Mengembalikan payload paket soal bersih tanpa kunci jawaban.
     */
    public function startExam(int $jadwalId, int $siswaId, ?string $tokenInput = null): array
    {
        $jadwal = CbtJadwal::with('bankSoal')->findOrFail($jadwalId);
        $bank = $jadwal->bankSoal;

        if (!$jadwal->status) {
            throw new \RuntimeException('Ujian ini saat ini sedang tidak aktif.');
        }

        // 1. Verifikasi Token Ujian
        if ($jadwal->isTokenRequired()) {
            if (!$this->tokenService->verifyToken($jadwalId, $tokenInput)) {
                throw new \InvalidArgumentException('Token ujian salah atau sudah kadaluarsa!');
            }
        }

        // 2. Ambil atau Buat Data Sesi Durasi
        $durasiId = CbtDurasiSiswa::generateId($siswaId, $jadwalId);
        $durasi = CbtDurasiSiswa::find($durasiId);

        if ($durasi && $durasi->status === CbtDurasiSiswa::STATUS_SELESAI) {
            throw new \RuntimeException('Anda sudah menyelesaikan ujian ini.');
        }

        $now = date('Y-m-d H:i:s');
        if (!$durasi) {
            $durasi = CbtDurasiSiswa::create([
                'id_durasi'  => $durasiId,
                'id_siswa'   => $siswaId,
                'id_jadwal'  => $jadwalId,
                'status'     => CbtDurasiSiswa::STATUS_SEDANG,
                'mulai'      => $now,
                'lama_ujian' => '00:00:00',
                'reset'      => 0,
            ]);
        } elseif ($durasi->status === CbtDurasiSiswa::STATUS_BELUM) {
            $durasi->status = CbtDurasiSiswa::STATUS_SEDANG;
            $durasi->mulai = $now;
            $durasi->save();
        }

        // 3. Cek sisa waktu server
        $sisaDetik = $this->calculateRemainingSeconds($jadwal, $durasi);
        if ($sisaDetik <= 0) {
            $this->finishExam($jadwalId, $siswaId);
            throw new \RuntimeException('Waktu ujian telah habis.');
        }

        // 4. Siapkan Paket Soal dengan Deterministic Seeded Shuffle
        $paketSoal = $this->getSeededExamQuestions($jadwal, $bank, $siswaId);

        // 5. Muat jawaban sementara dari Redis (jika siswa sempat reload/refresh)
        $savedAnswers = $this->getStudentAnswersFromRedis($jadwalId, $siswaId);

        // 6. Muat jumlah pelanggaran anti-cheat
        $violations = $this->getViolationCount($jadwalId, $siswaId);

        return [
            'jadwal' => [
                'id_jadwal'    => $jadwal->id_jadwal,
                'nama_bank'    => $bank->bank_nama,
                'kode_bank'    => $bank->bank_kode,
                'durasi_menit' => (int) $jadwal->durasi_ujian,
                'hasil_tampil' => (bool) $jadwal->hasil_tampil,
            ],
            'sisa_detik'     => $sisaDetik,
            'server_time'    => time(),
            'violations'     => $violations,
            'max_violations' => 3,
            'paket_soal'     => $paketSoal,
            'jawaban_aktif'  => $savedAnswers,
        ];
    }

    /**
     * Autosave jawaban siswa langsung ke Redis Hash (< 2ms) tanpa menyentuh disk MySQL.
     */
    public function autoSaveAnswer(int $jadwalId, int $siswaId, int $soalId, mixed $jawaban, bool $ragu = false): bool
    {
        $redisKey = "cbt_jawaban:{$jadwalId}:{$siswaId}";
        $data = json_encode([
            'jawaban'   => $jawaban,
            'ragu'      => $ragu,
            'timestamp' => time(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            Redis::hset($redisKey, (string) $soalId, $data);
            Redis::expire($redisKey, 86400); // 24 jam TTL
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mencatat pelanggaran anti-cheat (Ganti Tab / Window Blur / DevTools) di Redis.
     */
    public function recordViolation(int $jadwalId, int $siswaId, string $violationType = 'blur'): array
    {
        $redisKey = "cbt_violation:{$jadwalId}:{$siswaId}";
        $counter = 1;

        try {
            $counter = (int) Redis::incr($redisKey);
            Redis::expire($redisKey, 86400);

            // Simpan detail riwayat log pelanggaran
            $logKey = "cbt_violation_log:{$jadwalId}:{$siswaId}";
            Redis::rpush($logKey, json_encode([
                'type' => $violationType,
                'time' => date('Y-m-d H:i:s'),
            ]));
        } catch (\Throwable $e) {
        }

        $maxViolations = 3;
        $isLocked = ($counter >= $maxViolations);

        return [
            'violations'     => $counter,
            'max_violations' => $maxViolations,
            'is_locked'      => $isLocked,
            'message'        => $isLocked
                ? 'Ujian terkunci karena terdeteksi berpindah tab/jendela melebihi batas maksimal!'
                : "Peringatan! Pelanggaran ke-{$counter} dari {$maxViolations}.",
        ];
    }

    /**
     * Sinkronisasi sisa detik timer dari server secara akurat (kebal manipulasi jam lokal).
     */
    public function syncTimer(int $jadwalId, int $siswaId): array
    {
        $jadwal = CbtJadwal::findOrFail($jadwalId);
        $durasiId = CbtDurasiSiswa::generateId($siswaId, $jadwalId);
        $durasi = CbtDurasiSiswa::find($durasiId);

        if (!$durasi || $durasi->status === CbtDurasiSiswa::STATUS_SELESAI) {
            return ['sisa_detik' => 0, 'status' => 'selesai'];
        }

        $sisaDetik = $this->calculateRemainingSeconds($jadwal, $durasi);
        return [
            'sisa_detik'  => max(0, $sisaDetik),
            'server_time' => time(),
            'status'      => $sisaDetik > 0 ? 'aktif' : 'habis',
        ];
    }

    /**
     * Selesai Ujian:
     * 1. Ambil semua jawaban dari Redis buffer.
     * 2. Hitung nilai otomatis via ExamGradingService.
     * 3. Batch sinkronisasi / upsert ke `cbt_soal_siswa` dan `cbt_nilai`.
     * 4. Update status `cbt_durasi_siswa` menjadi 2 (selesai).
     */
    public function finishExam(int $jadwalId, int $siswaId): array
    {
        $jadwal = CbtJadwal::with('bankSoal.soals')->findOrFail($jadwalId);
        $bank = $jadwal->bankSoal;
        $soals = $bank->soals;

        // Ambil seluruh jawaban siswa dari Redis
        $savedAnswers = $this->getStudentAnswersFromRedis($jadwalId, $siswaId);

        // Hitung nilai otomatis
        $gradingResult = $this->gradingService->evaluateExam($bank, $soals, $savedAnswers);

        DB::transaction(function () use ($jadwalId, $bank, $siswaId, $soals, $savedAnswers, $gradingResult) {
            $now = date('Y-m-d H:i:s');

            // 1. Batch Upsert ke `cbt_soal_siswa`
            $soalOrderKey = "cbt_soal_order:{$jadwalId}:{$siswaId}";
            $orderedIds = [];
            try {
                $orderedRaw = Redis::get($soalOrderKey);
                if ($orderedRaw) {
                    $orderedIds = json_decode($orderedRaw, true) ?? [];
                }
            } catch (\Throwable $e) {
            }

            $orderLookup = array_flip($orderedIds);

            foreach ($soals as $soal) {
                $soalId = $soal->id_soal;
                $soalSiswaId = CbtSoalSiswa::generateId($jadwalId, $bank->id_bank, $siswaId, $soalId);
                $eval = $gradingResult['detail_soals'][$soalId] ?? null;

                $aliasNumber = isset($orderLookup[$soalId]) ? ($orderLookup[$soalId] + 1) : $soal->nomor_soal;
                $userAnsRaw = $savedAnswers[$soalId] ?? null;
                $userAnsVal = is_array($userAnsRaw) && array_key_exists('jawaban', $userAnsRaw)
                    ? $userAnsRaw['jawaban']
                    : $userAnsRaw;

                CbtSoalSiswa::query()->updateOrCreate(
                    ['id_soal_siswa' => $soalSiswaId],
                    [
                        'id_bank'        => $bank->id_bank,
                        'id_jadwal'      => $jadwalId,
                        'id_soal'        => $soalId,
                        'id_siswa'       => $siswaId,
                        'jenis_soal'     => (int) $soal->jenis,
                        'no_soal_alias'  => $aliasNumber,
                        'jawaban_alias'  => is_array($userAnsVal) ? json_encode($userAnsVal) : $userAnsVal,
                        'jawaban_siswa'  => is_array($userAnsVal) ? json_encode($userAnsVal) : $userAnsVal,
                        'jawaban_benar'  => is_array($soal->jawaban) ? json_encode($soal->jawaban) : $soal->jawaban,
                        'point_soal'     => (string) ($eval['point_soal'] ?? 0),
                        'nilai_otomatis' => (int) ($eval['nilai_otomatis'] ?? 0),
                        'nilai_koreksi'  => (string) ($eval['nilai_koreksi'] ?? 0),
                        'soal_end'       => 1,
                    ]
                );
            }

            // 2. Simpan Rekap Nilai Akhir ke `cbt_nilai`
            $nilaiId = CbtNilai::generateId($siswaId, $jadwalId);
            CbtNilai::query()->updateOrCreate(
                ['id_nilai' => $nilaiId],
                [
                    'id_siswa'        => (string) $siswaId,
                    'id_jadwal'       => (string) $jadwalId,
                    'pg_benar'        => $gradingResult['pg_benar'],
                    'pg_nilai'        => $gradingResult['pg_nilai'],
                    'kompleks_nilai'  => $gradingResult['kompleks_nilai'],
                    'jodohkan_nilai'  => $gradingResult['jodohkan_nilai'],
                    'isian_nilai'     => $gradingResult['isian_nilai'],
                    'essai_nilai'     => $gradingResult['essai_nilai'],
                ]
            );

            // 3. Update status sesi durasi menjadi Selesai
            $durasiId = CbtDurasiSiswa::generateId($siswaId, $jadwalId);
            $durasi = CbtDurasiSiswa::find($durasiId);
            if ($durasi) {
                $durasi->status = CbtDurasiSiswa::STATUS_SELESAI;
                $durasi->selesai = $now;
                $durasi->save();
            }
        });

        // 4. Bersihkan buffer Redis pengerjaan siswa dan lepaskan device lock
        try {
            Redis::del("cbt_jawaban:{$jadwalId}:{$siswaId}");
            Redis::del("cbt_violation:{$jadwalId}:{$siswaId}");
            Redis::del("cbt_device_lock:{$siswaId}");
        } catch (\Throwable $e) {
        }

        return [
            'success'     => true,
            'message'     => 'Ujian berhasil diselesaikan dan nilai telah tersimpan.',
            'total_nilai' => $gradingResult['total_nilai'],
            'pg_benar'    => $gradingResult['pg_benar'],
            'tampil_hasil'=> (bool) $jadwal->hasil_tampil,
        ];
    }

    /**
     * Algoritma Deterministic Seeded Shuffle:
     * Mengacak urutan butir soal dan opsi jawaban PG secara deterministik berbasis ID Siswa + ID Jadwal.
     * Hasilnya selalu identik jika siswa merefresh halaman atau ganti komputer.
     */
    protected function getSeededExamQuestions(CbtJadwal $jadwal, CbtBankSoal $bank, int $siswaId): array
    {
        $soals = $bank->soals;
        $seed = (int) sprintf('%u', crc32("cbt_seed_{$jadwal->id_jadwal}_{$siswaId}"));

        $soalList = $soals->all();

        // 1. Acak Soal (jika jadwal meminta acak_soal)
        if ($jadwal->acak_soal) {
            $this->seededShuffle($soalList, $seed);
        }

        // Cache urutan ID soal di Redis agar proktor & rekap tahu urutan aslinya
        $orderedIds = array_map(static fn($s) => $s->id_soal, $soalList);
        try {
            Redis::setex("cbt_soal_order:{$jadwal->id_jadwal}:{$siswaId}", 86400, json_encode($orderedIds));
        } catch (\Throwable $e) {
        }

        $formatted = [];
        $noUrut = 1;

        foreach ($soalList as $soal) {
            $opsiArray = [];

            if ((int) $soal->jenis === CbtSoal::JENIS_PG) {
                $rawOpsis = [
                    'A' => $soal->opsi_a,
                    'B' => $soal->opsi_b,
                    'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d,
                    'E' => $soal->opsi_e,
                ];

                // Batasi jumlah opsi sesuai konfigurasi bank soal (misal: 4 opsi A-D untuk SMP, 5 opsi A-E untuk SMA)
                $opsiCount = $bank->opsi > 0 ? $bank->opsi : 5;
                $keys = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $opsiCount);
                $available = [];
                foreach ($keys as $k) {
                    if (!empty($rawOpsis[$k])) {
                        $available[$k] = $rawOpsis[$k];
                    }
                }

                // Acak opsi jawaban jika jadwal meminta acak_opsi
                if ($jadwal->acak_opsi) {
                    $itemSeed = $seed + $soal->id_soal;
                    $items = [];
                    foreach ($available as $label => $text) {
                        $items[] = ['label' => $label, 'content' => $text];
                    }
                    $this->seededShuffle($items, $itemSeed);
                    $opsiArray = $items;
                } else {
                    $items = [];
                    foreach ($available as $label => $text) {
                        $items[] = ['label' => $label, 'content' => $text];
                    }
                    $opsiArray = $items;
                }
            } elseif ((int) $soal->jenis === CbtSoal::JENIS_KOMPLEKS) {
                // Opsi PG Kompleks / Checklist
                $opsiArray = [
                    'A' => $soal->opsi_a,
                    'B' => $soal->opsi_b,
                    'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d,
                    'E' => $soal->opsi_e,
                ];
            } elseif ((int) $soal->jenis === CbtSoal::JENIS_JODOHKAN) {
                // Pasangan Menjodohkan (kiri & kanan)
                $opsiArray = [
                    'left'  => $soal->opsi_a,
                    'right' => $soal->opsi_b,
                ];
            }

            // PENTING: Kunci jawaban TIDAK BOLEH disertakan dalam payload untuk menjaga integritas anti-contek!
            $formatted[] = [
                'nomor_tampil' => $noUrut++,
                'id_soal'      => $soal->id_soal,
                'jenis'        => (int) $soal->jenis,
                'soal'         => $soal->soal,
                'file'         => $soal->file,
                'tipe_file'    => $soal->tipe_file,
                'opsi'         => $opsiArray,
            ];
        }

        return $formatted;
    }

    /**
     * Menghitung sisa detik ujian berdasarkan waktu mulai server + durasi + waktu tambahan dari proktor.
     */
    protected function calculateRemainingSeconds(CbtJadwal $jadwal, CbtDurasiSiswa $durasi): int
    {
        $waktuMulai = strtotime($durasi->mulai);
        $totalDurasiMenit = (int) $jadwal->durasi_ujian;

        // Periksa waktu tambahan khusus dari proktor di Redis
        try {
            $extraMinutes = (int) Redis::get("cbt_extra_time:{$jadwal->id_jadwal}:{$durasi->id_siswa}");
            $totalDurasiMenit += $extraMinutes;
        } catch (\Throwable $e) {
        }

        $waktuSelesai = $waktuMulai + ($totalDurasiMenit * 60);
        $sisaDetik = $waktuSelesai - time();

        return max(0, $sisaDetik);
    }

    /**
     * Membaca semua jawaban siswa yang tersimpan di Redis Hash.
     */
    protected function getStudentAnswersFromRedis(int $jadwalId, int $siswaId): array
    {
        $redisKey = "cbt_jawaban:{$jadwalId}:{$siswaId}";
        try {
            $raw = Redis::hgetall($redisKey);
            if (!$raw) {
                return [];
            }

            $decoded = [];
            foreach ($raw as $soalId => $val) {
                $decoded[$soalId] = json_decode($val, true) ?? $val;
            }
            return $decoded;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Membaca jumlah pelanggaran yang tercatat di Redis.
     */
    protected function getViolationCount(int $jadwalId, int $siswaId): int
    {
        try {
            return (int) Redis::get("cbt_violation:{$jadwalId}:{$siswaId}");
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Seeded Shuffle deterministik menggunakan algoritma Fisher-Yates dengan LCG (Linear Congruential Generator).
     */
    protected function seededShuffle(array &$items, int $seed): void
    {
        mt_srand($seed);
        $count = count($items);
        for ($i = $count - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $items[$i];
            $items[$i] = $items[$j];
            $items[$j] = $tmp;
        }
    }
}
