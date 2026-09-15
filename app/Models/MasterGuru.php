<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterGuru extends Model
{
    protected $table = 'master_guru';
    protected $primaryKey = 'id_guru';
    public $timestamps = false;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function jabatan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        $activeTp = MasterTp::where('active', 1)->value('id_tp') ?? 1;
        $activeSmt = MasterSmt::where('active', 1)->value('id_smt') ?? 1;

        return $this->hasOne(JabatanGuru::class, 'id_guru', 'id_guru')
                    ->where('id_tp', $activeTp)
                    ->where('id_smt', $activeSmt);
    }

    public function allJabatan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JabatanGuru::class, 'id_guru', 'id_guru');
    }

    public function getFotoUrlAttribute(): string
    {
        $foto = trim((string)$this->foto);
        if ($foto) {
            if (file_exists(public_path('uploads/profiles/' . $foto))) {
                return asset('uploads/profiles/' . $foto);
            }
            if (file_exists(public_path($foto))) {
                return asset($foto);
            }
        }
        return asset('assets/img/guru.png');
    }
}
