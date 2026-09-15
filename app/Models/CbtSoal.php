<?php

namespace App\Models;

use App\Casts\SerializedOrJsonCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtSoal extends Model
{
    /**
     * Tipe-tipe soal Garuda CBT:
     * 1: Pilihan Ganda Biasa (PG)
     * 2: Pilihan Ganda Kompleks (PG Kompleks / Benar-Salah / Checklist)
     * 3: Menjodohkan (Matching)
     * 4: Isian Singkat (Short Answer)
     * 5: Uraian / Esai (Essay)
     */
    public const JENIS_PG        = 1;
    public const JENIS_KOMPLEKS  = 2;
    public const JENIS_JODOHKAN  = 3;
    public const JENIS_ISIAN     = 4;
    public const JENIS_ESAI      = 5;

    /**
     * Nama tabel sesuai database lama Garuda CBT.
     *
     * @var string
     */
    protected $table = 'cbt_soal';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_soal';

    /**
     * Database lama tidak menggunakan default timestamps Laravel.
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
     * Attribute casting untuk konversi otomatis JSON/Array/Serialization.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'opsi_a'        => SerializedOrJsonCast::class,
            'opsi_b'        => SerializedOrJsonCast::class,
            'opsi_c'        => SerializedOrJsonCast::class,
            'opsi_d'        => SerializedOrJsonCast::class,
            'opsi_e'        => SerializedOrJsonCast::class,
            'jawaban'       => SerializedOrJsonCast::class,
            'jenis'         => 'integer',
            'nomor_soal'    => 'integer',
            'created_on'    => 'integer',
            'updated_on'    => 'integer',
            'tampilkan'     => 'integer',
            'kesulitan'     => 'integer',
            'timer'         => 'integer',
            'timer_menit'   => 'integer',
        ];
    }

    /**
     * Relasi ke Bank Soal induk.
     */
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(CbtBankSoal::class, 'bank_id', 'id_bank');
    }

    /**
     * Relasi ke Mata Pelajaran.
     */
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MasterMapel::class, 'mapel_id', 'id_mapel');
    }

    /**
     * Relasi ke log pengerjaan siswa untuk butir soal ini.
     */
    public function soalSiswa(): HasMany
    {
        return $this->hasMany(CbtSoalSiswa::class, 'id_soal', 'id_soal');
    }

    /**
     * Helper pengecekan tipe soal.
     */
    public function isPg(): bool
    {
        return (int) $this->jenis === self::JENIS_PG;
    }

    public function isKompleks(): bool
    {
        return (int) $this->jenis === self::JENIS_KOMPLEKS;
    }

    public function isJodohkan(): bool
    {
        return (int) $this->jenis === self::JENIS_JODOHKAN;
    }

    public function isIsian(): bool
    {
        return (int) $this->jenis === self::JENIS_ISIAN;
    }

    public function isEsai(): bool
    {
        return (int) $this->jenis === self::JENIS_ESAI;
    }

    /**
     * Mengembalikan array opsi jawaban A-E yang tersedia.
     */
    public function getOpsiMapAttribute(): array
    {
        return array_filter([
            'A' => $this->opsi_a,
            'B' => $this->opsi_b,
            'C' => $this->opsi_c,
            'D' => $this->opsi_d,
            'E' => $this->opsi_e,
        ], static fn($val) => $val !== null && $val !== '');
    }
}
