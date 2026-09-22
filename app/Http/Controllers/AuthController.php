<?php

namespace App\Http\Controllers;

use App\Services\Auth\LegacyAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected LegacyAuthService $authService
    ) {}

    /**
     * Tampilkan form login Garuda CBT (kompatibel penuh dengan http://localhost/garudacbt/auth).
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

        $setting = null;
        try {
            if (Schema::hasTable('setting')) {
                $setting = DB::table('setting')->first();
            }
        } catch (\Throwable $e) {}

        return view('auth.login', compact('setting'));
    }

    /**
     * Proses login siswa / guru / admin (Mendukung AJAX & Standar Form).
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        if (!$request->has('username') && $request->has('identity')) {
            $request->merge(['username' => $request->input('identity')]);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:3|max:50',
        ], [
            'username.required' => 'Username wajib diisi!',
            'password.required' => 'Password wajib diisi!',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau Password salah!',
                    'error'   => $validator->errors(),
                    'role'    => '0',
                    'user'    => null,
                ]);
            }
            return back()->withErrors($validator)->withInput();
        }

        $deviceId = $request->input('device_id') ?? ($request->ip() . '_' . substr(md5($request->userAgent() ?? ''), 0, 10));

        $result = $this->authService->attemptLogin(
            $request->input('username'),
            $request->input('password'),
            $deviceId
        );

        if (!$result['success']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error'   => [],
                    'role'    => $result['role'] ?? '0',
                    'user'    => null,
                ]);
            }
            return back()->withErrors(['username' => $result['message']])->withInput();
        }

        // Tentukan URL Pengalihan Berdasarkan Peran Pengguna & Pilihan CBT Only
        $user = Auth::user();
        $isCbtOnly = (string) $request->input('cbt-only') === '1';

        if ($user->isAdmin()) {
            $redirectUrl = route('admin.dashboard');
        } elseif ($user->isGuru()) {
            $redirectUrl = route('guru.dashboard');
        } else {
            // Level Siswa: jika mode CBT aktif, arahkan ke modul ujian cbt
            $redirectUrl = $isCbtOnly ? route('exam.index') : route('exam.index');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Login berhasil',
                'role'         => (string) ($result['role_id'] ?? '1'),
                'redirect_url' => $redirectUrl,
                'user'         => $result['user'] ?? null,
            ]);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Proses logout, lepaskan kunci perangkat, dan simpan log audit.
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}
