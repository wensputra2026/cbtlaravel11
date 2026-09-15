<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\MasterGuru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruKoreksiController extends Controller
{
    protected function getGuru()
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    public function index(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;

        // Ambil jadwal yang bank soalnya dibuat oleh guru ini
        $query = CbtJadwal::with('bankSoal.mapel')
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

        $jadwalList = $query->orderBy('id_jadwal', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('guru.koreksi.index', compact('jadwalList', 'guru'));
    }

    public function showPeserta(Request $request, int $jadwalId): View
    {
        $guru = $this->getGuru();
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);

        $query = CbtSiswa::with('siswa.kelasSiswa.kelas')
            ->where('id_jadwal', $jadwalId);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->whereHas('siswa', function ($sq) use ($search) {
                $sq->where('nama', 'like', "%{$search}%")
                   ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $pesertaList = $query->orderBy('id_cbt_siswa', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('guru.koreksi.peserta', compact('jadwal', 'pesertaList', 'guru'));
    }

    public function showFormKoreksi(int $cbtSiswaId): View
    {
        $guru = $this->getGuru();
        $cbtSiswa = CbtSiswa::with(['siswa', 'jadwal.bankSoal'])->findOrFail($cbtSiswaId);

        $jawabanList = is_string($cbtSiswa->jawaban) ? json_decode($cbtSiswa->jawaban, true) : ($cbtSiswa->jawaban ?? []);
        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);

        return view('guru.koreksi.form', compact('cbtSiswa', 'jawabanList', 'nilaiInput', 'guru'));
    }

    public function storeKoreksi(Request $request, int $cbtSiswaId): RedirectResponse
    {
        $cbtSiswa = CbtSiswa::findOrFail($cbtSiswaId);
        $nilaiEsai = (float) $request->input('nilai_esai', 0);

        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);
        if (!is_array($nilaiInput)) {
            $nilaiInput = [];
        }

        $nilaiInput['dikoreksi'] = '1';
        $nilaiInput['essai_nilai'] = $nilaiEsai;

        $cbtSiswa->nilai_input = json_encode($nilaiInput);
        $cbtSiswa->save();

        return back()->with('success', 'Nilai esai siswa berhasil disimpan.');
    }
}
