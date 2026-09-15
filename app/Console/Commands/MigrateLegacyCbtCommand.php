<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyCbtCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cbt:migrate-legacy {--connection= : Nama koneksi database legacy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi ETL data legacy Garuda CBT (CodeIgniter 3) ke skema bersih Laravel 11 dengan normalisasi native JSON';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('   MIGRASI DATA LEGACY GARUDA CBT KE SKEMA BERSIH LARAVEL 11    ');
        $this->info('================================================================');

        $legacyConn = $this->option('connection') ?: 'mysql';

        // 1. Verifikasi ketersediaan tabel lama
        if (!Schema::connection($legacyConn)->hasTable('users')) {
            $this->error("Koneksi [{$legacyConn}] tidak memiliki tabel 'users'!");
            return Command::FAILURE;
        }

        // Jalankan migrasi skema baru terlebih dahulu jika belum ada
        $this->info("\n[1/6] Memeriksa & Menjalankan Migrasi Skema Bersih...");
        $this->call('migrate', ['--force' => true]);

        // Fase 1: Migrasi Users & Penentuan Role
        $this->info("\n[2/6] Memetakan Role Pengguna (Users)...");
        $this->migrateUsers($legacyConn);

        // Fase 2: Tabel Referensi (Kelas, Mapel, Sesi, Ruang)
        $this->info("\n[3/6] Memigrasikan Data Referensi (Kelas, Mapel, Sesi, Ruang)...");
        $this->migrateReferences($legacyConn);

        // Fase 3: Data Siswa
        $this->info("\n[4/6] Memigrasikan Data Siswa...");
        $this->migrateSiswa($legacyConn);

        // Fase 4: Bank Soal & Soal (Normalisasi PHP Serialized ke Native JSON)
        $this->info("\n[5/6] Memigrasikan Bank Soal & Butir Soal (Normalisasi JSON)...");
        $this->migrateBankAndSoal($legacyConn);

        // Fase 5: Jadwal Ujian
        $this->info("\n[6/6] Memigrasikan Jadwal Ujian...");
        $this->migrateJadwal($legacyConn);

        $this->info("\n================================================================");
        $this->info('   MIGRASI ETL GARUDA CBT BERHASIL DISELESAIKAN 100%!           ');
        $this->info("================================================================\n");

        return Command::SUCCESS;
    }

    protected function migrateUsers(string $conn): void
    {
        $hasUsersGroups = Schema::connection($conn)->hasTable('users_groups');
        
        $users = DB::connection($conn)->table('users')->get();
        $updated = 0;

        foreach ($users as $user) {
            $role = 'siswa';

            if ($hasUsersGroups) {
                $groupId = DB::connection($conn)->table('users_groups')
                    ->where('user_id', $user->id)
                    ->value('group_id');

                if ($groupId == 1) {
                    $role = 'admin';
                } elseif ($groupId == 2) {
                    $role = 'guru';
                }
            }

            DB::table('users')->where('id', $user->id)->update([
                'role' => $role,
                'created_at' => $user->created_at ?? now(),
                'updated_at' => $user->updated_at ?? now(),
            ]);
            $updated++;
        }

        $this->line("  -> {$updated} akun pengguna diperbarui dengan role.");
    }

    protected function migrateReferences(string $conn): void
    {
        // 1. ref_kelas
        if (Schema::connection($conn)->hasTable('master_kelas')) {
            $kelasList = DB::connection($conn)->table('master_kelas')->get();
            foreach ($kelasList as $k) {
                DB::table('ref_kelas')->updateOrInsert(
                    ['id' => $k->id_kelas],
                    [
                        'kode_kelas' => $k->kode_kelas ?: ('KELAS_' . $k->id_kelas),
                        'nama_kelas' => $k->nama_kelas,
                        'tingkat' => is_numeric($k->level_id) ? (int)$k->level_id : 10,
                        'jurusan' => (string)($k->jurusan_id ?? ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->line("  -> Ref Kelas: " . count($kelasList) . " baris dimigrasikan.");
        }

        // 2. ref_mapel
        if (Schema::connection($conn)->hasTable('master_mapel')) {
            $mapelList = DB::connection($conn)->table('master_mapel')->get();
            foreach ($mapelList as $m) {
                DB::table('ref_mapel')->updateOrInsert(
                    ['id' => $m->id_mapel],
                    [
                        'kode_mapel' => $m->kode ?: ('MAPEL_' . $m->id_mapel),
                        'nama_mapel' => $m->nama_mapel,
                        'kelompok' => $m->kelompok ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->line("  -> Ref Mapel: " . count($mapelList) . " baris dimigrasikan.");
        }

        // 3. ref_sesi
        if (Schema::connection($conn)->hasTable('cbt_sesi')) {
            $sesiList = DB::connection($conn)->table('cbt_sesi')->get();
            foreach ($sesiList as $s) {
                DB::table('ref_sesi')->updateOrInsert(
                    ['id' => $s->id_sesi],
                    [
                        'kode_sesi' => $s->kode_sesi ?: ('SESI_' . $s->id_sesi),
                        'nama_sesi' => $s->nama_sesi,
                        'waktu_mulai' => $s->waktu_mulai ?? null,
                        'waktu_selesai' => $s->waktu_akhir ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->line("  -> Ref Sesi: " . count($sesiList) . " baris dimigrasikan.");
        }

        // 4. ref_ruang
        if (Schema::connection($conn)->hasTable('cbt_ruang')) {
            $ruangList = DB::connection($conn)->table('cbt_ruang')->get();
            foreach ($ruangList as $r) {
                DB::table('ref_ruang')->updateOrInsert(
                    ['id' => $r->id_ruang],
                    [
                        'kode_ruang' => $r->kode_ruang ?: ('RUANG_' . $r->id_ruang),
                        'nama_ruang' => $r->nama_ruang,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->line("  -> Ref Ruang: " . count($ruangList) . " baris dimigrasikan.");
        }
    }

    protected function migrateSiswa(string $conn): void
    {
        if (!Schema::connection($conn)->hasTable('master_siswa')) {
            $this->line("  -> Tabel master_siswa tidak ditemukan.");
            return;
        }

        $siswaList = DB::connection($conn)->table('master_siswa')->get();
        $count = 0;

        foreach ($siswaList as $s) {
            // Cari user_id berdasarkan username siswa
            $userId = DB::table('users')->where('username', $s->username)->value('id');

            // Cek sesi & ruang jika ada di cbt_sesi_siswa
            $sesiId = null;
            $ruangId = null;
            if (Schema::connection($conn)->hasTable('cbt_sesi_siswa')) {
                $sesiRow = DB::connection($conn)->table('cbt_sesi_siswa')
                    ->where('siswa_id', $s->id_siswa)
                    ->first();
                if ($sesiRow) {
                    $sesiId = is_numeric($sesiRow->sesi_id) ? (int)$sesiRow->sesi_id : null;
                    $ruangId = is_numeric($sesiRow->ruang_id) ? (int)$sesiRow->ruang_id : null;
                }
            }

            // Validasi foreign key eksistensi
            if ($sesiId && !DB::table('ref_sesi')->where('id', $sesiId)->exists()) {
                $sesiId = null;
            }
            if ($ruangId && !DB::table('ref_ruang')->where('id', $ruangId)->exists()) {
                $ruangId = null;
            }
            $kelasId = is_numeric($s->kelas_awal) ? (int)$s->kelas_awal : null;
            if ($kelasId && !DB::table('ref_kelas')->where('id', $kelasId)->exists()) {
                $kelasId = null;
            }

            DB::table('siswa')->updateOrInsert(
                ['id' => $s->id_siswa],
                [
                    'user_id' => $userId,
                    'nis' => $s->nis ?: ('NIS_' . $s->id_siswa),
                    'nisn' => $s->nisn ? (string)$s->nisn : null,
                    'nama_lengkap' => $s->nama,
                    'jenis_kelamin' => strtoupper(substr($s->jenis_kelamin ?: 'L', 0, 1)),
                    'kelas_id' => $kelasId,
                    'sesi_id' => $sesiId,
                    'ruang_id' => $ruangId,
                    'foto' => $s->foto ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        $this->line("  -> Siswa: {$count} siswa berhasil dipetakan.");
    }

    protected function migrateBankAndSoal(string $conn): void
    {
        if (!Schema::connection($conn)->hasTable('cbt_bank_soal')) {
            $this->line("  -> Tabel cbt_bank_soal tidak ditemukan.");
            return;
        }

        $banks = DB::connection($conn)->table('cbt_bank_soal')->get();
        $bankCount = 0;
        $soalCount = 0;

        foreach ($banks as $b) {
            $alokasiKelas = $this->normalizeValue($b->bank_kelas);
            $mapelId = is_numeric($b->bank_mapel_id) ? (int)$b->bank_mapel_id : null;
            if ($mapelId && !DB::table('ref_mapel')->where('id', $mapelId)->exists()) {
                $mapelId = null;
            }

            DB::table('cbt_bank_soal_clean')->updateOrInsert(
                ['id' => $b->id_bank],
                [
                    'kode_bank' => $b->bank_kode ?: ('BANK_' . $b->id_bank),
                    'mapel_id' => $mapelId,
                    'guru_id' => is_numeric($b->bank_guru_id) ? (int)$b->bank_guru_id : null,
                    'nama_bank' => $b->bank_nama ?: ('Bank Soal ' . $b->id_bank),
                    'tingkat' => is_numeric($b->bank_level) ? (int)$b->bank_level : 10,
                    'alokasi_kelas' => json_encode($alokasiKelas),
                    'jml_pg' => (int)($b->tampil_pg ?: $b->jml_soal ?: 0),
                    'jml_kompleks' => (int)($b->jml_kompleks ?? 0),
                    'jml_jodoh' => (int)($b->jml_jodohkan ?? 0),
                    'jml_isian' => (int)($b->jml_isian ?? 0),
                    'jml_esai' => (int)($b->jml_esai ?? 0),
                    'bobot_pg' => (float)($b->bobot_pg ?? 0),
                    'bobot_kompleks' => (float)($b->bobot_kompleks ?? 0),
                    'bobot_jodoh' => (float)($b->bobot_jodohkan ?? 0),
                    'bobot_isian' => (float)($b->bobot_isian ?? 0),
                    'bobot_esai' => (float)($b->bobot_esai ?? 0),
                    'kkm' => (int)($b->kkm ?? 75),
                    'status' => ($b->status == 1) ? 'aktif' : 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $bankCount++;

            // Migrasikan butir soal untuk bank ini
            if (Schema::connection($conn)->hasTable('cbt_soal')) {
                $soals = DB::connection($conn)->table('cbt_soal')->where('bank_id', $b->id_bank)->get();
                foreach ($soals as $s) {
                    $opsi = [];
                    if (!empty($s->opsi_a)) $opsi['A'] = $s->opsi_a;
                    if (!empty($s->opsi_b)) $opsi['B'] = $s->opsi_b;
                    if (!empty($s->opsi_c)) $opsi['C'] = $s->opsi_c;
                    if (!empty($s->opsi_d)) $opsi['D'] = $s->opsi_d;
                    if (!empty($s->opsi_e)) $opsi['E'] = $s->opsi_e;

                    // Kunci jawaban: normalisasi serialized string atau teks biasa
                    $kunci = $this->normalizeValue($s->jawaban);

                    DB::table('cbt_soal_clean')->updateOrInsert(
                        ['id' => $s->id_soal],
                        [
                            'bank_id' => $b->id_bank,
                            'nomor_urut' => (int)($s->nomor_soal ?? 1),
                            'jenis_soal' => (int)($s->jenis ?? 1),
                            'pertanyaan' => $s->soal ?? '',
                            'opsi' => json_encode($opsi),
                            'kunci_jawaban' => json_encode($kunci),
                            'media' => $s->file ?? $s->file1 ?? null,
                            'bobot' => 1.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                    $soalCount++;
                }
            }
        }

        $this->line("  -> Bank Soal: {$bankCount} bank, {$soalCount} butir soal dinormalisasi ke JSON.");
    }

    protected function migrateJadwal(string $conn): void
    {
        if (!Schema::connection($conn)->hasTable('cbt_jadwal')) {
            $this->line("  -> Tabel cbt_jadwal tidak ditemukan.");
            return;
        }

        $jadwals = DB::connection($conn)->table('cbt_jadwal')->get();
        $count = 0;

        foreach ($jadwals as $j) {
            // Pastikan bank soal ada di cbt_bank_soal_clean
            if (!DB::table('cbt_bank_soal_clean')->where('id', $j->id_bank)->exists()) {
                continue;
            }

            DB::table('cbt_jadwal_clean')->updateOrInsert(
                ['id' => $j->id_jadwal],
                [
                    'bank_id' => $j->id_bank,
                    'nama_ujian' => 'Ujian Jadwal #' . $j->id_jadwal,
                    'kode_jenis' => 'PAT',
                    'waktu_mulai' => $j->tgl_mulai ? date('Y-m-d H:i:s', strtotime($j->tgl_mulai)) : now(),
                    'waktu_selesai' => $j->tgl_selesai ? date('Y-m-d H:i:s', strtotime($j->tgl_selesai)) : now()->addHours(2),
                    'durasi_menit' => (int)($j->durasi_ujian ?? 90),
                    'acak_soal' => (bool)($j->acak_soal ?? 1),
                    'acak_opsi' => (bool)($j->acak_opsi ?? 1),
                    'pakai_token' => (bool)($j->token ?? 1),
                    'token_default' => null,
                    'tampilkan_nilai' => (bool)($j->hasil_tampil ?? 0),
                    'status' => (int)($j->status ?? 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        $this->line("  -> Jadwal: {$count} jadwal ujian dimigrasikan.");
    }

    /**
     * Parse serialisasi PHP atau JSON string menjadi array PHP standar
     */
    protected function normalizeValue(mixed $value): array
    {
        if (empty($value)) return [];
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $trimmed = trim($value);
            // Cek PHP Serialized
            if (str_starts_with($trimmed, 'a:') || str_starts_with($trimmed, 'O:') || str_starts_with($trimmed, 's:')) {
                $unserialized = @unserialize($trimmed);
                if ($unserialized !== false) {
                    return is_array($unserialized) ? $unserialized : [$unserialized];
                }
            }

            // Cek JSON
            $json = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($json) ? $json : [$json];
            }

            // Cek Comma Separated
            if (str_contains($trimmed, ',')) {
                return array_map('trim', explode(',', $trimmed));
            }

            return [$trimmed];
        }

        return [(string)$value];
    }
}
