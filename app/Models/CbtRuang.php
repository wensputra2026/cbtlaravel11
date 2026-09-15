<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtRuang extends Model
{
    /**
     * Nama tabel ruang ujian.
     *
     * @var string
     */
    protected $table = 'cbt_ruang';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_ruang';

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
     * Relasi ke penempatan siswa di ruang ini.
     */
    public function sesiSiswa(): HasMany
    {
        return $this->hasMany(CbtSesiSiswa::class, 'ruang_id', 'id_ruang');
    }
}
