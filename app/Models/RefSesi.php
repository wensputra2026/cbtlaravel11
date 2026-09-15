<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefSesi extends Model
{
    protected $table = 'ref_sesi';

    protected $fillable = [
        'kode_sesi',
        'nama_sesi',
        'waktu_mulai',
        'waktu_selesai',
    ];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'sesi_id');
    }
}
