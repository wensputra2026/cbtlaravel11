<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CbtDurasiSiswa;
use App\Models\CbtUjianSiswa;
use App\Models\MasterSiswa;
use App\Services\Exam\CbtTokenService;
use App\Services\Exam\ExamRedisBuffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProctorActionController extends Controller
{
    public function __construct(
        protected CbtTokenService $tokenService
    ) {}

    /**
     * Endpoint Reset Login Siswa:
     * Menghapus key `cbt_device_lock:{siswa_id}` di Redis dan memperbarui status
     * `reset = 1` di tabel `cbt_durasi_siswa`. Siswa dapat langsung login kembali
     * di PC pengganti tanpa kehilangan sisa waktu atau jawaban yang telah disimpan.
     *
     * Route: POST /api/proctor/reset-login
     */
    public function resetLogin(Request $request): JsonResponse
    {
        $request->validate([
            'siswa_id'  => 'required|integer',
            'jadwal_id' => 'required|integer',
        ]);

        $siswaId  = (int) $request->input('siswa_id');
        $jadwalId = (int) $request->input('jadwal_id');

        $siswa = MasterSiswa::find($siswaId) ?? \App\Models\Siswa::find($siswaId);
        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        // 1. Hapus key Single-Device Lock di Redis Buffer secara Atomic
        ExamRedisBuffer::del("cbt_device_lock:{$siswaId}");

        // Bersihkan juga violation counter jika proctor ingin memberikan kesempatan ulang
        if ($request->boolean('clear_violations', false)) {
            ExamRedisBuffer::del("cbt_violation:{$jadwalId}:{$siswaId}");
            ExamRedisBuffer::del("cbt_violation_log:{$jadwalId}:{$siswaId}");
            ExamRedisBuffer::del("cbt_exam_terminated:{$jadwalId}:{$siswaId}");
            try {
                CbtUjianSiswa::where('jadwal_id', $jadwalId)->where('siswa_id', $siswaId)->update(['pelanggaran_count' => 0]);
            } catch (\Throwable $e) {}
        }

        // 2. Tandai reset pada tabel legacy cbt_durasi_siswa jika ada
        if (Schema::hasTable('cbt_durasi_siswa')) {
            $durasiId = CbtDurasiSiswa::generateId($siswaId, $jadwalId);
            $durasi = CbtDurasiSiswa::find($durasiId);

            if ($durasi) {
                $durasi->reset = 1;
                if ($durasi->status === CbtDurasiSiswa::STATUS_BELUM) {
                    $durasi->status = CbtDurasiSiswa::STATUS_SEDANG;
                }
                $durasi->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Kunci perangkat untuk siswa {$siswa->nama} berhasil direset. Siswa dapat login kembali di komputer lain.",
            'siswa'   => [
                'id_siswa' => $siswaId,
                'nama'     => $siswa->nama,
                'reset'    => 1,
            ],
        ]);
    }

    /**
     * Endpoint Generate Token Ujian Dinamis:
     *
     * Route: POST /api/proctor/token/generate
     */
    public function generateToken(Request $request): JsonResponse
    {
        $request->validate([
            'jadwal_id' => 'required|integer',
            'minutes'   => 'nullable|integer|min:5|max:180',
        ]);

        $jadwalId = (int) $request->input('jadwal_id');
        $minutes  = (int) $request->input('minutes', 15);

        $token = $this->tokenService->generateToken($jadwalId, $minutes);
        $ttl   = $this->tokenService->getTokenTtl($jadwalId);

        return response()->json([
            'success' => true,
            'message' => "Token baru untuk jadwal #{$jadwalId} berhasil di-generate.",
            'token'   => $token,
            'ttl'     => $ttl,
        ]);
    }

    /**
     * Endpoint Membaca Token Aktif & Sisa TTL:
     *
     * Route: GET /api/proctor/token/{jadwalId}
     */
    public function getToken(int $jadwalId): JsonResponse
    {
        $token = $this->tokenService->getActiveToken($jadwalId);
        $ttl   = $this->tokenService->getTokenTtl($jadwalId);

        return response()->json([
            'success' => true,
            'token'   => $token,
            'ttl'     => $ttl,
        ]);
    }
}
