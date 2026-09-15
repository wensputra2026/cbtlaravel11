<?php

namespace App\Models;

use App\Casts\SerializedOrJsonCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtBankSoal extends Model
{
    /**
     * Nama tabel sesuai database lama Garuda CBT (CodeIgniter 3).
     *
     * @var string
     */
    protected $table = 'cbt_bank_soal';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_bank';

    /**
     * Database lama tidak menggunakan default created_at dan updated_at Laravel.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Kolom yang tidak boleh di-mass assign (kosong = semua kolom boleh diisi).
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Attribute casting untuk konversi otomatis tipe data dan format JSON/Array.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'bank_kelas'        => SerializedOrJsonCast::class,
            'opsi'              => 'integer',
            'kkm'               => 'integer',
            'jml_soal'          => 'integer',
            'jml_esai'          => 'integer',
            'tampil_pg'         => 'integer',
            'tampil_esai'       => 'integer',
            'bobot_pg'          => 'integer',
            'bobot_esai'        => 'integer',
            'jml_kompleks'      => 'integer',
            'tampil_kompleks'   => 'integer',
            'bobot_kompleks'    => 'integer',
            'jml_jodohkan'      => 'integer',
            'tampil_jodohkan'   => 'integer',
            'bobot_jodohkan'    => 'integer',
            'jml_isian'         => 'integer',
            'tampil_isian'      => 'integer',
            'bobot_isian'       => 'integer',
            'status'            => 'integer',
            'status_soal'       => 'integer',
        ];
    }

    /**
     * Relasi ke daftar butir soal dalam bank soal ini.
     */
    public function soals(): HasMany
    {
        return $this->hasMany(CbtSoal::class, 'bank_id', 'id_bank')->orderBy('nomor_soal', 'asc');
    }

    /**
     * Relasi ke jadwal-jadwal ujian yang menggunakan bank soal ini.
     */
    public function jadwals(): HasMany
    {
        return $this->hasMany(CbtJadwal::class, 'id_bank', 'id_bank');
    }

    /**
     * Relasi ke mata pelajaran.
     */
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MasterMapel::class, 'bank_mapel_id', 'id_mapel');
    }

    /**
     * Relasi ke Guru Pembuat Bank Soal.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(MasterGuru::class, 'bank_guru_id', 'id_guru');
    }

    /**
     * Relasi ke jurusan terkait (jika ada spesifikasi jurusan).
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(MasterJurusan::class, 'bank_jurusan_id', 'id_jurusan');
    }

    /**
     * Relasi ke log jawaban siswa pada bank soal ini.
     */
    public function soalSiswa(): HasMany
    {
        return $this->hasMany(CbtSoalSiswa::class, 'id_bank', 'id_bank');
    }

    /**
     * Helper accessor untuk mengekstrak array ID kelas yang diizinkan mengerjakan bank soal ini.
     * Mendukung format array of object [{'kelas_id': 1}] maupun array of scalar [1, 2].
     */
    public function getKelasIdsAttribute(): array
    {
        $raw = $this->bank_kelas;
        if (empty($raw) || !is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $item) {
            if (is_array($item) && isset($item['kelas_id'])) {
                $ids[] = (int) $item['kelas_id'];
            } elseif (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        return array_unique($ids);
    }
}
