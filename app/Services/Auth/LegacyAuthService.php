<?php

namespace App\Services\Auth;

use App\Models\MasterSiswa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class LegacyAuthService
{
    /**
     * Durasi kunci perangkat (dalam detik) - default 4 jam pengerjaan ujian.
     */
    protected const DEVICE_LOCK_TTL = 14400;

    /**
     * Autentikasi pengguna menggunakan kredensial lama Garuda CBT (Bcrypt compatible).
     * Dilengkapi Single-Device Login Lock berbasis Redis.
     *
     * @param string $username
     * @param string $password
     * @param string|null $deviceFingerprint
     * @return array
     */
    public function attemptLogin(string $username, string $password, ?string $deviceFingerprint = null): array
    {
        $username = trim($username);

        // 1. Cari user di tabel `users`
        $user = User::query()->where('username', $username)->first();

        // 2. Jika tidak ditemukan di `users`, coba cari di `master_siswa` lalu sinkronkan
        if (!$user) {
            $siswa = MasterSiswa::query()->where('username', $username)->first();
            if ($siswa) {
                // Cari atau buatkan akun user otomatis jika siswa ada di master_siswa
                $user = User::query()->where('username', $siswa->username)->first();
                if (!$user) {
                    $user = User::create([
                        'username'   => $siswa->username,
                        'password'   => password_hash($siswa->password ?? $password, PASSWORD_BCRYPT),
                        'active'     => 1,
                        'first_name' => $siswa->nama,
                        'created_on' => time(),
                    ]);
                    $user->groups()->attach(User::ROLE_SISWA);
                }
            }
        }

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Username atau password salah.',
            ];
        }

        // 3. Verifikasi Password Bcrypt
        $passwordMatches = password_verify($password, $user->password);

        // Fallback untuk password legacy plain-text (jika ada data lama yang belum ter-hash)
        if (!$passwordMatches && $user->password === $password) {
            // Update langsung ke Bcrypt standar
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->save();
            $passwordMatches = true;
        }

        if (!$passwordMatches) {
            return [
                'success' => false,
                'message' => 'Username atau password salah.',
            ];
        }

        // 4. Verifikasi status akun aktif
        if (!$user->isActive()) {
            return [
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi proktor/administrator.',
            ];
        }

        // 5. Fitur Single-Device Login Lock (Mencegah 1 akun dipakai di 2 browser/komputer bersamaan)
        $deviceKey = "cbt_device_lock:{$user->id}";
        $currentDevice = $deviceFingerprint ?? request()->ip() . '_' . request()->userAgent();

        $activeDevice = null;
        try {
            $activeDevice = Redis::get($deviceKey);
        } catch (\Throwable $e) {
            // Redis fallback graceful jika koneksi Redis terganggu
        }

        if ($activeDevice && $activeDevice !== $currentDevice && $user->isSiswa()) {
            return [
                'success' => false,
                'message' => 'Akun sedang aktif di perangkat lain! Minta Proktor untuk melakukan Reset Login.',
                'locked'  => true,
            ];
        }

        // Kunci perangkat aktif di Redis
        try {
            Redis::setex($deviceKey, self::DEVICE_LOCK_TTL, $currentDevice);
        } catch (\Throwable $e) {
            // Fallback graceful
        }

        // 6. Login ke sesi Laravel
        Auth::login($user, true);

        // Update timestamp login terakhir
        $user->last_login = time();
        $user->ip_address = request()->ip();
        $user->save();

        return [
            'success' => true,
            'message' => 'Login berhasil.',
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'nama'     => $user->nama_lengkap,
                'role'     => $user->isSiswa() ? 'siswa' : ($user->isGuru() ? 'guru' : 'admin'),
            ],
        ];
    }

    /**
     * Logout pengguna dan hapus kunci single-device di Redis.
     */
    public function logout(?int $userId = null): void
    {
        $id = $userId ?? Auth::id();
        if ($id) {
            try {
                Redis::del("cbt_device_lock:{$id}");
                $user = User::find($id);
                if ($user) {
                    $siswa = MasterSiswa::where('username', $user->username)->first();
                    if ($siswa) {
                        Redis::del("cbt_device_lock:{$siswa->id_siswa}");
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Hapus kunci perangkat untuk seorang siswa (dilakukan oleh proktor).
     */
    public function forceUnlockDevice(int $identifier): bool
    {
        try {
            Redis::del("cbt_device_lock:{$identifier}");
            $siswa = MasterSiswa::find($identifier);
            if ($siswa && $siswa->user) {
                Redis::del("cbt_device_lock:{$siswa->user->id}");
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
