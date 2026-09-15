<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtNomorPeserta extends Model
{
    protected $table = 'cbt_nomor_peserta';
    protected $primaryKey = 'id_nomor';
    public $timestamps = false;
    protected $guarded = [];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(MasterSiswa::class, 'id_siswa', 'id_siswa');
    }
}
