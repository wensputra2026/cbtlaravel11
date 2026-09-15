<?php

namespace App\Services\Exam;

use App\Models\CbtToken;
use Illuminate\Support\Facades\Redis;

class CbtTokenService
{
    /**
     * Menghasilkan token ujian 6-digit acak huruf kapital/angka.
     * Disimpan di Redis key `cbt_exam_token:{jadwalId}` dengan TTL tertentu.
     *
     * @param int|string $jadwalId ID Jadwal Ujian
     * @param int $validMinutes Masa aktif token dalam menit (default: 15 menit)
     * @return string
     */
    public function generateToken(int|string $jadwalId, int $validMinutes = 15): string
    {
        // 6 karakter huruf kapital & angka tanpa karakter ambigu
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $token = '';
        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < 6; $i++) {
            $token .= $chars[random_int(0, $maxIndex)];
        }

        $ttlSeconds = $validMinutes * 60;
        $redisKey = "cbt_exam_token:{$jadwalId}";

        // 1. Simpan di Redis In-Memory secara Atomic dengan masa berlaku TTL
        try {
            Redis::setex($redisKey, $ttlSeconds, $token);
            // Simpan juga token global fallback
            Redis::setex('cbt_exam_token:global', $ttlSeconds, $token);
        } catch (\Throwable $e) {
        }

        // 2. Sinkronkan ke tabel `cbt_token` database lama Garuda CBT
        try {
            CbtToken::query()->updateOrCreate(
                ['id_token' => 1],
                [
                    'token'   => $token,
                    'auto'    => 1,
                    'jarak'   => $validMinutes,
                    'updated' => date('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
        }

        return $token;
    }

    /**
     * Mendapatkan token aktif saat ini untuk jadwal tertentu.
     * Urutan prioritas: Redis Jadwal -> Redis Global -> Tabel MySQL `cbt_token`.
     */
    public function getActiveToken(int|string $jadwalId): string
    {
        // 1. Cek Redis token spesifik jadwal
        try {
            $token = Redis::get("cbt_exam_token:{$jadwalId}");
            if ($token) {
                return (string) $token;
            }

            // 2. Cek Redis token global
            $globalToken = Redis::get('cbt_exam_token:global');
            if ($globalToken) {
                return (string) $globalToken;
            }
        } catch (\Throwable $e) {
        }

        // 3. Fallback ke tabel database MySQL lama `cbt_token`
        $dbToken = CbtToken::query()->latest('updated')->first();
        if ($dbToken && !empty($dbToken->token)) {
            // Re-cache ke Redis
            try {
                Redis::setex("cbt_exam_token:{$jadwalId}", 900, $dbToken->token);
            } catch (\Throwable $e) {
            }
            return (string) $dbToken->token;
        }

        // Jika belum ada sama sekali, buatkan token baru otomatis
        return $this->generateToken($jadwalId, 15);
    }

    /**
     * Mendapatkan sisa detik TTL token aktif di Redis.
     */
    public function getTokenTtl(int|string $jadwalId): int
    {
        try {
            $ttl = Redis::ttl("cbt_exam_token:{$jadwalId}");
            if ($ttl > 0) {
                return $ttl;
            }
            $globalTtl = Redis::ttl('cbt_exam_token:global');
            return $globalTtl > 0 ? $globalTtl : 0;
        } catch (\Throwable $e) {
            return 900;
        }
    }

    /**
     * Memvalidasi token sebelum siswa dapat masuk ke antarmuka soal.
     *
     * @param int|string $jadwalId
     * @param string|null $inputToken
     * @return bool
     */
    public function verifyToken(int|string $jadwalId, ?string $inputToken): bool
    {
        if (empty($inputToken)) {
            return false;
        }

        $cleanInput = strtoupper(trim($inputToken));
        $activeToken = strtoupper(trim($this->getActiveToken($jadwalId)));

        // Cocokkan dengan token aktif
        if ($cleanInput === $activeToken) {
            return true;
        }

        // Cek juga fallback dari tabel cbt_token langsung jika pengawas mengubah manual via MySQL
        $dbToken = CbtToken::query()->where('token', $cleanInput)->exists();
        return $dbToken;
    }

    /**
     * Convenience helpers for admin and proctor controllers
     */
    public function getOrGenerateDynamicToken(int|string $jadwalId = 'global'): string
    {
        return $this->getActiveToken($jadwalId);
    }

    public function getTokenRemainingSeconds(int|string $jadwalId = 'global'): int
    {
        return $this->getTokenTtl($jadwalId);
    }

    public function forceGenerateNewToken(int|string $jadwalId = 'global'): string
    {
        return $this->generateToken($jadwalId, 15);
    }
}
