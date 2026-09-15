<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterEkstra extends Model
{
    protected $table = 'master_ekstra';
    protected $primaryKey = 'id_ekstra';
    public $timestamps = false;
    protected $guarded = [];
}
