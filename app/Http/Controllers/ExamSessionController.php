<?php

namespace App\Http\Controllers;

use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\MasterSiswa;
use App\Services\Exam\CbtTokenService;
use App\Services\Exam\ExamSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamSessionController extends Controller
{
    public function __construct(
        protected ExamSessionService $examService,
        protected CbtTokenService $tokenService
    ) {}

    /**
     * Halaman Beranda Siswa (Dashboard CBT)
     * Dilengkapi Kartu Identitas Peserta dan Kartu Ujian Aktif.
     */
    public function index(): View
    {
        $user = Auth::user();
        $siswa = MasterSiswa::with([
            'nomorPeserta',
            'kelasSiswa.kelas',
            'sesiSiswa.ruang',
            'sesiSiswa.sesi'
        ])->where('username', $user->username)->first();

        $jadwals = [];
        if ($siswa) {
            $jadwals = $this->examService->getAvailableExamsForStudent($siswa->id_siswa);
        }

        $nomorPeserta = $siswa?->nomorPeserta?->nomor_peserta ?? '-';
        $namaKelas    = $siswa?->kelasSiswa?->first()?->kelas?->nama_kelas ?? '-';
        $namaRuang    = $siswa?->sesiSiswa?->first()?->ruang?->nama_ruang ?? 'Ruang 01';
        $namaSesi     = $siswa?->sesiSiswa?->first()?->sesi?->nama_sesi ?? 'Sesi 1';
        $fotoSiswa    = $siswa?->foto ? asset('uploads/foto_siswa/' . $siswa->foto) : null;

        return view('exam.index', [
            'user'         => $user,
            'siswa'        => $siswa,
            'nomorPeserta' => $nomorPeserta,
            'namaKelas'    => $namaKelas,
            'namaRuang'    => $namaRuang,
            'namaSesi'     => $namaSesi,
            'fotoSiswa'    => $fotoSiswa,
            'jadwals'      => $jadwals,
        ]);
    }

    /**
     * Halaman Konfirmasi Ujian & Input Token
     * Lembar verifikasi rincian tes sebelum memulai lembar pengerjaan.
     */
    public function konfirmasi(int $jadwalId): View|RedirectResponse
    {
        $user = Auth::user();
        $siswa = MasterSiswa::with([
            'nomorPeserta',
            'kelasSiswa.kelas',
            'sesiSiswa.ruang',
            'sesiSiswa.sesi'
        ])->where('username', $user->username)->firstOrFail();

        $jadwal = CbtJadwal::with(['bankSoal.mapel', 'jenis'])->findOrFail($jadwalId);

        // Verifikasi Otorisasi (Kelas, Agama, Mapel Pilihan Fase F)
        try {
            $this->examService->authorizeStudentForExam($jadwalId, $siswa->id_siswa);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        // Periksa apakah siswa sudah menyelesaikan ujian
        $cbtSiswa = CbtSiswa::where('id_jadwal', $jadwalId)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        if ($cbtSiswa && (int)$cbtSiswa->status === 2) {
            return redirect()->route('exam.hasil', $jadwalId)
                ->with('info', 'Anda telah menyelesaikan ujian ini.');
        }

        $nomorPeserta = $siswa->nomorPeserta?->nomor_peserta ?? '-';
        $namaKelas    = $siswa->kelasSiswa?->first()?->kelas?->nama_kelas ?? '-';
        $namaRuang    = $siswa->sesiSiswa?->first()?->ruang?->nama_ruang ?? 'Ruang 01';
        $namaSesi     = $siswa->sesiSiswa?->first()?->sesi?->nama_sesi ?? 'Sesi 1';

        $totalSoal = ($jadwal->bankSoal->tampil_pg ?? 0)
            + ($jadwal->bankSoal->tampil_kompleks ?? 0)
            + ($jadwal->bankSoal->tampil_jodohkan ?? 0)
            + ($jadwal->bankSoal->tampil_isian ?? 0)
            + ($jadwal->bankSoal->tampil_esai ?? 0);

        if ($totalSoal === 0) {
            $totalSoal = $jadwal->bankSoal?->soals()->count() ?? 0;
        }

        return view('exam.konfirmasi', [
            'user'         => $user,
            'siswa'        => $siswa,
            'jadwal'       => $jadwal,
            'nomorPeserta' => $nomorPeserta,
            'namaKelas'    => $namaKelas,
            'namaRuang'    => $namaRuang,
            'namaSesi'     => $namaSesi,
            'totalSoal'    => $totalSoal,
            'pakaiToken'   => (bool)$jadwal->token,
        ]);
    }

    /**
     * Proses Validasi Token & Masuk Lembar Ujian
     */
    public function prosesKonfirmasi(Request $request, int $jadwalId): RedirectResponse
    {
        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->firstOrFail();

        // Otorisasi Peserta
        try {
            $this->examService->authorizeStudentForExam($jadwalId, $siswa->id_siswa);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }

        $jadwal = CbtJadwal::findOrFail($jadwalId);

        $inputToken = strtoupper(trim((string)$request->input('token', '')));

        if ($jadwal->token) {
            if (empty($inputToken)) {
                return back()->with('error', 'Token ujian wajib diisi. Silakan minta token kepada guru/pengawas ruang.');
            }

            $validToken = $this->tokenService->getOrGenerateDynamicToken();
            if ($inputToken !== strtoupper(trim($validToken))) {
                return back()->with('error', 'Kode Token Ujian tidak cocok atau telah diperbarui. Silakan periksa kembali.');
            }
        }

        // Simpan token ke session agar terbawa ke single-page engine
        session(['exam_token_' . $jadwalId => $inputToken]);

        return redirect()->route('cbt.ujian', [
            'jadwalId' => $jadwalId,
            'token'    => $inputToken,
        ]);
    }

    /**
     * Halaman Hasil & Perolehan Nilai Ujian Siswa
     */
    public function hasil(int $jadwalId): View
    {
        $user = Auth::user();
        $siswa = MasterSiswa::with([
            'nomorPeserta',
            'kelasSiswa.kelas',
            'sesiSiswa.ruang',
            'sesiSiswa.sesi'
        ])->where('username', $user->username)->firstOrFail();

        $jadwal = CbtJadwal::with(['bankSoal.mapel', 'jenis'])->findOrFail($jadwalId);

        $cbtSiswa = CbtSiswa::where('id_jadwal', $jadwalId)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        $nilaiInput = is_string($cbtSiswa?->nilai_input)
            ? json_decode($cbtSiswa->nilai_input, true)
            : ($cbtSiswa?->nilai_input ?? []);

        $pgNilai    = (float)($nilaiInput['pg_nilai'] ?? 0);
        $esaiNilai  = (float)($nilaiInput['essai_nilai'] ?? 0);
        $totalNilai = $pgNilai + $esaiNilai;

        $nomorPeserta = $siswa->nomorPeserta?->nomor_peserta ?? '-';
        $namaKelas    = $siswa->kelasSiswa?->first()?->kelas?->nama_kelas ?? '-';

        return view('exam.hasil', [
            'user'           => $user,
            'siswa'          => $siswa,
            'jadwal'         => $jadwal,
            'cbtSiswa'       => $cbtSiswa,
            'nomorPeserta'   => $nomorPeserta,
            'namaKelas'      => $namaKelas,
            'tampilkanNilai' => (bool)$jadwal->hasil_tampil,
            'pgNilai'        => $pgNilai,
            'esaiNilai'      => $esaiNilai,
            'totalNilai'     => $totalNilai,
        ]);
    }

    /**
     * Tampilan antarmuka ujian siswa (Zero-Latency UI berbasis Alpine.js).
     */
    public function showSession(int $jadwalId): View
    {
        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->firstOrFail();
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);

        return view('exam.session', [
            'user'     => $user,
            'siswa'    => $siswa,
            'jadwal'   => $jadwal,
            'jadwalId' => $jadwalId,
        ]);
    }

    /**
     * API: Memulai ujian, memverifikasi token, dan mengembalikan paket soal (tanpa kunci jawaban).
     */
    public function apiStartExam(Request $request, int $jadwalId): JsonResponse
    {
        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $token = $request->input('token');

        try {
            $payload = $this->examService->startExam($jadwalId, $siswa->id_siswa, $token);
            return response()->json([
                'success' => true,
                'data'    => $payload,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Autosave jawaban ke Redis buffer in-memory (< 2ms).
     * Menerapkan fallback otomatis ke database jika Redis offline.
     */
    public function apiAutoSave(Request $request, ?int $jadwalId = null): JsonResponse
    {
        $jid = (int) ($jadwalId ?? $request->input('jadwal_id'));
        if ($jid <= 0) {
            return response()->json(['success' => false, 'message' => 'ID Jadwal tidak valid.'], 422);
        }

        $request->validate([
            'soal_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $soalId   = (int) $request->input('soal_id');
        $jawaban  = $request->input('jawaban');
        $ragu     = (bool) $request->input('ragu', false);

        $saved = $this->examService->autoSaveAnswer(
            $jid,
            $siswa->id_siswa,
            $soalId,
            $jawaban,
            $ragu
        );

        return response()->json([
            'success'   => $saved,
            'soal_id'   => $soalId,
            'timestamp' => time(),
        ]);
    }

    /**
     * API: Deteksi pelanggaran anti-cheat (ganti tab, window blur, inspect element).
     */
    public function apiRecordViolation(Request $request): JsonResponse
    {
        $request->validate([
            'jadwal_id' => 'required|integer',
            'type'      => 'nullable|string',
        ]);

        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->first();
        if (!$siswa) {
            return response()->json(['success' => false], 404);
        }

        $jadwalId = (int) $request->input('jadwal_id');
        $type     = $request->input('type', 'window_blur');

        $result = $this->examService->recordViolation($jadwalId, $siswa->id_siswa, $type);
        return response()->json($result);
    }

    /**
     * API: Sinkronisasi timer server secara tamper-proof.
     */
    public function apiSyncTimer(Request $request, int $jadwalId): JsonResponse
    {
        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->first();
        if (!$siswa) {
            return response()->json(['success' => false], 404);
        }

        $result = $this->examService->syncTimer($jadwalId, $siswa->id_siswa);
        return response()->json($result);
    }

    /**
     * API: Mengakhiri ujian, evaluasi skor, dan batch sync ke MySQL legacy.
     */
    public function apiFinishExam(Request $request, int $jadwalId): JsonResponse
    {
        $user = Auth::user();
        $siswa = MasterSiswa::where('username', $user->username)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        try {
            $result = $this->examService->finishExam($jadwalId, $siswa->id_siswa);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan ujian: ' . $e->getMessage(),
            ], 500);
        }
    }
}
