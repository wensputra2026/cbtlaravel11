<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id_setting';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Singleton instance helper untuk mendapatkan pengaturan saat ini.
     */
    public static function current(): self
    {
        return static::first() ?? new static([
            'sekolah'       => 'SMA Negeri Benlutu',
            'nama_aplikasi' => 'SMANBEN-CBT',
            'npsn'          => '50309196',
        ]);
    }

    protected function resolveImagePath(?string $filename, string $defaultRelativePath): string
    {
        $filename = trim((string)$filename);
        if ($filename) {
            // Check direct path
            if (file_exists(public_path($filename))) {
                return asset($filename);
            }
            // Check in uploads/settings/
            if (file_exists(public_path('uploads/settings/' . $filename))) {
                return asset('uploads/settings/' . $filename);
            }
            // Check in assets/img/
            if (file_exists(public_path('assets/img/' . $filename))) {
                return asset('assets/img/' . $filename);
            }
        }
        // Fallback to default relative path
        if (file_exists(public_path($defaultRelativePath))) {
            return asset($defaultRelativePath);
        }
        return asset('assets/img/garuda_circle.png');
    }

    public function getLogoKiriUrlAttribute(): string
    {
        return $this->resolveImagePath($this->logo_kiri, 'uploads/settings/logo_kiri.png');
    }

    public function getFaviconUrlAttribute(): string
    {
        $url = $this->getLogoKiriUrlAttribute();
        $ver = file_exists(public_path('uploads/settings/logo_kiri.png')) 
            ? filemtime(public_path('uploads/settings/logo_kiri.png')) 
            : time();
        return $url . '?v=' . $ver;
    }

    public function getLogoKananUrlAttribute(): string
    {
        return $this->resolveImagePath($this->logo_kanan, 'uploads/settings/logo_kanan.png');
    }

    public function getBackgroundUrlAttribute(): string
    {
        return $this->resolveImagePath($this->background, 'uploads/settings/background.jpg');
    }

    public function getTandatanganUrlAttribute(): string
    {
        return $this->resolveImagePath($this->tandatangan, 'uploads/settings/tandatangan.png');
    }

    public function getNamaAplikasiTampilAttribute(): string
    {
        return !empty($this->nama_aplikasi) ? $this->nama_aplikasi : config('app.name', 'SMANBEN-CBT');
    }

    public function getNamaSekolahTampilAttribute(): string
    {
        return !empty($this->sekolah) ? $this->sekolah : 'SMA Negeri Benlutu';
    }

    public function getAlamatLengkapAttribute(): string
    {
        $parts = array_filter([
            $this->alamat,
            $this->desa ? 'Ds. ' . $this->desa : null,
            $this->kecamatan ? 'Kec. ' . $this->kecamatan : null,
            $this->kota,
            $this->provinsi,
        ]);
        return !empty($parts) ? implode(', ', $parts) : 'Jl. Pendidikan, Benlutu';
    }

    public function getKepsekTampilAttribute(): string
    {
        return !empty($this->kepsek) ? $this->kepsek : 'Kepala Sekolah';
    }

    public function getNipKepsekTampilAttribute(): string
    {
        return !empty($this->nip) ? 'NIP. ' . $this->nip : 'NIP. -';
    }
}
