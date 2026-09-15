<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom Cast untuk menangani kolom database warisan Garuda CBT (CodeIgniter 3).
 * Kolom seperti `opsi_a`..`opsi_e`, `jawaban`, `bank_kelas`, `pengawas`
 * dapat berisi:
 * 1. String JSON murni (misal: ["A","B"] atau {"a":"1"})
 * 2. PHP Serialized string (misal: a:2:{i:0;s:1:"A";...})
 * 3. Teks HTML / plain string biasa (misal: "<p>Soal nomor 1</p>")
 * 4. Nilai null atau kosong.
 *
 * Cast ini membaca semua kemungkinan secara aman tanpa menimbulkan JsonException atau Unserialize error.
 */
class SerializedOrJsonCast implements CastsAttributes
{
    /**
     * Cast the given value from database to PHP native value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $trimmed = trim($value);

        // 1. Periksa apakah berupa PHP serialized string (format serialize PHP lama CI3)
        if ($this->isSerialized($trimmed)) {
            $unserialized = @unserialize($trimmed);
            if ($unserialized !== false || $trimmed === 'b:0;') {
                return json_decode(json_encode($unserialized), true);
            }
        }

        // 2. Periksa apakah berupa JSON array/object string
        if (
            (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
            (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) ||
            (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"'))
        ) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // 3. Jika berupa string/HTML biasa, kembalikan apa adanya tanpa merusak
        return $value;
    }

    /**
     * Prepare the given value for storage in database.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        // Jika berupa array atau objek, simpan sebagai JSON standar
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }

    /**
     * Deteksi apakah string berformat serialize PHP.
     */
    protected function isSerialized(string $data): bool
    {
        $data = trim($data);
        if ($data === 'N;') {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if ($data[1] !== ':') {
            return false;
        }

        $lastChar = substr($data, -1);
        if ($lastChar !== ';' && $lastChar !== '}') {
            return false;
        }

        $token = $data[0];
        switch ($token) {
            case 's':
                if ($lastChar !== ';') {
                    return false;
                }
                return (bool) preg_match('/^s:[0-9]+:"/s', $data);
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$/", $data);
        }

        return false;
    }
}
