<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtPengawas;
use App\Models\MasterGuru;
use App\Services\Exam\CbtTokenService;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruPengawasanController extends Controller
{
    public function __construct(
        protected ProctorService $proctorService,
        protected CbtTokenService $tokenService
    ) {}

    protected function getGuru()
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Daftar Penugasan Ruang Pengawasan Ujian.
     */
    public function index(): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;

        $pengawasans = CbtPengawas::with(['jadwal.bankSoal.mapel', 'ruang', 'sesi'])
            ->where('id_guru', $guruId)
            ->orderBy('id_pengawas', 'desc')
            ->get();

        $currentToken = $this->tokenService->getOrGenerateDynamicToken();
        $tokenTtl = $this->tokenService->getTokenRemainingSeconds();

        return view('guru.pengawasan.index', compact('pengawasans', 'currentToken', 'tokenTtl', 'guru'));
    }

    /**
     * Live Monitoring Ujian di Ruangan Guru Bersangkutan.
     */
    public function monitor(int $jadwalId): View
    {
        $guru = $this->getGuru();
        $initialData = $this->proctorService->getLiveMonitoring($jadwalId);
        $currentToken = $this->tokenService->getOrGenerateDynamicToken();

        return view('guru.pengawasan.monitor', compact('jadwalId', 'initialData', 'currentToken', 'guru'));
    }
}
