<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtBankSoalClean extends Model
{
    protected $table = 'cbt_bank_soal_clean';

    protected $fillable = [
        'kode_bank',
        'mapel_id',
        'guru_id',
        'nama_bank',
        'tingkat',
        'alokasi_kelas',
        'jml_pg',
        'jml_kompleks',
        'jml_jodoh',
        'jml_isian',
        'jml_esai',
        'bobot_pg',
        'bobot_kompleks',
        'bobot_jodoh',
        'bobot_isian',
        'bobot_esai',
        'kkm',
        'status',
    ];

    protected $casts = [
        'alokasi_kelas' => 'array',
        'bobot_pg' => 'float',
        'bobot_kompleks' => 'float',
        'bobot_jodoh' => 'float',
        'bobot_isian' => 'float',
        'bobot_esai' => 'float',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(RefMapel::class, 'mapel_id');
    }

    public function soal(): HasMany
    {
        return $this->hasMany(CbtSoalClean::class, 'bank_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(CbtJadwalClean::class, 'bank_id');
    }
}
