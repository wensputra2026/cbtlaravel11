<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterKelompokMapel extends Model
{
    protected $table = 'master_kelompok_mapel';
    protected $primaryKey = 'id_kel_mapel';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Parent kelompok utama jika merupakan sub kelompok
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterKelompokMapel::class, 'id_parent', 'id_kel_mapel');
    }

    /**
     * Sub kelompok di bawah kelompok utama
     */
    public function children(): HasMany
    {
        return $this->hasMany(MasterKelompokMapel::class, 'id_parent', 'id_kel_mapel');
    }
}
