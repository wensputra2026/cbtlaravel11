<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'kelas_id',
        'sesi_id',
        'ruang_id',
        'nomor_peserta',
        'foto',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(RefKelas::class, 'kelas_id');
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(RefSesi::class, 'sesi_id');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(RefRuang::class, 'ruang_id');
    }

    public function ujian(): HasMany
    {
        return $this->hasMany(CbtUjianSiswa::class, 'siswa_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(CbtJawabanSiswa::class, 'siswa_id');
    }

    /**
     * URL Foto siswa dengan fallback cerdas berbasis gender dan aset default.
     */
    public function getFotoUrlAttribute(): string
    {
        $foto = trim((string)$this->foto);
        if ($foto) {
            $candidates = [
                'uploads/foto_siswa/' . $foto,
                'uploads/foto_siswa/' . $foto . '.jpg',
                'uploads/foto_siswa/' . $foto . '.png',
                $foto,
            ];
            foreach ($candidates as $cand) {
                if (file_exists(public_path($cand))) {
                    return asset($cand);
                }
            }
        }

        $jk = strtoupper(substr((string)$this->jenis_kelamin, 0, 1));
        if ($jk === 'P') {
            return asset('assets/img/siswa-p.png');
        }
        return asset('assets/img/siswa-l.png');
    }
}
