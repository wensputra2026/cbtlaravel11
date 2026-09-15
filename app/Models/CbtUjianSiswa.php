<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtUjianSiswa extends Model
{
    protected $table = 'cbt_ujian_siswa';

    protected $fillable = [
        'jadwal_id',
        'siswa_id',
        'waktu_mulai',
        'waktu_selesai',
        'sisa_detik',
        'status',
        'pelanggaran_count',
        'nilai_akhir',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'sisa_detik' => 'integer',
        'pelanggaran_count' => 'integer',
        'nilai_akhir' => 'float',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwalClean::class, 'jadwal_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(CbtJawabanSiswa::class, 'siswa_id', 'siswa_id')
            ->where('jadwal_id', $this->jadwal_id);
    }
}
