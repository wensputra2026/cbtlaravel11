<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * ID Group Level Pengguna di Garuda CBT (Ion Auth):
     * 1 = admin
     * 2 = guru
     * 3 = siswa
     */
    public const ROLE_ADMIN = 1;
    public const ROLE_GURU  = 2;
    public const ROLE_SISWA = 3;

    /**
     * Nama tabel akun login warisan Garuda CBT.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Nonaktifkan default timestamps created_at dan updated_at Laravel.
     * (Tabel menggunakan Unix timestamp `created_on` dan `last_login`).
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
     * Kolom rahasia yang disembunyikan dari serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_code',
        'remember_selector',
        'activation_code',
        'activation_selector',
        'forgotten_password_code',
        'forgotten_password_selector',
    ];

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active'     => 'integer',
            'created_on' => 'integer',
            'last_login' => 'integer',
        ];
    }

    /**
     * Relasi ke profil data siswa jika pengguna adalah siswa (dihubungkan via username).
     */
    public function siswa(): HasOne
    {
        return $this->hasOne(MasterSiswa::class, 'username', 'username');
    }

    /**
     * Relasi ke profil siswa skema bersih (dihubungkan via user_id).
     */
    public function profileSiswa(): HasOne
    {
        return $this->hasOne(Siswa::class, 'user_id', 'id');
    }

    /**
     * Relasi ke grup peran (Ion Auth groups) via tabel perantara `users_groups`.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            'users_groups',
            'user_id',
            'group_id'
        );
    }

    /**
     * Cek apakah pengguna aktif.
     */
    public function isActive(): bool
    {
        return (int) $this->active === 1;
    }

    /**
     * Cek apakah pengguna berperan sebagai Siswa (Peserta Ujian).
     */
    public function isSiswa(): bool
    {
        return $this->groups->contains('id', self::ROLE_SISWA) ||
               $this->groups->contains('name', 'siswa');
    }

    /**
     * Cek apakah pengguna berperan sebagai Guru.
     */
    public function isGuru(): bool
    {
        return $this->groups->contains('id', self::ROLE_GURU) ||
               $this->groups->contains('name', 'guru');
    }

    /**
     * Cek apakah pengguna berperan sebagai Admin.
     */
    public function isAdmin(): bool
    {
        return $this->groups->contains('id', self::ROLE_ADMIN) ||
               $this->groups->contains('name', 'admin');
    }

    /**
     * Mendapatkan nama tampilan pengguna.
     */
    public function getNamaLengkapAttribute(): string
    {
        if ($this->first_name === $this->last_name) {
            return $this->first_name ?? $this->username ?? '';
        }

        return trim("{$this->first_name} {$this->last_name}");
    }
}
