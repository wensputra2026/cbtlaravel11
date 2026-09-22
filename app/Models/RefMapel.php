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
    ];

    public function bankSoal(): HasMany
    {
        return $this->hasMany(CbtBankSoalClean::class, 'mapel_id');
    }
}
