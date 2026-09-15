<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtJadwalClean extends Model
{
    protected $table = 'cbt_jadwal_clean';

    protected $fillable = [
        'bank_id',
        'nama_ujian',
        'kode_jenis',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'acak_soal',
        'acak_opsi',
        'pakai_token',
        'token_default',
        'tampilkan_nilai',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_menit' => 'integer',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'pakai_token' => 'boolean',
        'tampilkan_nilai' => 'boolean',
    ];

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(CbtBankSoalClean::class, 'bank_id');
    }

    public function ujianSiswa(): HasMany
    {
        return $this->hasMany(CbtUjianSiswa::class, 'jadwal_id');
    }
}
