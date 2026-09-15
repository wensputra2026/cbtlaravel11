<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Admin memiliki akses ke seluruh halaman admin & pengawasan
        if ($user->isAdmin() && in_array('admin', $roles)) {
            return $next($request);
        }

        if ($user->isGuru() && (in_array('guru', $roles) || in_array('pengawas', $roles))) {
            return $next($request);
        }

        if ($user->isSiswa() && in_array('siswa', $roles)) {
            return $next($request);
        }

        // Cek jika role cocok dengan nama grup
        foreach ($roles as $role) {
            if ($user->groups->contains('name', strtolower($role))) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Akun Anda tidak memiliki izin untuk tindakan ini.',
            ], 403);
        }

        // Redirect cerdas ke dashboard sesuai role yang dimiliki
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses dialihkan ke Panel Administrator.');
        } elseif ($user->isGuru()) {
            return redirect()->route('guru.dashboard')->with('error', 'Akses dialihkan ke Panel Guru.');
        }

        return redirect()->route('exam.index')->with('error', 'Akses dibatasi hanya untuk ruang ujian Anda.');
    }
}
