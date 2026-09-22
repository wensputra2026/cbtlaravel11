<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoalClean;
use App\Models\CbtJadwalClean;
use App\Models\CbtJawabanSiswa;
use App\Models\CbtSoalClean;
use App\Models\CbtUjianSiswa;
use App\Models\MasterSiswa;
use App\Models\Siswa;
use App\Services\Exam\CbtTokenService;
use App\Services\Exam\ExamRedisBuffer;
use App\Services\Exam\ExamSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamSessionController extends Controller
{
    /**
     * Batas maksimal toleransi pelanggaran kecurangan sebelum auto-lock.
     */
    protected const MAX_VIOLATIONS = 3;

    public function __construct(
        protected ExamSessionService $legacyExamService,
        protected CbtTokenService $tokenService
    ) {}

    /**
     * Helper untuk mengambil data profil siswa yang sedang login.
     * Mendukung pemetaan via Eloquent relasi, user_id, atau username.
     */
    protected function getAuthenticatedSiswa(Request $request): ?Siswa
    {
        $user = Auth::user();

        // Dukungan resolusi Bearer Token / X-Auth-Token dari header
        if (!$user) {
            $bearer = $request->bearerToken() ?? $request->header('X-Auth-Token');
            if ($bearer) {
                $userId = ExamRedisBuffer::get("cbt_auth_user:{$bearer}");
                if ($userId) {
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        Auth::setUser($user);
                    }
                }
            }
        }

        if (!$user) {
            return null;
        }

        // 1. Coba dari attributes yang diset middleware
        $cachedSiswa = $request->attributes->get('auth_siswa');
        if ($cachedSiswa instanceof Siswa) {
            return $cachedSiswa;
        }

        // 2. Ambil dari model Siswa (skema bersih)
        $siswa = Siswa::with(['kelas', 'sesi', 'ruang'])->where('user_id', $user->id)->first();
        if ($siswa) {
            return $siswa;
        }

        // 3. Fallback jika data siswa masih dihubungkan via username
        $siswa = Siswa::with(['kelas', 'sesi', 'ruang'])->where('nis', $user->username)->first();
        if ($siswa) {
            return $siswa;
        }

        // 4. Fallback ke tabel master_siswa legacy jika skema bersih belum terisi
        $legacy = MasterSiswa::where('username', $user->username)->first();
        if ($legacy) {
            // Bungkus ke objek Siswa on-the-fly agar seragam
            $cleanSiswa = new Siswa([
                'user_id'       => $user->id,
                'nis'           => $legacy->nis ?: (string) $legacy->id_siswa,
                'nisn'          => (string) $legacy->nisn,
                'nama_lengkap'  => $legacy->nama,
                'jenis_kelamin' => strtoupper(substr($legacy->jenis_kelamin ?: 'L', 0, 1)),
                'kelas_id'      => is_numeric($legacy->kelas_awal) ? (int) $legacy->kelas_awal : null,
            ]);
            $cleanSiswa->id = $legacy->id_siswa;
            return $cleanSiswa;
        }

        return null;
    }

    /**
     * A. startExam(Request $request, $jadwalId)
     *
     * - Validasi hak akses peserta & rentang waktu jadwal ujian.
     * - Pencocokan alokasi kelas bank soal dengan kelas siswa.
     * - Verifikasi token ujian via CbtTokenService (jika pakai_token == true).
     * - Sanitasi Kunci Jawaban: Kunci jawaban (kunci_jawaban / jawaban) DIHAPUS SEPENUHNYA dari JSON response!
     * - Pengacakan soal & opsi deterministik berbasis seed crc32($siswaId . '_' . $jadwalId).
     * - Inisialisasi record pengerjaan cbt_ujian_siswa & timer tersinkronisasi Redis.
     * - Restorasi jawaban sebelumnya dari Redis Hash buffer (cbt_answers:{jadwalId}:{siswaId}).
     *
     * Route: GET /api/cbt/start/{jadwalId} atau POST /api/cbt/start/{jadwalId}
     */
    public function startExam(Request $request, $jadwalId): JsonResponse
    {
        $jadwalId = (int) $jadwalId;
        $siswa = $this->getAuthenticatedSiswa($request);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi peserta diperlukan. Silakan login terlebih dahulu.',
            ], 401);
        }

        $siswaId = (int) $siswa->id;

        // 1. Ambil Data Jadwal Ujian (Skema Bersih cbt_jadwal_clean)
        $jadwal = CbtJadwalClean::with(['bankSoal.mapel', 'bankSoal.soal'])->find($jadwalId);

        // Fallback kompatibilitas ke tabel legacy jika skema bersih jadwal belum terdaftar
        $bank = null;
        $isCleanSchema = true;

        if ($jadwal) {
            $bank = $jadwal->bankSoal;
        } else {
            $legacyJadwal = \App\Models\CbtJadwal::with(['bankSoal.mapel', 'bankSoal.soals'])->find($jadwalId);
            if (!$legacyJadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal ujian tidak ditemukan.',
                ], 404);
            }
            $isCleanSchema = false;
            // Petakan atribut jadwal legacy ke format seragam
            $jadwal = (object) [
                'id'              => $legacyJadwal->id_jadwal,
                'nama_ujian'      => $legacyJadwal->bankSoal->bank_nama ?? 'Ujian CBT',
                'kode_jenis'      => 'PAT',
                'durasi_menit'    => (int) $legacyJadwal->durasi_ujian,
                'waktu_mulai'     => $legacyJadwal->tgl_mulai ? \Carbon\Carbon::parse($legacyJadwal->tgl_mulai) : null,
                'waktu_selesai'   => $legacyJadwal->tgl_selesai ? \Carbon\Carbon::parse($legacyJadwal->tgl_selesai) : null,
                'acak_soal'       => (bool) $legacyJadwal->acak_soal,
                'acak_opsi'       => (bool) $legacyJadwal->acak_opsi,
                'pakai_token'     => (bool) $legacyJadwal->token,
                'tampilkan_nilai' => (bool) $legacyJadwal->hasil_tampil,
                'status'          => (int) $legacyJadwal->status,
            ];
            $bank = $legacyJadwal->bankSoal;
        }

        if ($jadwal->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian ini sedang dinonaktifkan oleh administrator/proktor.',
            ], 403);
        }

        // 2. Validasi Rentang Waktu Pelaksanaan Ujian
        $now = now();
        if ($jadwal->waktu_mulai && $now->lt($jadwal->waktu_mulai)) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian belum dapat dimulai. Pelaksanaan dibuka pada: ' . $jadwal->waktu_mulai->format('d/m/Y H:i') . ' WIB.',
            ], 403);
        }

        if ($jadwal->waktu_selesai && $now->gt($jadwal->waktu_selesai)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan ujian ini telah ditutup pada: ' . $jadwal->waktu_selesai->format('d/m/Y H:i') . ' WIB.',
            ], 403);
        }

        // 3. Validasi Alokasi Kelas Siswa
        if ($bank) {
            $alokasiKelas = $isCleanSchema ? $bank->alokasi_kelas : $bank->kelas_ids;
            if (is_string($alokasiKelas)) {
                $alokasiKelas = json_decode($alokasiKelas, true) ?? @unserialize($alokasiKelas) ?? [];
            }
            if (!empty($alokasiKelas) && is_array($alokasiKelas)) {
                $siswaKelasId = (string) ($siswa->kelas_id ?? '');
                $allowedIds = array_map('strval', $alokasiKelas);
                if ($siswaKelasId !== '' && !in_array($siswaKelasId, $allowedIds, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kelas Anda tidak terdaftar dalam alokasi peserta untuk ujian ini.',
                    ], 403);
                }
            }
        }

        // 4. Verifikasi Status Pemutusan Ujian karena Pelanggaran Anti-Cheat
        $isTerminated = (int) ExamRedisBuffer::get("cbt_exam_terminated:{$jadwalId}:{$siswaId}");
        if ($isTerminated === 1) {
            return response()->json([
                'success'    => false,
                'terminated' => true,
                'message'    => 'Ujian Anda telah dibatalkan otomatis oleh sistem karena terdeteksi melakukan kecurangan berulang kali.',
            ], 403);
        }

        // 5. Verifikasi Token Ujian
        if ($jadwal->pakai_token) {
            $tokenInput = trim((string) ($request->input('token') ?? $request->header('X-Exam-Token')));
            if (empty($tokenInput) || !$this->tokenService->verifyToken($jadwalId, $tokenInput)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token ujian tidak valid atau sudah kadaluarsa. Silakan minta token aktif kepada proktor pengawas.',
                ], 422);
            }
        }

        // 6. Inisialisasi / Ambil Sesi Ujian Siswa (cbt_ujian_siswa)
        $ujianSiswa = null;
        if (class_exists(CbtUjianSiswa::class)) {
            $ujianSiswa = CbtUjianSiswa::firstOrCreate(
                ['jadwal_id' => $jadwalId, 'siswa_id' => $siswaId],
                [
                    'waktu_mulai'       => $now,
                    'sisa_detik'        => $jadwal->durasi_menit * 60,
                    'status'            => 1, // sedang ujian
                    'pelanggaran_count' => 0,
                ]
            );

            if ($ujianSiswa->status === 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah menyelesaikan ujian ini sebelumnya.',
                ], 400);
            }
        }

        // Kompatibilitas tabel legacy cbt_durasi_siswa jika tabel tersedia
        $durasiLegacy = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('cbt_durasi_siswa')) {
            try {
                $durasiId = \App\Models\CbtDurasiSiswa::generateId($siswaId, $jadwalId);
                $durasiLegacy = \App\Models\CbtDurasiSiswa::find($durasiId);
                if (!$durasiLegacy) {
                    \App\Models\CbtDurasiSiswa::create([
                        'id_durasi'  => $durasiId,
                        'id_siswa'   => $siswaId,
                        'id_jadwal'  => $jadwalId,
                        'status'     => \App\Models\CbtDurasiSiswa::STATUS_SEDANG,
                        'mulai'      => $now->toDateTimeString(),
                        'lama_ujian' => '00:00:00',
                        'reset'      => 0,
                    ]);
                } elseif ($durasiLegacy->status === \App\Models\CbtDurasiSiswa::STATUS_SELESAI) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah menyelesaikan ujian ini sebelumnya.',
                    ], 400);
                }
            } catch (\Throwable $e) {}
        }

        // 7. Manajemen Timer Tersinkronisasi Server
        $timerRedisKey = "cbt_timer:{$jadwalId}:{$siswaId}";
        $extraMinutes = (int) ExamRedisBuffer::get("cbt_extra_time:{$jadwalId}:{$siswaId}");
        $totalDurasiDetik = ($jadwal->durasi_menit + $extraMinutes) * 60;

        $waktuMulaiTs = $ujianSiswa?->waktu_mulai ? $ujianSiswa->waktu_mulai->timestamp : ($durasiLegacy ? strtotime($durasiLegacy->mulai) : $now->timestamp);
        $elapsedDetik = max(0, time() - $waktuMulaiTs);
        $sisaDetikServer = max(0, $totalDurasiDetik - $elapsedDetik);

        $sisaDetikRedis = ExamRedisBuffer::get($timerRedisKey);
        if ($sisaDetikRedis === null || (int) $sisaDetikRedis <= 0) {
            $sisaDetik = $sisaDetikServer;
        } else {
            // Gunakan nilai terkecil antara Redis dan kalkulasi server untuk mencegah time-drift tampering
            $sisaDetik = min((int) $sisaDetikRedis, $sisaDetikServer);
        }

        ExamRedisBuffer::set($timerRedisKey, $sisaDetik, $totalDurasiDetik + 7200);

        if ($sisaDetik <= 0) {
            $this->finishExamSession($jadwalId, $siswaId);
            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan ujian telah habis.',
            ], 400);
        }

        // 8. Ambil Soal, Sanitasi Kunci Jawaban & Pengacakan Deterministik
        $seed = (int) sprintf('%u', crc32($siswaId . '_' . $jadwalId));
        $rawSoals = [];

        if ($isCleanSchema && $bank) {
            $rawSoals = CbtSoalClean::where('bank_id', $bank->id)->orderBy('nomor_urut')->get()->all();
        }

        if (empty($rawSoals) && $bank) {
            $rawSoals = \App\Models\CbtSoal::where('bank_id', $bank->id_bank ?? $bank->id)->orderBy('nomor_soal')->get()->all();
        }

        // Acak Soal jika diaktifkan (Deterministic Shuffle)
        if ($jadwal->acak_soal) {
            $this->seededShuffle($rawSoals, $seed);
        }

        // Simpan urutan soal di Redis
        $orderedIds = array_map(static fn($s) => (int) ($s->id ?? $s->id_soal), $rawSoals);
        ExamRedisBuffer::set("cbt_soal_order:{$jadwalId}:{$siswaId}", json_encode($orderedIds), 86400);

        // STRUKTURKAN DAN SANITASI KUNCI JAWABAN (CRITICAL SECURITY)
        $sanitizedSoal = [];
        $noUrut = 1;

        foreach ($rawSoals as $soal) {
            $soalId = (int) ($soal->id ?? $soal->id_soal);
            $jenisSoal = (int) ($soal->jenis_soal ?? $soal->jenis ?? 1);
            $opsiFormatted = [];

            // Pilihan Ganda (PG)
            if ($jenisSoal === 1) {
                $rawOpsis = [];

                if (isset($soal->opsi) && is_array($soal->opsi)) {
                    $rawOpsis = $soal->opsi;
                } else {
                    $rawOpsis = [
                        'A' => $soal->opsi_a ?? null,
                        'B' => $soal->opsi_b ?? null,
                        'C' => $soal->opsi_c ?? null,
                        'D' => $soal->opsi_d ?? null,
                        'E' => $soal->opsi_e ?? null,
                    ];
                }

                // Filter opsi yang tidak kosong
                $available = [];
                foreach ($rawOpsis as $lbl => $txt) {
                    if (!empty($txt)) {
                        $available[$lbl] = $txt;
                    }
                }

                // Acak Pilihan Jawaban jika acak_opsi aktif
                $items = [];
                foreach ($available as $label => $text) {
                    $items[] = ['label' => (string) $label, 'content' => (string) $text];
                }

                if ($jadwal->acak_opsi) {
                    $itemSeed = $seed + $soalId;
                    $this->seededShuffle($items, $itemSeed);
                }

                $opsiFormatted = $items;
            } elseif ($jenisSoal === 2) {
                // PG Kompleks (Checklist)
                $opsiFormatted = is_array($soal->opsi ?? null) ? $soal->opsi : [
                    'A' => $soal->opsi_a ?? null,
                    'B' => $soal->opsi_b ?? null,
                    'C' => $soal->opsi_c ?? null,
                    'D' => $soal->opsi_d ?? null,
                    'E' => $soal->opsi_e ?? null,
                ];
            } elseif ($jenisSoal === 3) {
                // Menjodohkan
                $opsiFormatted = is_array($soal->opsi ?? null) ? $soal->opsi : [
                    'left'  => $soal->opsi_a ?? null,
                    'right' => $soal->opsi_b ?? null,
                ];
            }

            // CRITICAL SECURITY: KUNCI JAWABAN (kunci_jawaban / jawaban) TIDAK PERNAH DISERTAKAN
            $sanitizedSoal[] = [
                'id'          => $soalId,
                'nomor_urut'  => $noUrut++,
                'jenis_soal'  => $jenisSoal,
                'pertanyaan'  => (string) ($soal->pertanyaan ?? $soal->soal ?? ''),
                'media'       => $soal->media ?? $soal->file ?? $soal->file1 ?? null,
                'opsi'        => $opsiFormatted,
            ];
        }

        // 9. Restorasi Jawaban Sebelumnya dari Redis Hash Buffer
        $rawSavedAnswers = ExamRedisBuffer::hgetall("cbt_answers:{$jadwalId}:{$siswaId}");
        if (empty($rawSavedAnswers)) {
            $rawSavedAnswers = ExamRedisBuffer::hgetall("cbt_jawaban:{$jadwalId}:{$siswaId}");
        }

        $restoredAnswers = [];
        foreach ($rawSavedAnswers as $sId => $rawJson) {
            $decoded = json_decode($rawJson, true);
            $restoredAnswers[$sId] = $decoded ?: ['jawaban' => $rawJson, 'ragu' => false];
        }

        // 10. Status Pelanggaran
        $violations = (int) ExamRedisBuffer::get("cbt_violation:{$jadwalId}:{$siswaId}");

        return response()->json([
            'success'            => true,
            'jadwal'             => [
                'id'              => $jadwal->id,
                'nama_ujian'      => $jadwal->nama_ujian,
                'kode_jenis'      => $jadwal->kode_jenis,
                'durasi_menit'    => $jadwal->durasi_menit,
                'acak_soal'       => $jadwal->acak_soal,
                'acak_opsi'       => $jadwal->acak_opsi,
                'tampilkan_nilai' => $jadwal->tampilkan_nilai,
                'total_soal'      => count($sanitizedSoal),
            ],
            'sisa_detik'         => $sisaDetik,
            'server_time'        => now()->timestamp,
            'violations'         => $violations,
            'max_violations'     => self::MAX_VIOLATIONS,
            'soal'               => $sanitizedSoal,
            'jawaban_tersimpan'  => $restoredAnswers,
        ]);
    }

    /**
     * B. autoSave(Request $request, $jadwalId)
     *
     * - Menerima payload: { "soal_id": int, "jawaban": mixed, "ragu": bool }
     * - Simpan langsung ke Redis Hash buffer: HSET cbt_answers:{jadwalId}:{siswaId} {soal_id} {json}
     * - Latensi super rendah (< 5ms) tanpa menyentuh disk write / query insert ke MySQL.
     *
     * Route: POST /api/cbt/autosave/{jadwalId}
     */
    public function autoSave(Request $request, $jadwalId): JsonResponse
    {
        $jadwalId = (int) $jadwalId;
        $siswa = $this->getAuthenticatedSiswa($request);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi peserta diperlukan.',
            ], 401);
        }

        $siswaId = (int) $siswa->id;

        // Cek apakah ujian telah di-terminate karena kecurangan
        $isTerminated = (int) ExamRedisBuffer::get("cbt_exam_terminated:{$jadwalId}:{$siswaId}");
        if ($isTerminated === 1) {
            return response()->json([
                'success'    => false,
                'terminated' => true,
                'message'    => 'Ujian telah dibatalkan otomatis oleh sistem karena pelanggaran kecurangan.',
            ], 403);
        }

        // Cek status timer di Redis
        $timer = ExamRedisBuffer::get("cbt_timer:{$jadwalId}:{$siswaId}");
        if ($timer !== null && (int) $timer <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan ujian telah habis.',
            ], 400);
        }

        $request->validate([
            'soal_id' => 'required',
            'jawaban' => 'nullable',
            'ragu'    => 'nullable|boolean',
        ]);

        $soalId = (string) $request->input('soal_id');
        $jawaban = $request->input('jawaban');
        $ragu = (bool) $request->input('ragu', false);
        $timestamp = now()->timestamp;

        $hashPayload = json_encode([
            'jawaban'    => $jawaban,
            'ragu'       => $ragu,
            'updated_at' => $timestamp,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Simpan langsung ke Redis In-Memory Hash (< 5ms)
        ExamRedisBuffer::hset("cbt_answers:{$jadwalId}:{$siswaId}", $soalId, $hashPayload, 86400);
        ExamRedisBuffer::hset("cbt_jawaban:{$jadwalId}:{$siswaId}", $soalId, $hashPayload, 86400);

        return response()->json([
            'success'   => true,
            'soal_id'   => $soalId,
            'synced_at' => $timestamp,
        ]);
    }

    /**
     * C. logCheatViolation(Request $request, $jadwalId)
     *
     * - Menerima payload: { "violation_type": "tab_switch" | "window_blur" | "dev_tools" }
     * - Catat counter ke Redis: INCR cbt_violation:{jadwalId}:{siswaId}
     * - Jika counter >= 3: set cbt_exam_terminated:{jadwalId}:{siswaId} = 1, update cbt_ujian_siswa, return HTTP 403.
     * - Jika < 3: kembalikan peringatan ramah peserta beserta sisa toleransi.
     *
     * Route: POST /api/cbt/violation/{jadwalId}
     */
    public function logCheatViolation(Request $request, $jadwalId): JsonResponse
    {
        $jadwalId = (int) $jadwalId;
        $siswa = $this->getAuthenticatedSiswa($request);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi peserta diperlukan.',
            ], 401);
        }

        $siswaId = (int) $siswa->id;
        $violationType = (string) ($request->input('violation_type') ?: 'window_blur');

        // Increment atomic counter pelanggaran di Redis
        $counter = ExamRedisBuffer::incr("cbt_violation:{$jadwalId}:{$siswaId}", 86400);

        // Catat riwayat log detail pelanggaran
        ExamRedisBuffer::rpush("cbt_violation_log:{$jadwalId}:{$siswaId}", json_encode([
            'type' => $violationType,
            'time' => now()->toDateTimeString(),
            'ip'   => $request->ip(),
        ]));

        // Update counter pelanggaran di database jika record ada
        try {
            CbtUjianSiswa::where('jadwal_id', $jadwalId)
                ->where('siswa_id', $siswaId)
                ->update(['pelanggaran_count' => $counter]);
        } catch (\Throwable $e) {}

        if ($counter >= self::MAX_VIOLATIONS) {
            // Kunci permanen sesi ujian siswa
            ExamRedisBuffer::set("cbt_exam_terminated:{$jadwalId}:{$siswaId}", 1, 86400);

            return response()->json([
                'success'        => false,
                'terminated'     => true,
                'violations'     => $counter,
                'max_violations' => self::MAX_VIOLATIONS,
                'message'        => 'Ujian Anda telah dibatalkan otomatis oleh sistem karena terdeteksi berpindah layar / melakukan kecurangan sebanyak ' . $counter . ' kali.',
            ], 403);
        }

        $remainingTolerance = max(0, self::MAX_VIOLATIONS - $counter);

        return response()->json([
            'success'             => true,
            'warned'              => true,
            'terminated'          => false,
            'violations'          => $counter,
            'remaining_tolerance' => $remainingTolerance,
            'max_violations'      => self::MAX_VIOLATIONS,
            'message'             => "Peringatan! Terdeteksi aktivitas keluar dari layar ujian ({$violationType}). Sisa toleransi: {$remainingTolerance} kali sebelum ujian dibatalkan otomatis.",
        ]);
    }

    /**
     * D. syncTimer(Request $request, $jadwalId)
     *
     * - Endpoint heartbeat browser setiap 30-60 detik.
     * - Memperbarui sisa waktu di Redis dan drift correction dengan waktu server resmi now()->timestamp.
     *
     * Route: POST /api/cbt/sync-timer/{jadwalId} atau GET /api/cbt/sync-timer/{jadwalId}
     */
    public function syncTimer(Request $request, $jadwalId): JsonResponse
    {
        $jadwalId = (int) $jadwalId;
        $siswa = $this->getAuthenticatedSiswa($request);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi peserta diperlukan.',
            ], 401);
        }

        $siswaId = (int) $siswa->id;

        // Cek apakah ujian terminated
        $isTerminated = (int) ExamRedisBuffer::get("cbt_exam_terminated:{$jadwalId}:{$siswaId}");
        if ($isTerminated === 1) {
            return response()->json([
                'success'    => false,
                'terminated' => true,
                'message'    => 'Ujian telah dibatalkan otomatis karena pelanggaran.',
            ], 403);
        }

        $ujianSiswa = CbtUjianSiswa::where('jadwal_id', $jadwalId)->where('siswa_id', $siswaId)->first();
        $jadwal = CbtJadwalClean::find($jadwalId);

        if (!$jadwal) {
            $legacyJadwal = \App\Models\CbtJadwal::find($jadwalId);
            $durasiMenit = $legacyJadwal ? (int) $legacyJadwal->durasi_ujian : 90;
        } else {
            $durasiMenit = (int) $jadwal->durasi_menit;
        }

        if ($ujianSiswa && $ujianSiswa->status === 2) {
            return response()->json([
                'success'     => true,
                'status'      => 'selesai',
                'sisa_detik'  => 0,
                'server_time' => now()->timestamp,
            ]);
        }

        // Kalkulasi sisa detik server
        $extraMinutes = (int) ExamRedisBuffer::get("cbt_extra_time:{$jadwalId}:{$siswaId}");
        $totalDurasiDetik = ($durasiMenit + $extraMinutes) * 60;

        $waktuMulaiTs = $ujianSiswa?->waktu_mulai ? $ujianSiswa->waktu_mulai->timestamp : time();
        $elapsedDetik = max(0, time() - $waktuMulaiTs);
        $sisaDetik = max(0, $totalDurasiDetik - $elapsedDetik);

        // Update sisa detik di Redis key cbt_timer
        ExamRedisBuffer::set("cbt_timer:{$jadwalId}:{$siswaId}", $sisaDetik, $totalDurasiDetik + 7200);

        if ($sisaDetik <= 0) {
            $this->finishExamSession($jadwalId, $siswaId);
            return response()->json([
                'success'     => true,
                'status'      => 'habis',
                'sisa_detik'  => 0,
                'server_time' => now()->timestamp,
                'message'     => 'Waktu pengerjaan ujian telah habis.',
            ]);
        }

        return response()->json([
            'success'     => true,
            'status'      => 'aktif',
            'sisa_detik'  => $sisaDetik,
            'server_time' => now()->timestamp,
        ]);
    }

    /**
     * Submit Akhir Pengerjaan Ujian.
     *
     * Route: POST /api/cbt/finish/{jadwalId}
     */
    public function finishExam(Request $request, $jadwalId): JsonResponse
    {
        $jadwalId = (int) $jadwalId;
        $siswa = $this->getAuthenticatedSiswa($request);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi peserta diperlukan.',
            ], 401);
        }

        $siswaId = (int) $siswa->id;

        try {
            $result = $this->finishExamSession($jadwalId, $siswaId);

            return response()->json([
                'success'      => true,
                'message'      => 'Ujian berhasil diselesaikan dan jawaban telah disimpan secara permanen.',
                'total_nilai'  => $result['tampil_hasil'] ? $result['total_nilai'] : null,
                'tampil_hasil' => $result['tampil_hasil'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan ujian: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Internal: Batch sinkronisasi jawaban dari Redis ke database & evaluasi nilai.
     */
    protected function finishExamSession(int $jadwalId, int $siswaId): array
    {
        // 1. Ambil semua jawaban dari Redis Hash
        $savedAnswers = ExamRedisBuffer::hgetall("cbt_answers:{$jadwalId}:{$siswaId}");
        if (empty($savedAnswers)) {
            $savedAnswers = ExamRedisBuffer::hgetall("cbt_jawaban:{$jadwalId}:{$siswaId}");
        }

        $jadwalClean = CbtJadwalClean::with('bankSoal.soal')->find($jadwalId);
        $totalNilai = 0.0;
        $tampilHasil = false;

        if ($jadwalClean && $jadwalClean->bankSoal) {
            $tampilHasil = (bool) $jadwalClean->tampilkan_nilai;
            $soals = $jadwalClean->bankSoal->soal;

            // Evaluasi skor per butir soal
            $totalBobotBenar = 0.0;
            $totalBobotSoal = 0.0;

            foreach ($soals as $soal) {
                $soalId = (int) $soal->id;
                $bobot = (float) ($soal->bobot ?: 1.0);
                $totalBobotSoal += $bobot;

                $userAnsJson = $savedAnswers[$soalId] ?? null;
                $userAnsData = $userAnsJson ? json_decode($userAnsJson, true) : null;
                $userAnswer = $userAnsData['jawaban'] ?? null;
                $ragu = (bool) ($userAnsData['ragu'] ?? false);

                // Cek kebenaran jawaban
                $kunci = $soal->kunci_jawaban;
                $isBenar = false;

                if ($kunci !== null) {
                    if (is_array($kunci)) {
                        $isBenar = ($userAnswer !== null && in_array((string)$userAnswer, array_map('strval', $kunci), true));
                    } else {
                        $isBenar = (strcasecmp(trim((string)$userAnswer), trim((string)$kunci)) === 0);
                    }
                }

                $skorButir = $isBenar ? $bobot : 0.0;
                $totalBobotBenar += $skorButir;

                // Batch upsert ke cbt_jawaban_siswa
                try {
                    CbtJawabanSiswa::updateOrCreate(
                        ['jadwal_id' => $jadwalId, 'siswa_id' => $siswaId, 'soal_id' => $soalId],
                        [
                            'jawaban'    => $userAnswer ? (is_array($userAnswer) ? $userAnswer : [$userAnswer]) : null,
                            'ragu'       => $ragu,
                            'skor_butir' => $skorButir,
                        ]
                    );
                } catch (\Throwable $e) {}
            }

            // Hitung nilai akhir skala 100
            $totalNilai = ($totalBobotSoal > 0) ? round(($totalBobotBenar / $totalBobotSoal) * 100, 2) : 0.0;

            // Update status pengerjaan cbt_ujian_siswa
            try {
                CbtUjianSiswa::updateOrCreate(
                    ['jadwal_id' => $jadwalId, 'siswa_id' => $siswaId],
                    [
                        'status'        => 2, // selesai
                        'waktu_selesai' => now(),
                        'sisa_detik'    => 0,
                        'nilai_akhir'   => $totalNilai,
                    ]
                );
            } catch (\Throwable $e) {}
        } else {
            // Panggil legacy service jika jadwal berada di tabel legacy
            try {
                $legacyRes = $this->legacyExamService->finishExam($jadwalId, $siswaId);
                $totalNilai = (float) ($legacyRes['total_nilai'] ?? 0);
                $tampilHasil = (bool) ($legacyRes['tampil_hasil'] ?? false);
            } catch (\Throwable $e) {}
        }

        // Bersihkan state Redis siswa
        ExamRedisBuffer::del("cbt_answers:{$jadwalId}:{$siswaId}");
        ExamRedisBuffer::del("cbt_jawaban:{$jadwalId}:{$siswaId}");
        ExamRedisBuffer::del("cbt_timer:{$jadwalId}:{$siswaId}");
        ExamRedisBuffer::del("cbt_exam_terminated:{$jadwalId}:{$siswaId}");
        ExamRedisBuffer::del("cbt_device_lock:{$siswaId}");

        return [
            'total_nilai'  => $totalNilai,
            'tampil_hasil' => $tampilHasil,
        ];
    }

    /**
     * Algoritma Deterministic Seeded Shuffle (Fisher-Yates dengan LCG).
     * Urutan selalu konsisten untuk seed yang sama.
     */
    protected function seededShuffle(array &$items, int $seed): void
    {
        $count = count($items);
        if ($count <= 1) {
            return;
        }

        $currentSeed = $seed;
        for ($i = $count - 1; $i > 0; $i--) {
            $currentSeed = (1103515245 * $currentSeed + 12345) & 0x7fffffff;
            $j = $currentSeed % ($i + 1);

            $temp = $items[$i];
            $items[$i] = $items[$j];
            $items[$j] = $temp;
        }
    }
}
