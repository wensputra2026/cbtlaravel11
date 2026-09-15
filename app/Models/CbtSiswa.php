<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtSiswa extends Model
{
    /**
     * Nama tabel sesi dan rekaman ujian siswa.
     *
     * @var string
     */
    protected $table = 'cbt_siswa';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_cbt_siswa';

    /**
     * Nonaktifkan default timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Relasi ke data Siswa.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(MasterSiswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Relasi ke Jadwal Ujian.
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwal::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Relasi ke Bank Soal.
     */
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(CbtBankSoal::class, 'id_bank', 'id_bank');
    }
}
