<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMapel extends Model
{
    /**
     * Nama tabel mata pelajaran di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'master_mapel';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_mapel';

    /**
     * Nonaktifkan default timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Mass assignment guard.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Tipe casting atribut model.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_pilihan' => 'boolean',
    ];

    /**
     * Relasi ke kelompok mapel berdasarkan kode kelompok.
     */
    public function kelompokRelasi(): BelongsTo
    {
        return $this->belongsTo(MasterKelompokMapel::class, 'kelompok', 'kode_kel_mapel');
    }

    /**
     * Relasi ke Bank Soal pada mata pelajaran ini.
     */
    public function bankSoal(): HasMany
    {
        return $this->hasMany(CbtBankSoal::class, 'bank_mapel_id', 'id_mapel');
    }

    /**
     * Relasi ke Butir Soal pada mata pelajaran ini.
     */
    public function soals(): HasMany
    {
        return $this->hasMany(CbtSoal::class, 'mapel_id', 'id_mapel');
    }

    /**
     * Relasi ke siswa yang memilih mata pelajaran ini (Kurikulum Merdeka).
     */
    public function siswaPilihan(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            MasterSiswa::class,
            'siswa_mapel_pilihan',
            'mapel_id',
            'siswa_id'
        )->withPivot('tahun_ajaran_id');
    }

    /**
     * Scope untuk memfilter mapel pilihan saja.
     */
    public function scopePilihan($query)
    {
        return $query->where('is_pilihan', true);
    }
}
