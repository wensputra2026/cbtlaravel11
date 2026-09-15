<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtJawabanSiswa extends Model
{
    protected $table = 'cbt_jawaban_siswa';

    protected $fillable = [
        'jadwal_id',
        'siswa_id',
        'soal_id',
        'jawaban',
        'ragu',
        'skor_butir',
    ];

    protected $casts = [
        'jawaban' => 'array',
        'ragu' => 'boolean',
        'skor_butir' => 'float',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwalClean::class, 'jadwal_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(CbtSoalClean::class, 'soal_id');
    }
}
