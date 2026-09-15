<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtSesiSiswa extends Model
{
    /**
     * Nama tabel alokasi sesi & ruang siswa di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'cbt_sesi_siswa';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'siswa_id';

    /**
     * Kunci primer tidak auto-increment.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Nonaktifkan default timestamps Laravel.
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
     * Relasi ke Data Siswa.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(MasterSiswa::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Relasi ke Kelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(MasterKelas::class, 'kelas_id', 'id_kelas');
    }

    /**
     * Relasi ke Sesi Ujian.
     */
    public function sesi(): BelongsTo
    {
        return $this->belongsTo(CbtSesi::class, 'sesi_id', 'id_sesi');
    }

    /**
     * Relasi ke Ruang Ujian.
     */
    public function ruang(): BelongsTo
    {
        return $this->belongsTo(CbtRuang::class, 'ruang_id', 'id_ruang');
    }
}
