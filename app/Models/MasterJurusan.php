<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterJurusan extends Model
{
    /**
     * Nama tabel kejuruan/jurusan di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'master_jurusan';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_jurusan';

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
     * Relasi ke daftar kelas pada jurusan ini.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(MasterKelas::class, 'jurusan_id', 'id_jurusan');
    }

    /**
     * Relasi ke bank soal yang ditujukan untuk jurusan ini.
     */
    public function bankSoal(): HasMany
    {
        return $this->hasMany(CbtBankSoal::class, 'bank_jurusan_id', 'id_jurusan');
    }
}
