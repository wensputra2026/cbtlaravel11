<?php

namespace App\Models;

use App\Casts\SerializedOrJsonCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtJadwal extends Model
{
    /**
     * Nama tabel sesuai database lama Garuda CBT.
     *
     * @var string
     */
    protected $table = 'cbt_jadwal';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_jadwal';

    /**
     * Database lama tidak menggunakan timestamps Laravel.
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
            'pengawas'      => SerializedOrJsonCast::class,
            'acak_soal'     => 'boolean',
            'acak_opsi'     => 'boolean',
            'hasil_tampil'  => 'boolean',
            'token'         => 'boolean',
            'status'        => 'boolean',
            'ulang'         => 'boolean',
            'reset_login'   => 'boolean',
            'durasi_ujian'  => 'integer',
            'rekap'         => 'integer',
            'jam_ke'        => 'integer',
            'jarak'         => 'integer',
            'id_tp'         => 'integer',
            'id_smt'        => 'integer',
            'id_bank'       => 'integer',
            'id_jenis'      => 'integer',
        ];
    }

    /**
     * Relasi ke Bank Soal yang diujikan.
     */
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(CbtBankSoal::class, 'id_bank', 'id_bank');
    }

    /**
     * Relasi ke Jenis Ujian (misal: PAS, PTS, US, USBK, dll).
     */
    public function jenis(): BelongsTo
    {
        return $this->belongsTo(CbtJenis::class, 'id_jenis', 'id_jenis');
    }

    public function tp(): BelongsTo
    {
        return $this->belongsTo(MasterTp::class, 'id_tp', 'id_tp');
    }

    public function smt(): BelongsTo
    {
        return $this->belongsTo(MasterSmt::class, 'id_smt', 'id_smt');
    }

    /**
     * Relasi ke sesi durasi / pengerjaan seluruh peserta.
     */
    public function durasiSiswa(): HasMany
    {
        return $this->hasMany(CbtDurasiSiswa::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Relasi ke seluruh butir log jawaban siswa pada jadwal ini.
     */
    public function soalSiswa(): HasMany
    {
        return $this->hasMany(CbtSoalSiswa::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Relasi ke rekapan nilai siswa pada jadwal ini.
     */
    public function nilais(): HasMany
    {
        return $this->hasMany(CbtNilai::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Scope untuk jadwal ujian yang berstatus aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Cek apakah ujian membutuhkan validasi token.
     */
    public function isTokenRequired(): bool
    {
        return (bool) $this->token;
    }

    /**
     * Cek apakah jadwal ujian masih dalam rentang waktu pelaksanaan.
     */
    public function isDalamRentangWaktu(): bool
    {
        $now = date('Y-m-d H:i:s');
        if (!empty($this->tgl_mulai) && $now < $this->tgl_mulai) {
            return false;
        }
        if (!empty($this->tgl_selesai) && $now > $this->tgl_selesai) {
            return false;
        }
        return true;
    }
}
