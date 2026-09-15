<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefRuang extends Model
{
    protected $table = 'ref_ruang';

    protected $fillable = [
        'kode_ruang',
        'nama_ruang',
    ];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'ruang_id');
    }
}
