<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefTahunAjaran extends Model
{
    protected $table = 'ref_tahun_ajaran';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'tgl_mulai'   => 'date',
            'tgl_selesai' => 'date',
        ];
    }

    /**
     * Scope query hanya untuk tahun ajaran aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Dapatkan teks semester yang mudah dibaca (Ganjil / Genap).
     */
    public function getSemesterTextAttribute(): string
    {
        return $this->semester == '1' ? 'Ganjil' : 'Genap';
    }

    /**
     * Relasi ke entri rombel siswa pada tahun ajaran ini.
     */
    public function siswaRombel(): HasMany
    {
        return $this->hasMany(SiswaRombelTahun::class, 'tahun_ajaran_id');
    }
}
