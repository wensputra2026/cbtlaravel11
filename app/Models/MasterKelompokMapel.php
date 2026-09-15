<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKelompokMapel extends Model
{
    /**
     * Nama tabel kelompok mata pelajaran di Garuda CBT.
     *
     * @var string
     */
    protected $table = 'master_kelompok_mapel';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_kel_mapel';

    /**
     * Nonaktifkan timestamps.
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
     * Relasi ke kelompok utama (parent).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterKelompokMapel::class, 'id_parent', 'id_kel_mapel');
    }

    /**
     * Relasi ke sub kelompok (children).
     */
    public function children(): HasMany
    {
        return $this->hasMany(MasterKelompokMapel::class, 'id_parent', 'id_kel_mapel');
    }
}
