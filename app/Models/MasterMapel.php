<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
