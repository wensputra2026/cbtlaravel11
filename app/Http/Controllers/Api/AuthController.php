<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CbtSesiSiswa;
use App\Models\MasterSiswa;
use App\Models\User;
use App\Services\Exam\ExamRedisBuffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected const DEVICE_LOCK_TTL = 28800; // 8 Jam

    /**
     * Endpoint Login Siswa / Pengguna API.
     * Menerima username & password, mencocokkan hash Bcrypt legacy Garuda CBT,
     * memeriksa Single-Device Lock di Redis, dan mengembalikan identitas lengkap siswa.
     *
     * Route: POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');

        // 1. Cari user di tabel `users`
        $user = User::where('username', $username)->first();

        // Jika tidak ditemukan di `users`, coba periksa di `master_siswa`
        if (!$user) {
            $siswa = MasterSiswa::where('username', $username)->first();
            if ($siswa) {
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.',
            ], 401);
        }

        // 2. Verifikasi Password Bcrypt (Kompatibel Ion Auth Garuda CBT)
        $passwordValid = password_verify($password, $user->password);

        // Fallback untuk akun lama yang belum ter-hash (plain text)
        if (!$passwordValid && $user->password === $password) {
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->save();
            $passwordValid = true;
        }

        if (!$passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.',
            ], 401);
        }

        // 3. Verifikasi Status Akun Aktif
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dinonaktifkan. Silakan hubungi proktor.',
            ], 403);
        }

        // 4. Identifikasi Profil Siswa (Nama, NIS, NISN, Kelas, Sesi, Ruang)
        $siswa = MasterSiswa::with(['kelasSiswa.kelas'])->where('username', $user->username)->first();
        $siswaData = null;
        $deviceLockKey = null;

        if ($siswa) {
            $siswaId = $siswa->id_siswa;
            $deviceLockKey = "cbt_device_lock:{$siswaId}";

            // Generate Device Token unik untuk sesi ini
            $deviceToken = $request->header('X-Device-Token')
                ?? $request->input('device_token')
                ?? ('dev_' . Str::random(24) . '_' . $siswaId);

            // Cek Single-Device Lock di Redis
            $activeDevice = ExamRedisBuffer::get($deviceLockKey);

            // Jika ada perangkat lain yang sedang aktif dan belum direset oleh proktor
            if ($activeDevice && $activeDevice !== $deviceToken) {
                return response()->json([
                    'success' => false,
                    'locked'  => true,
                    'message' => 'Akun Anda sedang aktif di perangkat lain. Minta pengawas untuk mereset login Anda jika komputer Anda mengalami kendala.',
                ], 403);
            }

            // Daftarkan kunci perangkat ke Redis buffer
            ExamRedisBuffer::set($deviceLockKey, $deviceToken, self::DEVICE_LOCK_TTL);

            // Ambil info alokasi sesi & ruang dari `cbt_sesi_siswa`
            $sesiSiswa = CbtSesiSiswa::with(['sesi', 'ruang'])->find($siswaId);

            $siswaData = [
                'id_siswa'    => $siswaId,
                'nama'        => $siswa->nama,
                'nis'         => $siswa->nis,
                'nisn'        => $siswa->nisn,
                'kelas'       => $siswa->kelasSiswa->first()->kelas->nama_kelas ?? '-',
                'sesi'        => $sesiSiswa?->sesi?->nama_sesi ?? 'Sesi 1',
                'ruang'       => $sesiSiswa?->ruang?->nama_ruang ?? 'Ruang 01',
                'device_token'=> $deviceToken,
            ];
        }

        // 5. Generate Auth Token untuk State Persisten (Redis Token + Laravel Auth)
        $authToken = Str::random(40);
        ExamRedisBuffer::set("cbt_auth_user:{$authToken}", (string) $user->id, self::DEVICE_LOCK_TTL);

        Auth::login($user, true);

        // Update waktu login terakhir
        $user->last_login = time();
        $user->ip_address = $request->ip();
        $user->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Login berhasil',
            'token'        => $authToken,
            'device_token' => $siswaData['device_token'] ?? null,
            'user'         => [
                'id'       => $user->id,
                'username' => $user->username,
                'role'     => $user->isSiswa() ? 'siswa' : ($user->isGuru() ? 'guru' : 'admin'),
            ],
            'siswa'        => $siswaData,
        ]);
    }

    /**
     * Endpoint Logout:
     * Menghapus sesi aktif dan melepaskan lock perangkat di Redis.
     *
     * Route: POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            $siswa = MasterSiswa::where('username', $user->username)->first();
            if ($siswa) {
                // Hapus kunci perangkat di Redis buffer
                ExamRedisBuffer::del("cbt_device_lock:{$siswa->id_siswa}");
            }

            // Hapus auth token
            $bearer = $request->bearerToken();
            if ($bearer) {
                ExamRedisBuffer::del("cbt_auth_user:{$bearer}");
            }

            Auth::logout();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil dan kunci perangkat telah dilepaskan.',
        ]);
    }
}
