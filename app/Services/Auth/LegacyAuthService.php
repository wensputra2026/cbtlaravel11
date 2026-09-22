<?php

namespace App\Services\Auth;

use App\Models\MasterSiswa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class LegacyAuthService
{
    /**
     * Durasi kunci perangkat (dalam detik) - default 4 jam pengerjaan ujian.
     */
    protected const DEVICE_LOCK_TTL = 14400;

    /**
     * Autentikasi pengguna menggunakan kredensial Garuda CBT.
     * Mengimplementasikan fitur error login attempts (3x limit), pengecekan status akun,
     * audit logging ke tabel `log`, dan session kompatibel Garuda CBT.
     *
     * @param string $username
     * @param string $password
     * @param string|null $deviceFingerprint
     * @return array
     */
    public function attemptLogin(string $username, string $password, ?string $deviceFingerprint = null): array
    {
        $username = trim($username);
        $ip = request()->ip() ?? '127.0.0.1';

        // 1. Cek Percobaan Login (Rate Limiting via tabel `login_attempts` bawaan Garuda CBT)
        try {
            if (Schema::hasTable('login_attempts')) {
                $attempts = DB::table('login_attempts')->where('login', $username)->get();
                if ($attempts->count() >= 3) {
                    $lastAttempt = $attempts->last();
                    $timeDifference = time() - ($lastAttempt->time ?? 0);
                    if ($timeDifference < 300) {
                        return [
                            'success' => false,
                            'message' => 'Anda sudah 3x melakukan percobaan login. Silakan tunggu 5 menit atau hubungi Administrator.',
                            'role'    => '0',
                            'user'    => null,
                        ];
                    } else {
                        DB::table('login_attempts')->where('login', $username)->delete();
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 2. Cari data pengguna di tabel `users`
        $user = User::query()->where('username', $username)->first();

        // Jika tidak ditemukan di `users`, cari di `master_siswa` (sinkronisasi otomatis jika baru)
        if (!$user) {
            $siswa = MasterSiswa::query()->where('username', $username)->first();
            if ($siswa) {
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

        // Jika akun sama sekali tidak ditemukan
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Akun tidak terdaftar!',
                'role'    => '0',
                'user'    => null,
            ];
        }

        // 3. Pengecekan apakah akun aktif
        if (!$user->isActive()) {
            return [
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan!',
                'role'    => '0',
                'user'    => null,
            ];
        }

        // Pengecekan status siswa di buku_induk jika akun adalah siswa
        if ($user->isSiswa() && Schema::hasTable('buku_induk') && Schema::hasTable('master_siswa')) {
            $siswaData = DB::table('master_siswa as a')
                ->select('a.id_siswa', 'b.status')
                ->leftJoin('buku_induk as b', 'a.id_siswa', '=', 'b.id_siswa')
                ->where('a.username', $username)
                ->first();

            if ($siswaData && isset($siswaData->status) && $siswaData->status !== null && (string)$siswaData->status !== '1') {
                return [
                    'success' => false,
                    'message' => 'Akun Anda dinonaktifkan!',
                    'role'    => '0',
                    'user'    => null,
                ];
            }
        }

        // 4. Verifikasi Password (Bcrypt dengan fallback teks polos legacy)
        $passwordMatches = password_verify($password, $user->password);
        if (!$passwordMatches && $user->password === $password) {
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->save();
            $passwordMatches = true;
        }

        if (!$passwordMatches) {
            // Catat kesalahan login ke tabel `login_attempts`
            try {
                if (Schema::hasTable('login_attempts')) {
                    DB::table('login_attempts')->insert([
                        'ip_address' => $ip,
                        'login'      => $username,
                        'time'       => time(),
                    ]);
                }
            } catch (\Throwable $e) {}

            return [
                'success' => false,
                'message' => 'Username atau Password salah!',
                'role'    => '0',
                'user'    => null,
            ];
        }

        // 5. Fitur Single-Device Login Lock (Mencegah 1 akun aktif di banyak perangkat bersamaan)
        $deviceKey = "cbt_device_lock:{$user->id}";
        $currentDevice = $deviceFingerprint ?? ($ip . '_' . substr(md5(request()->userAgent() ?? ''), 0, 10));

        $activeDevice = null;
        try {
            $activeDevice = Redis::get($deviceKey);
        } catch (\Throwable $e) {}

        if ($activeDevice && $activeDevice !== $currentDevice && $user->isSiswa()) {
            return [
                'success' => false,
                'message' => 'Akun sedang aktif di perangkat lain! Minta Proktor untuk melakukan Reset Login.',
                'locked'  => true,
                'role'    => '3',
                'user'    => null,
            ];
        }

        // Kunci perangkat aktif di Redis
        try {
            Redis::setex($deviceKey, self::DEVICE_LOCK_TTL, $currentDevice);
        } catch (\Throwable $e) {}

        // Reset error login attempts setelah berhasil login
        try {
            if (Schema::hasTable('login_attempts')) {
                DB::table('login_attempts')->where('login', $username)->delete();
            }
        } catch (\Throwable $e) {}

        // 6. Login ke sesi Laravel & Set Data Sesi Kompatibilitas Garuda CBT
        Auth::login($user, true);

        $primaryGroup = $user->groups->first();
        $roleId = $user->isSiswa() ? 3 : ($user->isGuru() ? 2 : 1);
        $roleName = $user->isSiswa() ? 'siswa' : ($user->isGuru() ? 'guru' : 'admin');

        session([
            'user_id'   => $user->id,
            'username'  => $user->username,
            'role'      => (string) $roleId,
            'role_name' => $roleName,
            'is_login'  => true,
        ]);

        // 7. Audit Logging ke Tabel `log` (log_type = 1: Login)
        try {
            if (Schema::hasTable('log')) {
                DB::table('log')->insert([
                    'id_user'    => $user->id,
                    'id_group'   => $roleId,
                    'name_group' => $roleName,
                    'log_type'   => 1, // 1 = Login
                    'log_desc'   => 'Login',
                    'address'    => $ip,
                    'agent'      => request()->userAgent() ?? 'Browser',
                    'device'     => PHP_OS,
                ]);
            }
        } catch (\Throwable $e) {}

        // Update timestamp login terakhir pada user
        $user->last_login = time();
        $user->ip_address = $ip;
        $user->save();

        return [
            'success' => true,
            'message' => 'Login berhasil',
            'role_id' => (string) $roleId,
            'role'    => (string) $roleId,
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'nama'     => $user->nama_lengkap,
                'role'     => $roleName,
            ],
        ];
    }

    /**
     * Logout pengguna, hapus kunci single-device, dan simpan log audit.
     */
    public function logout(?int $userId = null): void
    {
        $user = Auth::user();
        $id = $userId ?? $user?->id;

        if ($id) {
            try {
                Redis::del("cbt_device_lock:{$id}");
                $u = $user ?? User::find($id);
                if ($u) {
                    $siswa = MasterSiswa::where('username', $u->username)->first();
                    if ($siswa) {
                        Redis::del("cbt_device_lock:{$siswa->id_siswa}");
                    }

                    // Audit Logging ke Tabel `log` (log_type = 2: Logout)
                    if (Schema::hasTable('log')) {
                        $roleId = $u->isSiswa() ? 3 : ($u->isGuru() ? 2 : 1);
                        $roleName = $u->isSiswa() ? 'siswa' : ($u->isGuru() ? 'guru' : 'admin');

                        DB::table('log')->insert([
                            'id_user'    => $u->id,
                            'id_group'   => $roleId,
                            'name_group' => $roleName,
                            'log_type'   => 2, // 2 = Logout
                            'log_desc'   => 'Logout',
                            'address'    => request()->ip() ?? '127.0.0.1',
                            'agent'      => request()->userAgent() ?? 'Browser',
                            'device'     => PHP_OS,
                        ]);
                    }
                }
            } catch (\Throwable $e) {}
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
