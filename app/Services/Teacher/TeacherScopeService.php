<?php

namespace App\Services\Teacher;

use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\KelasSiswa;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherScopeService
{
    /**
     * Dapatkan profil guru yang sedang login atau dari objek user tertentu.
     */
    public function getTeacherProfile(?User $user = null): ?MasterGuru
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return null;
        }

        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Dapatkan rincian penugasan mengajar, wali kelas, dan rombel guru pada TP & SMT aktif.
     */
    public function getTeacherAssignment(?MasterGuru $guru = null, ?int $tpId = null, ?int $smtId = null): array
    {
        $guru = $guru ?? $this->getTeacherProfile();
        if (!$guru) {
            return $this->getEmptyAssignment();
        }

        $activeTp = $tpId ? MasterTp::find($tpId) : (MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first());
        $activeSmt = $smtId ? MasterSmt::find($smtId) : (MasterSmt::activeSmt() ?? MasterSmt::orderBy('id_smt', 'desc')->first());
        $tpId = $activeTp?->id_tp ?? 1;
        $smtId = $activeSmt?->id_smt ?? 1;

        // Ambil data penugasan dari jabatan_guru
        $jabatan = DB::table('jabatan_guru')
            ->where('id_guru', $guru->id_guru)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->first();

        // 1. Logika Penentuan Wali Kelas
        $isWaliKelas = false;
        $waliKelasId = null;
        $waliKelas = null;
        $waliKelasCount = 0;

        if ($jabatan && (int)$jabatan->id_jabatan === 4 && (int)$jabatan->id_kelas > 0) {
            $isWaliKelas = true;
            $waliKelasId = (int)$jabatan->id_kelas;
        } else {
            // Fallback: cek apakah terdaftar di master_kelas.guru_id
            $kelasAsWali = MasterKelas::where('guru_id', $guru->id_guru)
                ->where('id_tp', $tpId)
                ->where('id_smt', $smtId)
                ->first();
            if ($kelasAsWali) {
                $isWaliKelas = true;
                $waliKelasId = $kelasAsWali->id_kelas;
            }
        }

        if ($isWaliKelas && $waliKelasId) {
            $waliKelas = MasterKelas::with('jurusan')->find($waliKelasId);
            if ($waliKelas) {
                $waliKelasCount = DB::table('kelas_siswa')
                    ->where('id_kelas', $waliKelasId)
                    ->where('id_tp', $tpId)
                    ->where('id_smt', $smtId)
                    ->count();
            } else {
                $isWaliKelas = false;
            }
        }

        // 2. Logika Parsing Mata Pelajaran & Kelas Diampu (mapel_kelas JSON)
        $rawMapelKelas = [];
        if ($jabatan && !empty($jabatan->mapel_kelas)) {
            $decoded = json_decode($jabatan->mapel_kelas, true);
            if (is_array($decoded)) {
                $rawMapelKelas = $decoded;
            }
        }

        $assignedMapelIds = [];
        $assignedKelasIds = [];
        $mapelDetails = [];

        foreach ($rawMapelKelas as $item) {
            if (!empty($item['id_mapel'])) {
                $mId = (int)$item['id_mapel'];
                $assignedMapelIds[] = $mId;

                $targetKelasIds = [];
                if (!empty($item['kelas_mapel']) && is_array($item['kelas_mapel'])) {
                    foreach ($item['kelas_mapel'] as $km) {
                        if (!empty($km['kelas'])) {
                            $kId = (int)$km['kelas'];
                            $targetKelasIds[] = $kId;
                            $assignedKelasIds[] = $kId;
                        }
                    }
                }

                $mapelDetails[$mId] = [
                    'id_mapel'   => $mId,
                    'nama_mapel' => $item['nama_mapel'] ?? '',
                    'kelas_ids'  => array_unique($targetKelasIds),
                ];
            }
        }

        $assignedMapelIds = array_values(array_unique($assignedMapelIds));
        $assignedKelasIds = array_values(array_unique($assignedKelasIds));

        // Ambil model MasterMapel dan MasterKelas yang diampu
        $assignedMapels = MasterMapel::whereIn('id_mapel', $assignedMapelIds)
            ->orderBy('nama_mapel', 'asc')
            ->get();

        $assignedClasses = MasterKelas::whereIn('id_kelas', $assignedKelasIds)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        // 3. Hitung total siswa unik yang diajar oleh guru ini
        $totalSiswaDiampu = 0;
        if (!empty($assignedKelasIds)) {
            $totalSiswaDiampu = DB::table('kelas_siswa')
                ->whereIn('id_kelas', $assignedKelasIds)
                ->where('id_tp', $tpId)
                ->where('id_smt', $smtId)
                ->distinct('id_siswa')
                ->count('id_siswa');
        }

        return [
            'guru'                => $guru,
            'active_tp'           => $activeTp,
            'active_smt'          => $activeSmt,
            'is_wali_kelas'       => $isWaliKelas,
            'wali_kelas_id'       => $waliKelasId,
            'wali_kelas'          => $waliKelas,
            'wali_kelas_siswa_count' => $waliKelasCount,
            'raw_mapel_kelas'     => $rawMapelKelas,
            'assigned_mapel_ids'  => $assignedMapelIds,
            'assigned_kelas_ids'  => $assignedKelasIds,
            'mapel_details'       => $mapelDetails,
            'assigned_mapels'     => $assignedMapels,
            'assigned_classes'    => $assignedClasses,
            'total_siswa_diampu'  => $totalSiswaDiampu,
        ];
    }

    /**
     * Cek apakah guru berhak mengakses data kelas tertentu.
     */
    public function canAccessKelas(int $guruId, int $kelasId, ?int $tpId = null, ?int $smtId = null): bool
    {
        $guru = MasterGuru::find($guruId);
        $assignment = $this->getTeacherAssignment($guru, $tpId, $smtId);

        // Jika wali kelas dari kelas ini
        if ($assignment['is_wali_kelas'] && $assignment['wali_kelas_id'] === $kelasId) {
            return true;
        }

        // Jika mengajar mapel di kelas ini
        return in_array($kelasId, $assignment['assigned_kelas_ids'], true);
    }

    /**
     * Cek apakah guru berhak mengampu mata pelajaran tertentu.
     */
    public function canAccessMapel(int $guruId, int $mapelId, ?int $tpId = null, ?int $smtId = null): bool
    {
        $guru = MasterGuru::find($guruId);
        $assignment = $this->getTeacherAssignment($guru, $tpId, $smtId);

        return in_array($mapelId, $assignment['assigned_mapel_ids'], true);
    }

    /**
     * Format penugasan kosong untuk fallback.
     */
    protected function getEmptyAssignment(): array
    {
        return [
            'guru'                => null,
            'active_tp'           => null,
            'active_smt'          => null,
            'is_wali_kelas'       => false,
            'wali_kelas_id'       => null,
            'wali_kelas'          => null,
            'wali_kelas_siswa_count' => 0,
            'raw_mapel_kelas'     => [],
            'assigned_mapel_ids'  => [],
            'assigned_kelas_ids'  => [],
            'mapel_details'       => [],
            'assigned_mapels'     => collect(),
            'assigned_classes'    => collect(),
            'total_siswa_diampu'  => 0,
        ];
    }
}
