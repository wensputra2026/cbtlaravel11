<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaRombelTahun extends Model
{
    protected $table = 'siswa_rombel_tahun';
    protected $guarded = [];

    /**
     * Relasi ke Siswa (mendukung master_siswa maupun clean siswa).
     */
    public function masterSiswa(): BelongsTo
    {
        return $this->belongsTo(MasterSiswa::class, 'siswa_id', 'id_siswa');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    /**
     * Relasi ke Kelas (mendukung master_kelas maupun ref_kelas).
     */
    public function masterKelas(): BelongsTo
    {
        return $this->belongsTo(MasterKelas::class, 'kelas_id', 'id_kelas');
    }

    public function refKelas(): BelongsTo
    {
        return $this->belongsTo(RefKelas::class, 'kelas_id', 'id');
    }

    /**
     * Relasi ke Tahun Ajaran.
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(RefTahunAjaran::class, 'tahun_ajaran_id', 'id');
    }

    /**
     * Scope status siswa.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeAlumni(Builder $query): Builder
    {
        return $query->where('status', 'lulus');
    }
}
