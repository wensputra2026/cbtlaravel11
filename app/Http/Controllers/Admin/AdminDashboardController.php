<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterSiswa;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard Utama Administrator CBT.
     */
    public function index(): View
    {
        $totalSiswa = MasterSiswa::count();
        $totalGuru  = MasterGuru::count();
        $totalKelas = MasterKelas::count();
        $totalMapel = MasterMapel::count();
        $totalBank  = CbtBankSoal::count();
        
        $jadwalAktif = CbtJadwal::with(['bankSoal.mapel', 'jenis'])
            ->where('status', 1)
            ->orderBy('id_jadwal', 'desc')
            ->take(10)
            ->get();

        // Peserta aktif / sedang ujian hari ini
        $pesertaAktif = CbtSiswa::where('status', 1)->count();
        $pesertaSelesai = CbtSiswa::where('status', 2)->count();

        // Status Sistem & Memori
        $serverInfo = [
            'php_version'    => PHP_VERSION,
            'laravel_version'=> app()->version(),
            'memory_usage'   => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'server_os'      => PHP_OS,
        ];

        return view('admin.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'totalBank',
            'jadwalAktif',
            'pesertaAktif',
            'pesertaSelesai',
            'serverInfo'
        ));
    }
}
