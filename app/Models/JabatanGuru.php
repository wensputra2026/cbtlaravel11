<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JabatanGuru extends Model
{
    protected $table = 'jabatan_guru';
    protected $primaryKey = 'id_jabatan_guru';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(MasterGuru::class, 'id_guru', 'id_guru');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LevelGuru::class, 'id_jabatan', 'id_level');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(MasterKelas::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Decode mapel_kelas JSON/serialized attribute
     */
    public function getParsedMapelKelasAttribute(): array
    {
        if (empty($this->mapel_kelas)) {
            return [];
        }

        $val = $this->mapel_kelas;
        // Check if serialized PHP
        if (is_string($val) && (str_starts_with($val, 'a:') || str_starts_with($val, 's:') || str_starts_with($val, 'O:'))) {
            $unserialized = @unserialize($val);
            if ($unserialized !== false) {
                return json_decode(json_encode($unserialized), true) ?: [];
            }
        }

        // Check if JSON
        $json = json_decode($val, true);
        return is_array($json) ? $json : [];
    }

    /**
     * Decode ekstra_kelas JSON/serialized attribute
     */
    public function getParsedEkstraKelasAttribute(): array
    {
        if (empty($this->ekstra_kelas)) {
            return [];
        }

        $val = $this->ekstra_kelas;
        // Check if serialized PHP
        if (is_string($val) && (str_starts_with($val, 'a:') || str_starts_with($val, 's:') || str_starts_with($val, 'O:'))) {
            $unserialized = @unserialize($val);
            if ($unserialized !== false) {
                return json_decode(json_encode($unserialized), true) ?: [];
            }
        }

        // Check if JSON
        $json = json_decode($val, true);
        return is_array($json) ? $json : [];
    }
}
