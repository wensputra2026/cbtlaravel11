<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MasterSmt extends Model
{
    protected $table = 'master_smt';
    protected $primaryKey = 'id_smt';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active' => 'integer',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('active', 1);
    }

    public static function activeSmt(): ?self
    {
        return static::where('active', 1)->first();
    }
}
