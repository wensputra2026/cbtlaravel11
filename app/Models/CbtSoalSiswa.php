<?php

namespace App\Models;

use App\Casts\SerializedOrJsonCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtSoalSiswa extends Model
{
    /**
     * Nama tabel log jawaban dan urutan soal per siswa di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'cbt_soal_siswa';

    /**
     * Kunci primer tabel berupa varchar string.
     *
     * @var string
     */
    protected $primaryKey = 'id_soal_siswa';

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
            'jawaban_alias' => SerializedOrJsonCast::class,
            'jawaban_siswa' => SerializedOrJsonCast::class,
            'jawaban_benar' => SerializedOrJsonCast::class,
            'id_bank'       => 'integer',
            'id_jadwal'     => 'integer',
            'id_soal'       => 'integer',
            'id_siswa'      => 'integer',
            'jenis_soal'    => 'integer',
            'no_soal_alias' => 'integer',
            'point_essai'   => 'integer',
            'soal_end'      => 'integer',
            'nilai_otomatis'=> 'integer',
        ];
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

    /**
     * Relasi ke Butir Soal master.
     */
    public function soal(): BelongsTo
    {
        return $this->belongsTo(CbtSoal::class, 'id_soal', 'id_soal');
    }

    /**
     * Relasi ke Data Siswa peserta ujian.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(MasterSiswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Generator ID kunci primer format Garuda CBT:
     * Format standar: {id_jadwal}{id_bank}{id_siswa}{id_soal}
     */
    public static function generateId(int|string $idJadwal, int|string $idBank, int|string $idSiswa, int|string $idSoal): string
    {
        return "{$idJadwal}{$idBank}{$idSiswa}{$idSoal}";
    }
}
