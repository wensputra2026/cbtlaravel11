<?php

namespace App\Http\Controllers;

use App\Services\Auth\LegacyAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected LegacyAuthService $authService
    ) {}

    /**
     * Tampilkan form login Garuda CBT.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isGuru()) {
                return redirect()->route('guru.dashboard');
            }
            return redirect()->route('exam.index');
        }

        return view('auth.login');
    }

    /**
     * Proses login siswa / guru / admin.
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $deviceId = $request->input('device_id') ?? ($request->ip() . '_' . substr(md5($request->userAgent() ?? ''), 0, 10));

        $result = $this->authService->attemptLogin(
            $request->input('username'),
            $request->input('password'),
            $deviceId
        );

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json($result, 401);
            }
            return back()->withErrors(['username' => $result['message']])->withInput();
        }

        $user = Auth::user();
        if ($user->isAdmin()) {
            $redirectUrl = route('admin.dashboard');
        } elseif ($user->isGuru()) {
            $redirectUrl = route('guru.dashboard');
        } else {
            $redirectUrl = route('exam.index');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Login berhasil',
                'redirect_url' => $redirectUrl,
                'user'         => $result['user'],
            ]);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Proses logout dan lepaskan kunci single-device.
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}
