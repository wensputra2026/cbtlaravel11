<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MasterGuru;
use App\Services\Exam\CbtTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruTokenController extends Controller
{
    public function __construct(
        protected CbtTokenService $tokenService
    ) {}

    protected function getGuru(): ?MasterGuru
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Tampilan token ujian untuk guru & pengawas ruang.
     */
    public function index(): View
    {
        $guru = $this->getGuru();
        $currentToken = $this->tokenService->getOrGenerateDynamicToken();
        $tokenTtl = $this->tokenService->getTokenRemainingSeconds();

        return view('guru.token.index', compact('guru', 'currentToken', 'tokenTtl'));
    }

    /**
     * API Polling Token untuk guru.
     */
    public function apiGetToken(): JsonResponse
    {
        return response()->json([
            'token' => $this->tokenService->getOrGenerateDynamicToken(),
            'ttl'   => $this->tokenService->getTokenRemainingSeconds(),
        ]);
    }
}
