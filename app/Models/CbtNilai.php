<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtNilai extends Model
{
    /**
     * Nama tabel rekapan nilai ujian siswa di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'cbt_nilai';

    /**
     * Kunci primer tabel bertipe string.
     *
     * @var string
     */
    protected $primaryKey = 'id_nilai';

    /**
     * Kunci primer bukan auto-increment.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe data primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

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
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pg_benar'  => 'integer',
        ];
    }

    /**
     * Relasi ke Siswa.
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
     * Generator ID kunci primer format Garuda CBT:
     * Standar format: {id_siswa}{id_jadwal}
     */
    public static function generateId(int|string $idSiswa, int|string $idJadwal): string
    {
        return "{$idSiswa}{$idJadwal}";
    }
}
