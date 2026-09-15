<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtPengawas;
use App\Models\MasterGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruJadwalController extends Controller
{
    protected function getGuru(): ?MasterGuru
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Tampilkan jadwal tes yang menggunakan bank soal milik guru atau ditugaskan sebagai pengawas.
     */
    public function index(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;

        // Ambil jadwal yang menggunakan bank soal milik guru bersangkutan
        $query = CbtJadwal::with(['bankSoal.mapel', 'jenis'])
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                $q->where('bank_guru_id', $guruId);
            });

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->whereHas('bankSoal', function ($bq) use ($search) {
                $bq->where('bank_nama', 'like', "%{$search}%")
                   ->orWhere('bank_kode', 'like', "%{$search}%");
            });
        }

        $jadwalSaya = $query->orderBy('tgl_mulai', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Ambil jadwal di mana guru ini ditugaskan sebagai pengawas ruang
        $tugasPengawas = CbtPengawas::with(['jadwal.bankSoal.mapel', 'jadwal.jenis', 'ruang', 'sesi'])
            ->where('id_guru', $guruId)
            ->get();

        return view('guru.jadwal.index', compact('guru', 'jadwalSaya', 'tugasPengawas'));
    }
}
