<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtSesi extends Model
{
    /**
     * Nama tabel sesi ujian.
     *
     * @var string
     */
    protected $table = 'cbt_sesi';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_sesi';

    /**
     * Nonaktifkan timestamps Laravel.
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
     * Relasi ke alokasi sesi siswa.
     */
    public function sesiSiswa(): HasMany
    {
        return $this->hasMany(CbtSesiSiswa::class, 'sesi_id', 'id_sesi');
    }
}
