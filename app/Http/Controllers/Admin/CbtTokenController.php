<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtToken;
use App\Services\Exam\CbtTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CbtTokenController extends Controller
{
    public function __construct(
        protected CbtTokenService $tokenService
    ) {}

    /**
     * Halaman Pengelolaan Token Ujian.
     */
    public function index(): View
    {
        $currentToken = $this->tokenService->getOrGenerateDynamicToken();
        $tokenTtl = $this->tokenService->getTokenRemainingSeconds();

        // History / Log token terakhir
        $history = CbtToken::orderBy('updated', 'desc')->take(10)->get();

        return view('admin.cbt.token', compact('currentToken', 'tokenTtl', 'history'));
    }

    /**
     * Rilis / Generate Token Baru Secara Manual.
     */
    public function generate(Request $request): RedirectResponse|JsonResponse
    {
        $newToken = $this->tokenService->forceGenerateNewToken();
        $tokenTtl = $this->tokenService->getTokenRemainingSeconds();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'token'   => $newToken,
                'ttl'     => $tokenTtl,
                'message' => 'Token ujian baru berhasil dirilis.',
            ]);
        }

        return back()->with('success', "Token baru [{$newToken}] berhasil dirilis dan disinkronkan ke seluruh pengawas.");
    }

    /**
     * API Real-time Polling Token saat ini.
     */
    public function apiGetToken(): JsonResponse
    {
        return response()->json([
            'token' => $this->tokenService->getOrGenerateDynamicToken(),
            'ttl'   => $this->tokenService->getTokenRemainingSeconds(),
        ]);
    }
}
