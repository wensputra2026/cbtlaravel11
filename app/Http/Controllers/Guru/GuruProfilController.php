<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MasterGuru;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruProfilController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $guru = MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();

        return view('guru.profil', compact('user', 'guru'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_baru' => 'required|string|min:4|confirmed',
        ]);

        $user = Auth::user();
        $user->password = password_hash($request->input('password_baru'), PASSWORD_BCRYPT);
        $user->save();

        $guru = MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();

        if ($guru) {
            $guru->password = $request->input('password_baru');
            $guru->save();
        }

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
