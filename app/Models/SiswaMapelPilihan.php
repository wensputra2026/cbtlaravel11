<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaMapelPilihan extends Model
{
    /**
     * Nama tabel alokasi mata pelajaran pilihan siswa.
     */
    protected $table = 'siswa_mapel_pilihan';

    /**
     * Kolom yang dapat diisi secara mass-assignment.
     */
    protected $fillable = [
        'siswa_id',
        'mapel_id',
        'tahun_ajaran_id',
    ];

    /**
     * Tipe casting atribut.
     */
    protected $casts = [
        'siswa_id'        => 'integer',
        'mapel_id'        => 'integer',
        'tahun_ajaran_id' => 'integer',
    ];

    /**
     * Relasi ke profil data Siswa (skema bersih).
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    /**
     * Relasi ke profil data Siswa (skema legacy master_siswa).
     */
    public function masterSiswa(): BelongsTo
    {
        return $this->belongsTo(MasterSiswa::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Relasi ke Master Mata Pelajaran (skema legacy master_mapel).
     */
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MasterMapel::class, 'mapel_id', 'id_mapel');
    }

    /**
     * Relasi ke Referensi Mata Pelajaran (skema bersih ref_mapel).
     */
    public function refMapel(): BelongsTo
    {
        return $this->belongsTo(RefMapel::class, 'mapel_id', 'id');
    }

    /**
     * Relasi ke Referensi Tahun Ajaran (skema bersih ref_tahun_ajaran).
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(RefTahunAjaran::class, 'tahun_ajaran_id', 'id');
    }

    /**
     * Relasi ke Tahun Pelajaran (skema legacy master_tp).
     */
    public function masterTp(): BelongsTo
    {
        return $this->belongsTo(MasterTp::class, 'tahun_ajaran_id', 'id_tp');
    }
}
