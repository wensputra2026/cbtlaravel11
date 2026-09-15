<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtSoalClean extends Model
{
    protected $table = 'cbt_soal_clean';

    protected $fillable = [
        'bank_id',
        'nomor_urut',
        'jenis_soal',
        'pertanyaan',
        'opsi',
        'kunci_jawaban',
        'media',
        'bobot',
    ];

    protected $casts = [
        'opsi' => 'array',
        'kunci_jawaban' => 'array',
        'bobot' => 'float',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(CbtBankSoalClean::class, 'bank_id');
    }
}
