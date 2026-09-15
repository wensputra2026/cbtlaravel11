<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefMapel extends Model
{
    protected $table = 'ref_mapel';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'is_pilihan',
        'agama',
    ];

    protected $casts = [
        'is_pilihan' => 'boolean',
    ];

    public function bankSoal(): HasMany
    {
        return $this->hasMany(CbtBankSoalClean::class, 'mapel_id');
    }

    public function siswaPilihan(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Siswa::class,
            'siswa_mapel_pilihan',
            'mapel_id',
            'siswa_id'
        )->withPivot('tahun_ajaran_id');
    }
}
