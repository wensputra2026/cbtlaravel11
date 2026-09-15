<?php

namespace App\Http\Middleware;

use App\Models\MasterSiswa;
use App\Models\User;
use App\Services\Exam\ExamRedisBuffer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleExamDevice
{
    /**
     * Durasi TTL kunci perangkat di Redis (detik) - default 8 jam.
     */
    protected const LOCK_TTL = 28800;

    /**
     * Memastikan siswa hanya dapat mengakses ujian dari satu perangkat aktif.
     * Mencegah pembukaan sesi ganda atau pengerjaan joki di komputer lain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Dukungan token autentikasi API via Header Authorization Bearer atau X-Auth-Token
        if (!$user) {
            $bearer = $request->bearerToken() ?? $request->header('X-Auth-Token');
            if ($bearer) {
                $userId = ExamRedisBuffer::get("cbt_auth_user:{$bearer}");
                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        Auth::setUser($user);
                    }
                }
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan untuk mengakses layanan ujian.',
            ], 401);
        }

        // Pengawas / Guru / Admin dikecualikan dari device locking
        if (!$user->isSiswa()) {
            return $next($request);
        }

        // Ambil profil data siswa (prioritaskan skema bersih Siswa, fallback ke MasterSiswa)
        $siswa = \App\Models\Siswa::with(['kelas', 'sesi', 'ruang'])->where('user_id', $user->id)->first()
            ?? MasterSiswa::with('kelasSiswa')->where('username', $user->username)->first()
            ?? $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Profil siswa tidak ditemukan.',
            ], 404);
        }

        $siswaId = (int) ($siswa->id ?? $siswa->id_siswa);
        $redisKey = "cbt_device_lock:{$siswaId}";

        // Tentukan identifier unik perangkat dari request:
        // Prioritas: Header X-Device-Token -> Bearer Token -> Input device_token -> Session ID
        $clientDevice = $request->header('X-Device-Token')
            ?? $request->bearerToken()
            ?? $request->input('device_token')
            ?? session()->getId();

        $activeDevice = ExamRedisBuffer::get($redisKey);

        // 1. Jika ada device lock aktif dan TIDAK COCOK dengan perangkat sekarang
        if ($activeDevice && $activeDevice !== $clientDevice) {
            return response()->json([
                'success' => false,
                'locked'  => true,
                'message' => 'Akun Anda sedang aktif di perangkat lain. Minta proktor/pengawas untuk mereset login Anda jika komputer Anda mengalami kendala.',
            ], 403);
        }

        // 2. Jika belum ada lock, daftarkan perangkat ini secara atomic di Redis
        if (!$activeDevice && $clientDevice) {
            ExamRedisBuffer::set($redisKey, $clientDevice, self::LOCK_TTL);
        }

        // Sisipkan data siswa dan info device ke request context
        $request->attributes->set('auth_siswa', $siswa);
        $request->attributes->set('device_token', $clientDevice);

        return $next($request);
    }
}
