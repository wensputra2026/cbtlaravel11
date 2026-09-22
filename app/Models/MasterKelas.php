<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKelas extends Model
{
    /**
     * Nama tabel kelas di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'master_kelas';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_kelas';

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
     * Relasi ke jurusan kelas.
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(MasterJurusan::class, 'jurusan_id', 'id_jurusan');
    }

    /**
     * Relasi ke anggota siswa di kelas ini (`kelas_siswa`).
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'id_kelas', 'id_kelas');
    }
}
