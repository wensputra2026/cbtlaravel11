<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtDurasiSiswa extends Model
{
    /**
     * Status pengerjaan ujian:
     * 0 = Belum mulai
     * 1 = Sedang mengerjakan
     * 2 = Selesai mengerjakan
     */
    public const STATUS_BELUM   = 0;
    public const STATUS_SEDANG  = 1;
    public const STATUS_SELESAI = 2;

    /**
     * Nama tabel sesi dan durasi pengerjaan siswa.
     *
     * @var string
     */
    protected $table = 'cbt_durasi_siswa';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_durasi';

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
     * Attribute casting.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'id_siswa'  => 'integer',
            'id_jadwal' => 'integer',
            'status'    => 'integer',
            'reset'     => 'integer',
        ];
    }

    /**
     * Relasi ke Data Siswa.
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
     * Helper generator ID durasi format Garuda CBT:
     * Standar format: {id_siswa}{id_jadwal}
     */
    public static function generateId(int|string $idSiswa, int|string $idJadwal): string
    {
        return "{$idSiswa}{$idJadwal}";
    }

    /**
     * Status helper.
     */
    public function isSedangUjian(): bool
    {
        return (int) $this->status === self::STATUS_SEDANG;
    }

    public function isSelesai(): bool
    {
        return (int) $this->status === self::STATUS_SELESAI;
    }

    public function isMintaReset(): bool
    {
        return (int) $this->reset === 1;
    }
}
