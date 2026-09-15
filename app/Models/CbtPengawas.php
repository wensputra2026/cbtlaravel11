<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtPengawas extends Model
{
    protected $table = 'cbt_pengawas';
    protected $primaryKey = 'id_pengawas';
    public $timestamps = false;
    protected $guarded = [];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(MasterGuru::class, 'id_guru', 'id_guru');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwal::class, 'id_jadwal', 'id_jadwal');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(CbtRuang::class, 'id_ruang', 'id_ruang');
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(CbtSesi::class, 'id_sesi', 'id_sesi');
    }
}
