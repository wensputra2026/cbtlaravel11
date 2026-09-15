<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtToken extends Model
{
    /**
     * Nama tabel token ujian CBT.
     *
     * @var string
     */
    protected $table = 'cbt_token';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_token';

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
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto'  => 'integer',
            'jarak' => 'integer',
        ];
    }

    /**
     * Ambil token aktif saat ini.
     */
    public static function getActiveToken(): ?string
    {
        return static::query()->latest('updated')->value('token')
            ?? static::query()->value('token');
    }
}
