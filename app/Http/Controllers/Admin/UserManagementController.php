<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    // =========================================================================
    // 1. USER ADMINISTRATOR
    // =========================================================================
    public function indexAdmin(Request $request): View
    {
        $search = $request->input('q');
        $query = User::where(function ($q) {
            $q->whereHas('groups', function ($g) {
                $g->where('name', 'admin');
            })->orWhere('id', 1);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        return view('admin.users.admin', compact('admins', 'search'));
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'username'   => 'required|string|max:50|unique:users,username',
            'password'   => 'required|string|min:4',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'first_name' => $request->input('first_name'),
                'username'   => $request->input('username'),
                'password'   => password_hash($request->input('password'), PASSWORD_BCRYPT),
                'active'     => 1,
                'created_on' => time(),
            ]);
            $user->groups()->attach(User::ROLE_ADMIN);
        });

        return back()->with('success', 'Akun Administrator baru berhasil ditambahkan.');
    }

    // =========================================================================
    // 2. USER GURU
    // =========================================================================
    public function indexGuru(Request $request): View
    {
        $search = $request->input('q');
        $query = MasterGuru::with('user');

        if ($search) {
            $query->where('nama_guru', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
        }

        $gurus = $query->orderBy('nama_guru', 'asc')->paginate(10)->withQueryString();
        return view('admin.users.guru', compact('gurus', 'search'));
    }

    // =========================================================================
    // 3. USER SISWA
    // =========================================================================
    public function indexSiswa(Request $request): View
    {
        $search = $request->input('q');
        $kelasId = $request->input('kelas_id');

        $query = MasterSiswa::with('kelasSiswa.kelas');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($kelasId) {
            $siswaIds = DB::table('kelas_siswa')->where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();
        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.users.siswa', compact('siswas', 'kelasList', 'search', 'kelasId'));
    }

    // =========================================================================
    // 4. RESET PASSWORD & TOGGLE AKTIF
    // =========================================================================
    public function resetPassword(Request $request, int $userId): RedirectResponse
    {
        $request->validate(['new_password' => 'required|string|min:4']);
        $newPass = $request->input('new_password');

        $user = User::findOrFail($userId);
        $user->password = password_hash($newPass, PASSWORD_BCRYPT);
        $user->save();

        // Sinkronkan ke master guru atau master siswa jika ada
        MasterGuru::where('username', $user->username)->update(['password' => $newPass]);
        MasterSiswa::where('username', $user->username)->update(['password' => $newPass]);

        return back()->with('success', "Kata sandi pengguna [{$user->username}] berhasil di-reset menjadi [{$newPass}].");
    }

    public function toggleActive(int $userId): RedirectResponse
    {
        $user = User::findOrFail($userId);
        $user->active = $user->active ? 0 : 1;
        $user->save();

        $status = $user->active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun [{$user->username}] berhasil {$status}.");
    }

    // =========================================================================
    // 5. LOG AKTIVITAS PENGGUNA
    // =========================================================================
    public function indexLogs(): View
    {
        $logs = [];
        if (Schema::hasTable('log')) {
            $logs = DB::table('log')
                ->leftJoin('users', 'log.id_user', '=', 'users.id')
                ->select('log.*', 'users.username', 'users.first_name')
                ->orderBy('log.id_log', 'desc')
                ->paginate(10);
        }

        return view('admin.users.logs', compact('logs'));
    }
}
