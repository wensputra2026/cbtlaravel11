<?php

namespace App\Services\Proctor;

use App\Models\CbtDurasiSiswa;
use App\Models\CbtJadwal;
use App\Models\CbtNilai;
use App\Models\KelasSiswa;
use App\Models\MasterSiswa;
use App\Services\Auth\LegacyAuthService;
use App\Services\Exam\ExamSessionService;
use Illuminate\Support\Facades\Redis;

class ProctorService
{
    public function __construct(
        protected ExamSessionService $examSessionService,
        protected LegacyAuthService $authService
    ) {}

    /**
     * Memonitor status seluruh peserta ujian secara real-time.
     */
    public function getLiveMonitoring(int $jadwalId): array
    {
        $jadwal = CbtJadwal::with('bankSoal')->findOrFail($jadwalId);
        $bank = $jadwal->bankSoal;
        $kelasIds = $bank->kelas_ids;

        // Ambil siswa-siswa yang terdaftar di kelas-kelas yang diujikan
        $siswaQuery = MasterSiswa::with(['user', 'kelasSiswa.kelas']);
        if (!empty($kelasIds)) {
            $siswaIds = KelasSiswa::whereIn('id_kelas', $kelasIds)->pluck('id_siswa')->toArray();
            $siswaQuery->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $siswaQuery->orderBy('nama', 'asc')->get();

        $durasis = CbtDurasiSiswa::where('id_jadwal', $jadwalId)->get()->keyBy('id_siswa');
        $nilais = CbtNilai::where('id_jadwal', $jadwalId)->get()->keyBy('id_siswa');

        $totalSoal = (int) ($bank->tampil_pg + $bank->tampil_kompleks + $bank->tampil_jodohkan + $bank->tampil_isian + $bank->tampil_esai);
        $listPeserta = [];

        foreach ($siswas as $siswa) {
            $siswaId = $siswa->id_siswa;
            $durasi = $durasis->get($siswaId);
            $nilai = $nilais->get($siswaId);

            $statusUjian = 'Belum Mulai';
            $statusCode = 0;
            $sisaMenit = (int) $jadwal->durasi_ujian;
            $mulai = '-';
            $selesai = '-';

            if ($durasi) {
                $statusCode = (int) $durasi->status;
                $mulai = $durasi->mulai ?? '-';
                $selesai = $durasi->selesai ?? '-';

                if ($durasi->status === CbtDurasiSiswa::STATUS_SEDANG) {
                    $statusUjian = 'Sedang Ujian';
                    $waktuMulai = strtotime($durasi->mulai);
                    $totalDurasi = (int) $jadwal->durasi_ujian;
                    try {
                        $extra = (int) Redis::get("cbt_extra_time:{$jadwalId}:{$siswaId}");
                        $totalDurasi += $extra;
                    } catch (\Throwable $e) {
                    }
                    $sisaDetik = ($waktuMulai + ($totalDurasi * 60)) - time();
                    $sisaMenit = max(0, (int) ceil($sisaDetik / 60));
                    if ($sisaDetik <= 0) {
                        $statusUjian = 'Waktu Habis';
                    }
                } elseif ($durasi->status === CbtDurasiSiswa::STATUS_SELESAI) {
                    $statusUjian = 'Selesai';
                    $sisaMenit = 0;
                }
            }

            // Hitung jawaban yang sudah tersimpan di Redis
            $terjawabCount = 0;
            try {
                $terjawabCount = (int) Redis::hlen("cbt_jawaban:{$jadwalId}:{$siswaId}");
            } catch (\Throwable $e) {
            }

            // Pelanggaran anti-cheat di Redis
            $pelanggaran = 0;
            try {
                $pelanggaran = (int) Redis::get("cbt_violation:{$jadwalId}:{$siswaId}");
            } catch (\Throwable $e) {
            }

            // Cek device lock
            $isDeviceLocked = false;
            if ($siswa->user) {
                try {
                    $isDeviceLocked = (bool) Redis::get("cbt_device_lock:{$siswa->user->id}");
                } catch (\Throwable $e) {
                }
            }

            $listPeserta[] = [
                'id_siswa'       => $siswaId,
                'nisn'           => $siswa->nisn,
                'nis'            => $siswa->nis,
                'nama'           => $siswa->nama,
                'kelas'          => $siswa->kelasSiswa->first()->kelas->nama_kelas ?? '-',
                'status_code'    => $statusCode,
                'status_text'    => $statusUjian,
                'mulai'          => $mulai,
                'selesai'        => $selesai,
                'sisa_menit'     => $sisaMenit,
                'terjawab'       => $terjawabCount,
                'total_soal'     => $totalSoal,
                'pelanggaran'    => $pelanggaran,
                'device_locked'  => $isDeviceLocked,
                'skor_pg'        => $nilai->pg_nilai ?? '-',
                'skor_total'     => $nilai ? (round($nilai->pg_nilai + $nilai->kompleks_nilai + $nilai->jodohkan_nilai + $nilai->isian_nilai + $nilai->essai_nilai, 2)) : '-',
            ];
        }

        $totalPeserta = count($listPeserta);
        $mengerjakan = count(array_filter($listPeserta, fn($p) => $p['status_code'] === 1));
        $selesai = count(array_filter($listPeserta, fn($p) => $p['status_code'] === 2));
        $pelanggaran = count(array_filter($listPeserta, fn($p) => $p['pelanggaran'] > 0));

        return [
            'jadwal' => [
                'id_jadwal'    => $jadwal->id_jadwal,
                'nama_ujian'   => $bank->bank_nama,
                'kode_bank'    => $bank->bank_kode,
                'mapel'        => $bank->mapel->nama_mapel ?? '-',
                'durasi'       => (int) $jadwal->durasi_ujian,
                'durasi_menit' => (int) $jadwal->durasi_ujian,
            ],
            'statistik' => [
                'total'        => $totalPeserta,
                'mengerjakan'  => $mengerjakan,
                'selesai'      => $selesai,
                'pelanggaran'  => $pelanggaran,
            ],
            'summary' => [
                'total_peserta' => $totalPeserta,
                'belum_mulai'   => count(array_filter($listPeserta, fn($p) => $p['status_code'] === 0)),
                'sedang_ujian'  => $mengerjakan,
                'selesai'       => $selesai,
                'pelanggaran'   => $pelanggaran,
            ],
            'peserta' => $listPeserta,
        ];
    }

    /**
     * Reset Login Peserta (membuka kunci perangkat agar siswa bisa login di PC lain).
     */
    public function resetStudentLogin(int $jadwalId, int $siswaId): bool
    {
        $siswa = MasterSiswa::with('user')->find($siswaId);
        if ($siswa && $siswa->user) {
            $this->authService->forceUnlockDevice($siswa->user->id);
        }

        // Reset status pelanggaran di Redis jika diminta
        try {
            Redis::del("cbt_violation:{$jadwalId}:{$siswaId}");
        } catch (\Throwable $e) {
        }

        return true;
    }

    /**
     * Memaksa seorang siswa mengakhiri ujian (Force Submit).
     */
    public function forceSubmit(int $jadwalId, int $siswaId): array
    {
        return $this->examSessionService->finishExam($jadwalId, $siswaId);
    }

    /**
     * Memberikan waktu tambahan khusus untuk siswa tertentu (misal: jika PC sempat bermasalah).
     */
    public function addExtraTime(int $jadwalId, int $siswaId, int $extraMinutes): int
    {
        $redisKey = "cbt_extra_time:{$jadwalId}:{$siswaId}";
        try {
            $current = (int) Redis::get($redisKey);
            $newTotal = $current + $extraMinutes;
            Redis::setex($redisKey, 86400, $newTotal);
            return $newTotal;
        } catch (\Throwable $e) {
            return $extraMinutes;
        }
    }
}
