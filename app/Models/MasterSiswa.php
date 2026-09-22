<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class MasterSiswa extends Model
{
    /**
     * Nama tabel data master siswa Garuda CBT.
     *
     * @var string
     */
    protected $table = 'master_siswa';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_siswa';

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
     * Sembunyikan password siswa dari serialisasi JSON/Array.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Relasi ke akun login di tabel `users` (dihubungkan via username).
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'username', 'username');
    }

    /**
     * Relasi ke entri rombel / kelas siswa (`kelas_siswa`).
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Relasi langsung ke Master Kelas melalui tabel perantara `kelas_siswa`.
     */
    public function kelas(): HasOneThrough
    {
        return $this->hasOneThrough(
            MasterKelas::class,
            KelasSiswa::class,
            'id_siswa',   // Foreign key on kelas_siswa table
            'id_kelas',   // Foreign key on master_kelas table
            'id_siswa',   // Local key on master_siswa table
            'id_kelas'    // Local key on kelas_siswa table
        );
    }

    /**
     * Relasi ke sesi durasi pengerjaan ujian siswa.
     */
    public function durasiUjian(): HasMany
    {
        return $this->hasMany(CbtDurasiSiswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Relasi ke seluruh jawaban ujian yang dikerjakan siswa.
     */
    public function soalSiswa(): HasMany
    {
        return $this->hasMany(CbtSoalSiswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Relasi ke rekapan nilai ujian siswa.
     */
    public function nilais(): HasMany
    {
        return $this->hasMany(CbtNilai::class, 'id_siswa', 'id_siswa');
    }

    public function nomorPeserta(): HasOne
    {
        return $this->hasOne(CbtNomorPeserta::class, 'id_siswa', 'id_siswa');
    }

    public function sesiSiswa(): HasOne
    {
        return $this->hasOne(CbtSesiSiswa::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Relasi riwayat rombel kelas siswa per tahun ajaran.
     */
    public function rombelTahun(): HasMany
    {
        return $this->hasMany(SiswaRombelTahun::class, 'siswa_id', 'id_siswa');
    }
}
