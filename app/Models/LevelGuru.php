<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelGuru extends Model
{
    protected $table = 'level_guru';
    protected $primaryKey = 'id_level';
    public $timestamps = false;
    protected $guarded = [];
}
