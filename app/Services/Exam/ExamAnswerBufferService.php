<?php

namespace App\Services\Exam;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ExamAnswerBufferService
{
    protected const TTL_BUFFER_SECONDS = 86400; // 24 jam
    protected static ?bool $isRedisAvailable = null;
    protected static float $lastCheckTime = 0.0;
    protected const CIRCUIT_BREAKER_TTL = 30.0; // 30 detik sebelum mencoba reconnect

    /**
     * Cek apakah koneksi Redis sedang online dan responsif dengan Circuit Breaker.
     */
    public function isRedisAvailable(): bool
    {
        $now = microtime(true);
        if (self::$isRedisAvailable !== null && ($now - self::$lastCheckTime) < self::CIRCUIT_BREAKER_TTL) {
            return self::$isRedisAvailable;
        }

        self::$lastCheckTime = $now;

        try {
            self::$isRedisAvailable = (bool) Redis::ping();
        } catch (\Throwable $e) {
            self::$isRedisAvailable = false;
        }

        return self::$isRedisAvailable;
    }

    /**
     * Dapatkan key Redis untuk jawaban siswa.
     */
    public function getAnswersKey(int $jadwalId, int $siswaId): string
    {
        return "cbt_answers:{$jadwalId}:{$siswaId}";
    }

    /**
     * Dapatkan key Redis untuk status ragu-ragu.
     */
    public function getDoubtsKey(int $jadwalId, int $siswaId): string
    {
        return "cbt_doubts:{$jadwalId}:{$siswaId}";
    }

    /**
     * Simpan jawaban butir soal ke buffer Redis in-memory (< 2ms response time).
     * Menerapkan GRACEFUL FALLBACK ke database MySQL jika Redis server offline.
     *
     * @param int $jadwalId ID Jadwal
     * @param int $siswaId ID Siswa
     * @param int $soalId ID Butir Soal
     * @param mixed $jawaban Nilai jawaban (string, array, dll)
     * @param bool $ragu Status ragu-ragu
     * @return bool True jika berhasil tersimpan
     */
    public function saveAnswer(int $jadwalId, int $siswaId, int $soalId, mixed $jawaban, bool $ragu = false): bool
    {
        // Jika Circuit Breaker mendeteksi Redis Offline, langsung ke fallback DB tanpa delay socket timeout
        if (!$this->isRedisAvailable()) {
            return $this->fallbackSaveToDatabase($jadwalId, $siswaId, $soalId, $jawaban, $ragu);
        }

        $jawabanEncoded = json_encode($jawaban, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $raguVal = $ragu ? '1' : '0';

        // 1. Coba simpan ke Redis In-Memory Buffer
        try {
            $ansKey = $this->getAnswersKey($jadwalId, $siswaId);
            $doubtKey = $this->getDoubtsKey($jadwalId, $siswaId);

            Redis::hset($ansKey, (string) $soalId, $jawabanEncoded);
            Redis::hset($doubtKey, (string) $soalId, $raguVal);

            Redis::expire($ansKey, self::TTL_BUFFER_SECONDS);
            Redis::expire($doubtKey, self::TTL_BUFFER_SECONDS);

            return true;
        } catch (\Throwable $e) {
            self::$isRedisAvailable = false;
            self::$lastCheckTime = microtime(true);

            Log::warning("CBT Redis Buffer offline / gagal, beralih ke MySQL Fallback: " . $e->getMessage(), [
                'jadwal_id' => $jadwalId,
                'siswa_id'  => $siswaId,
                'soal_id'   => $soalId,
            ]);

            // 2. Graceful Fallback: Simpan langsung ke MySQL transaksi cbt_jawaban_siswa
            return $this->fallbackSaveToDatabase($jadwalId, $siswaId, $soalId, $jawaban, $ragu);
        }
    }

    /**
     * Fallback penyimpanan jawaban langsung ke tabel MySQL jika Redis sedang tidak tersedia.
     */
    protected function fallbackSaveToDatabase(int $jadwalId, int $siswaId, int $soalId, mixed $jawaban, bool $ragu): bool
    {
        try {
            DB::table('cbt_jawaban_siswa')->updateOrInsert(
                [
                    'jadwal_id' => $jadwalId,
                    'siswa_id'  => $siswaId,
                    'soal_id'   => $soalId,
                ],
                [
                    'jawaban'    => json_encode($jawaban, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ragu'       => $ragu ? 1 : 0,
                    'updated_at' => now(),
                ]
            );

            return true;
        } catch (\Throwable $dbEx) {
            Log::error("Gagal fallback database jawaban CBT: " . $dbEx->getMessage());
            return false;
        }
    }

    /**
     * Ambil seluruh jawaban dan status ragu siswa dari buffer Redis.
     * Jika Redis kosong / offline, lakukan fallback membaca dari tabel cbt_jawaban_siswa.
     *
     * @return array [soal_id => ['jawaban' => mixed, 'ragu' => bool]]
     */
    public function getAllAnswers(int $jadwalId, int $siswaId): array
    {
        $result = [];

        // 1. Coba baca dari Redis jika online
        if ($this->isRedisAvailable()) {
            try {
                $ansKey = $this->getAnswersKey($jadwalId, $siswaId);
                $doubtKey = $this->getDoubtsKey($jadwalId, $siswaId);

                $rawAnswers = Redis::hgetall($ansKey) ?? [];
                $rawDoubts = Redis::hgetall($doubtKey) ?? [];

                if (!empty($rawAnswers)) {
                    foreach ($rawAnswers as $soalId => $jsonVal) {
                        $decoded = json_decode($jsonVal, true);
                        $result[(int) $soalId] = [
                            'jawaban' => ($decoded !== null || $jsonVal === 'null') ? $decoded : $jsonVal,
                            'ragu'    => isset($rawDoubts[$soalId]) && $rawDoubts[$soalId] === '1',
                        ];
                    }
                    return $result;
                }
            } catch (\Throwable $e) {
                self::$isRedisAvailable = false;
                self::$lastCheckTime = microtime(true);
                Log::warning("Gagal membaca buffer Redis, membaca dari fallback database: " . $e->getMessage());
            }
        }

        // 2. Fallback: Ambil dari MySQL `cbt_jawaban_siswa`
        $dbRows = DB::table('cbt_jawaban_siswa')
            ->where('jadwal_id', $jadwalId)
            ->where('siswa_id', $siswaId)
            ->get();

        foreach ($dbRows as $row) {
            $decoded = json_decode($row->jawaban, true);
            $result[(int) $row->soal_id] = [
                'jawaban' => ($decoded !== null) ? $decoded : $row->jawaban,
                'ragu'    => (bool) $row->ragu,
            ];
        }

        return $result;
    }

    /**
     * Bersihkan buffer Redis pengerjaan siswa setelah commit database berhasil.
     */
    public function clearBuffer(int $jadwalId, int $siswaId): void
    {
        if ($this->isRedisAvailable()) {
            try {
                $ansKey = $this->getAnswersKey($jadwalId, $siswaId);
                $doubtKey = $this->getDoubtsKey($jadwalId, $siswaId);

                Redis::del($ansKey);
                Redis::del($doubtKey);
            } catch (\Throwable $e) {
                self::$isRedisAvailable = false;
                self::$lastCheckTime = microtime(true);
            }
        }
    }
}
