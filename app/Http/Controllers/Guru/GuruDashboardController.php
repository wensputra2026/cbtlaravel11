<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtPengawas;
use App\Models\MasterGuru;
use App\Services\Exam\CbtTokenService;
use App\Services\Teacher\TeacherScopeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruDashboardController extends Controller
{
    public function __construct(
        protected CbtTokenService $tokenService,
        protected TeacherScopeService $scopeService
    ) {}

    /**
     * Dashboard Utama Guru & Pengawas Ujian.
     */
    public function index(): View
    {
        $guru = $this->scopeService->getTeacherProfile();
        $assignment = $this->scopeService->getTeacherAssignment($guru);
        $guruId = $guru?->id_guru ?? 0;

        // Bank Soal yang dibuat oleh guru bersangkutan
        $myBanks = CbtBankSoal::with('mapel')
            ->where('bank_guru_id', $guruId)
            ->get();

        // Jadwal pengawasan yang ditugaskan kepada guru
        $myPengawasan = CbtPengawas::with(['jadwal.bankSoal.mapel', 'ruang', 'sesi'])
            ->where('id_guru', $guruId)
            ->get();

        // Token Ujian Terkini
        $currentToken = $this->tokenService->getOrGenerateDynamicToken();
        $tokenTtl = $this->tokenService->getTokenRemainingSeconds();

        return view('guru.dashboard', compact(
            'guru',
            'assignment',
            'myBanks',
            'myPengawasan',
            'currentToken',
            'tokenTtl'
        ));
    }
}
