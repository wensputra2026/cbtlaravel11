<?php

namespace App\Services\Exam;

use App\Models\CbtToken;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ExamTokenService
{
    protected const REDIS_TOKEN_KEY = 'cbt_token:active';
    protected const REDIS_TOKEN_TTL_KEY = 'cbt_token:expiry';

    /**
     * Menghasilkan token ujian baru 6 karakter (huruf kapital & angka acak).
     * Disimpan di Redis dengan TTL dan disinkronkan ke tabel `cbt_token` lama.
     *
     * @param int $validMinutes Masa berlaku dalam menit (default: 15 menit)
     * @return string
     */
    public function generateToken(int $validMinutes = 15): string
    {
        // 6 Karakter unik kombinasi huruf kapital dan angka tanpa karakter ambigu (0, O, 1, I)
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $token = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < 6; $i++) {
            $token .= $characters[random_int(0, $max)];
        }

        $ttlSeconds = $validMinutes * 60;
        $expiryTimestamp = time() + $ttlSeconds;

        // 1. Simpan di Redis In-Memory untuk pembacaan ultra-cepat (latensi < 1ms)
        try {
            Redis::setex(self::REDIS_TOKEN_KEY, $ttlSeconds, $token);
            Redis::setex(self::REDIS_TOKEN_TTL_KEY, $ttlSeconds, $expiryTimestamp);
        } catch (\Throwable $e) {
        }

        // 2. Sinkronkan ke tabel MySQL `cbt_token` warisan Garuda CBT
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
     * Mendapatkan token aktif saat ini (mengutamakan Redis, fallback ke MySQL).
     */
    public function getActiveToken(): string
    {
        try {
            $token = Redis::get(self::REDIS_TOKEN_KEY);
            if ($token) {
                return (string) $token;
            }
        } catch (\Throwable $e) {
        }

        // Fallback ke tabel database MySQL
        $dbToken = CbtToken::query()->latest('updated')->first();
        if ($dbToken && !empty($dbToken->token)) {
            // Re-cache ke Redis
            try {
                Redis::setex(self::REDIS_TOKEN_KEY, 900, $dbToken->token);
            } catch (\Throwable $e) {
            }
            return $dbToken->token;
        }

        // Jika belum ada sama sekali, buatkan token baru otomatis
        return $this->generateToken(15);
    }

    /**
     * Mendapatkan sisa detik masa berlaku token aktif.
     */
    public function getTokenTtl(): int
    {
        try {
            $ttl = Redis::ttl(self::REDIS_TOKEN_KEY);
            return $ttl > 0 ? $ttl : 0;
        } catch (\Throwable $e) {
            return 900;
        }
    }

    /**
     * Verifikasi token yang diinput peserta ujian.
     */
    public function verifyToken(?string $inputToken): bool
    {
        if (empty($inputToken)) {
            return false;
        }

        $activeToken = $this->getActiveToken();
        return strtoupper(trim($inputToken)) === strtoupper(trim($activeToken));
    }
}
