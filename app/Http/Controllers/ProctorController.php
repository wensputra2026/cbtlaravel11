<?php

namespace App\Http\Controllers;

use App\Models\CbtJadwal;
use App\Services\Exam\ExamTokenService;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProctorController extends Controller
{
    public function __construct(
        protected ProctorService $proctorService,
        protected ExamTokenService $tokenService
    ) {}

    /**
     * Halaman daftar jadwal ujian yang dapat dipantau oleh proktor.
     */
    public function index(): View
    {
        $jadwals = CbtJadwal::with(['bankSoal.mapel'])
            ->orderBy('id_jadwal', 'desc')
            ->get();

        $currentToken = $this->tokenService->getActiveToken();
        $tokenTtl = $this->tokenService->getTokenTtl();

        return view('proctor.index', [
            'jadwals'      => $jadwals,
            'currentToken' => $currentToken,
            'tokenTtl'     => $tokenTtl,
        ]);
    }

    /**
     * Tampilan Live Monitoring ujian aktif per jadwal.
     */
    public function monitor(int $jadwalId): View
    {
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);
        $initialData = $this->proctorService->getLiveMonitoring($jadwalId);

        return view('proctor.monitor', [
            'jadwal'      => $jadwal,
            'jadwalId'    => $jadwalId,
            'initialData' => $initialData,
        ]);
    }

    /**
     * API: Polling status real-time peserta ujian (< 15ms).
     */
    public function apiLiveStatus(int $jadwalId): JsonResponse
    {
        $data = $this->proctorService->getLiveMonitoring($jadwalId);
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * API: Reset login siswa (membuka kunci single-device di Redis).
     */
    public function apiResetLogin(Request $request, int $jadwalId, int $siswaId): JsonResponse
    {
        $success = $this->proctorService->resetStudentLogin($jadwalId, $siswaId);
        return response()->json([
            'success' => $success,
            'message' => 'Kunci login perangkat siswa berhasil direset.',
        ]);
    }

    /**
     * API: Paksa selesai ujian (Force Submit).
     */
    public function apiForceSubmit(Request $request, int $jadwalId, int $siswaId): JsonResponse
    {
        try {
            $result = $this->proctorService->forceSubmit($jadwalId, $siswaId);
            return response()->json([
                'success' => true,
                'message' => 'Ujian siswa berhasil diselesaikan secara paksa.',
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan ujian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Menambah waktu khusus untuk peserta tertentu.
     */
    public function apiAddExtraTime(Request $request, int $jadwalId, int $siswaId): JsonResponse
    {
        $minutes = (int) $request->input('minutes', 10);
        $newTotal = $this->proctorService->addExtraTime($jadwalId, $siswaId, $minutes);

        return response()->json([
            'success'       => true,
            'message'       => "Berhasil menambahkan waktu {$minutes} menit.",
            'total_extra'   => $newTotal,
        ]);
    }

    /**
     * API: Generate token ujian baru 6-karakter.
     */
    public function apiGenerateToken(Request $request): JsonResponse
    {
        $validMinutes = (int) $request->input('minutes', 15);
        $newToken = $this->tokenService->generateToken($validMinutes);

        return response()->json([
            'success' => true,
            'token'   => $newToken,
            'ttl'     => $validMinutes * 60,
            'message' => "Token baru berhasil dibuat: {$newToken}",
        ]);
    }

    /**
     * API: Mendapatkan token ujian aktif beserta sisa detik TTL.
     */
    public function apiGetToken(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'token'   => $this->tokenService->getActiveToken(),
            'ttl'     => $this->tokenService->getTokenTtl(),
        ]);
    }

    /**
     * API Actions with JSON request bodies
     */
    public function actionResetLogin(Request $request): JsonResponse
    {
        $jadwalId = (int) ($request->input('id_jadwal') ?? $request->input('jadwal_id'));
        $siswaId = (int) ($request->input('id_siswa') ?? $request->input('siswa_id'));
        return $this->apiResetLogin($request, $jadwalId, $siswaId);
    }

    public function actionExtendTime(Request $request): JsonResponse
    {
        $jadwalId = (int) ($request->input('id_jadwal') ?? $request->input('jadwal_id'));
        $siswaId = (int) ($request->input('id_siswa') ?? $request->input('siswa_id'));
        return $this->apiAddExtraTime($request, $jadwalId, $siswaId);
    }

    public function actionForceSubmit(Request $request): JsonResponse
    {
        $jadwalId = (int) ($request->input('id_jadwal') ?? $request->input('jadwal_id'));
        $siswaId = (int) ($request->input('id_siswa') ?? $request->input('siswa_id'));
        return $this->apiForceSubmit($request, $jadwalId, $siswaId);
    }
}
