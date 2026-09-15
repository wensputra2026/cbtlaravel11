<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MasterTp extends Model
{
    protected $table = 'master_tp';
    protected $primaryKey = 'id_tp';
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

    public static function activeTp(): ?self
    {
        return static::where('active', 1)->first();
    }
}
