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
