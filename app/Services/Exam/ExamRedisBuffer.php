<?php

namespace App\Services\Exam;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ExamRedisBuffer
{
    protected static ?bool $isRedisAvailable = null;

    /**
     * Memeriksa apakah ekstensi dan server Redis aktif.
     */
    public static function isRedisAvailable(): bool
    {
        if (self::$isRedisAvailable !== null) {
            return self::$isRedisAvailable;
        }

        if (!extension_loaded('redis')) {
            self::$isRedisAvailable = false;
            return false;
        }

        try {
            Redis::ping();
            self::$isRedisAvailable = true;
        } catch (\Throwable $e) {
            self::$isRedisAvailable = false;
        }

        return self::$isRedisAvailable;
    }

    protected static function fallbackStore()
    {
        try {
            return Cache::store('file');
        } catch (\Throwable $e) {
            return Cache::store('array');
        }
    }

    /**
     * HSET ke Redis buffer (< 2ms) dengan fallback transparan ke fast cache store.
     */
    public static function hset(string $key, string $field, string $value, int $ttl = 86400): bool
    {
        if (self::isRedisAvailable()) {
            try {
                Redis::hset($key, $field, $value);
                Redis::expire($key, $ttl);
                return true;
            } catch (\Throwable $e) {
            }
        }

        $cacheKey = "cbt_h_{$key}";
        $data = self::fallbackStore()->get($cacheKey, []);
        if (!is_array($data)) {
            $data = [];
        }
        $data[$field] = $value;
        self::fallbackStore()->put($cacheKey, $data, $ttl);

        return true;
    }

    /**
     * HGETALL dari Redis buffer.
     */
    public static function hgetall(string $key): array
    {
        if (self::isRedisAvailable()) {
            try {
                $raw = Redis::hgetall($key);
                if (!empty($raw) && is_array($raw)) {
                    return $raw;
                }
            } catch (\Throwable $e) {
            }
        }

        $cacheKey = "cbt_h_{$key}";
        $data = self::fallbackStore()->get($cacheKey, []);

        return is_array($data) ? $data : [];
    }

    /**
     * INCR atomic counter di Redis.
     */
    public static function incr(string $key, int $ttl = 86400): int
    {
        if (self::isRedisAvailable()) {
            try {
                $val = (int) Redis::incr($key);
                Redis::expire($key, $ttl);
                return $val;
            } catch (\Throwable $e) {
            }
        }

        $val = (int) self::fallbackStore()->get($key, 0) + 1;
        self::fallbackStore()->put($key, $val, $ttl);

        return $val;
    }

    /**
     * GET nilai string/int dari Redis.
     */
    public static function get(string $key): mixed
    {
        if (self::isRedisAvailable()) {
            try {
                $val = Redis::get($key);
                if ($val !== false && $val !== null) {
                    return $val;
                }
            } catch (\Throwable $e) {
            }
        }

        return self::fallbackStore()->get($key);
    }

    /**
     * SET nilai dengan TTL di Redis.
     */
    public static function set(string $key, mixed $value, int $ttl = 86400): bool
    {
        if (self::isRedisAvailable()) {
            try {
                Redis::setex($key, $ttl, (string) $value);
                return true;
            } catch (\Throwable $e) {
            }
        }

        self::fallbackStore()->put($key, $value, $ttl);

        return true;
    }

    /**
     * DEL key dari Redis.
     */
    public static function del(string $key): bool
    {
        if (self::isRedisAvailable()) {
            try {
                Redis::del($key);
            } catch (\Throwable $e) {
            }
        }

        self::fallbackStore()->forget($key);
        self::fallbackStore()->forget("cbt_h_{$key}");

        return true;
    }

    /**
     * RPUSH append log ke Redis list.
     */
    public static function rpush(string $key, string $value): void
    {
        if (self::isRedisAvailable()) {
            try {
                Redis::rpush($key, $value);
                return;
            } catch (\Throwable $e) {
            }
        }

        $listKey = "cbt_list_{$key}";
        $list = self::fallbackStore()->get($listKey, []);
        if (!is_array($list)) {
            $list = [];
        }
        $list[] = $value;
        self::fallbackStore()->put($listKey, $list, 86400);
    }
}
