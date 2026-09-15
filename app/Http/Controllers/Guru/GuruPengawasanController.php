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
    public function index(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;
        $search = $request->input('q');

        $query = CbtPengawas::with(['jadwal.bankSoal.mapel', 'ruang', 'sesi'])
            ->where('id_guru', $guruId)
            ->orderBy('id_pengawas', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('ruang', function ($rq) use ($search) {
                    $rq->where('nama_ruang', 'like', "%{$search}%");
                })->orWhereHas('sesi', function ($sq) use ($search) {
                    $sq->where('nama_sesi', 'like', "%{$search}%");
                })->orWhereHas('jadwal.bankSoal', function ($bq) use ($search) {
                    $bq->where('bank_nama', 'like', "%{$search}%")
                       ->orWhereHas('mapel', function ($mq) use ($search) {
                           $mq->where('nama_mapel', 'like', "%{$search}%");
                       });
                });
            });
        }

        $pengawasans = $query->paginate(10)->withQueryString();

        $currentToken = $this->tokenService->getOrGenerateDynamicToken();
        $tokenTtl = $this->tokenService->getTokenRemainingSeconds();

        return view('guru.pengawasan.index', compact('pengawasans', 'currentToken', 'tokenTtl', 'guru', 'search'));
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
