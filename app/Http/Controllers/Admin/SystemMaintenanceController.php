<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtSiswa;
use App\Models\CbtToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

class SystemMaintenanceController extends Controller
{
    /**
     * Halaman Pemeliharaan Sistem & Database.
     */
    public function index(): View
    {
        $dbName = DB::getDatabaseName();
        $totalSessions = CbtSiswa::count();
        $activeTokens = CbtToken::count();

        // Cek ruang penyimpanan hard drive
        $freeDisk = round(@disk_free_space('.') / 1024 / 1024 / 1024, 2);
        $totalDisk = round(@disk_total_space('.') / 1024 / 1024 / 1024, 2);

        return view('admin.setting.maintenance', compact('dbName', 'totalSessions', 'activeTokens', 'freeDisk', 'totalDisk'));
    }

    /**
     * Reset Seluruh Kunci Login Perangkat (Single-Device Locks).
     */
    public function resetAllDeviceLocks(): RedirectResponse
    {
        try {
            $keys = Redis::keys('cbt_device_lock:*');
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } catch (\Throwable $e) {
            // Redis fallback
        }

        return back()->with('success', 'Seluruh kunci sesi login perangkat berhasil di-reset. Semua siswa dapat login kembali.');
    }

    /**
     * Bersihkan Riwayat Pelaksanaan Ujian (Hapus data cbt_siswa yang telah selesai).
     */
    public function clearCompletedSessions(Request $request): RedirectResponse
    {
        $deleted = CbtSiswa::where('status', 2)->delete();
        return back()->with('success', "Sebanyak {$deleted} riwayat ujian yang telah selesai berhasil dibersihkan.");
    }
}
