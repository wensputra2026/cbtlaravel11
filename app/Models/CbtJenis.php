<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtJenis extends Model
{
    protected $table = 'cbt_jenis';
    protected $primaryKey = 'id_jenis';
    public $timestamps = false;
    protected $guarded = [];
}
