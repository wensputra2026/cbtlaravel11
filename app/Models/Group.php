<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    /**
     * Nama tabel roles/groups warisan Ion Auth CodeIgniter.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Nonaktifkan default timestamps.
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
     * Relasi ke pengguna yang tergabung dalam grup ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_groups', 'group_id', 'user_id');
    }
}
