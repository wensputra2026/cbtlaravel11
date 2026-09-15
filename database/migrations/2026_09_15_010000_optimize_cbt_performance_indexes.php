<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dapatkan daftar nama index tabel di MySQL.
     */
    protected function getExistingIndexes(string $table): array
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}`");
            return collect($indexes)->pluck('Key_name')->unique()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Composite Index pada cbt_ujian_siswa: ['jadwal_id', 'siswa_id', 'status']
        if (Schema::hasTable('cbt_ujian_siswa')) {
            $existing = $this->getExistingIndexes('cbt_ujian_siswa');
            if (!in_array('idx_ujian_jadwal_siswa_status', $existing)) {
                Schema::table('cbt_ujian_siswa', function (Blueprint $table) {
                    $table->index(['jadwal_id', 'siswa_id', 'status'], 'idx_ujian_jadwal_siswa_status');
                });
            }
        }

        // 2. Composite Index pada cbt_jawaban_siswa: ['jadwal_id', 'siswa_id', 'soal_id']
        if (Schema::hasTable('cbt_jawaban_siswa')) {
            $existing = $this->getExistingIndexes('cbt_jawaban_siswa');
            if (!in_array('idx_jawaban_composite_lookup', $existing) && !in_array('uk_jadwal_siswa_soal', $existing)) {
                Schema::table('cbt_jawaban_siswa', function (Blueprint $table) {
                    $table->index(['jadwal_id', 'siswa_id', 'soal_id'], 'idx_jawaban_composite_lookup');
                });
            }
        }

        // 3. Composite Index pada cbt_soal: ['bank_id', 'nomor_soal']
        if (Schema::hasTable('cbt_soal')) {
            $existing = $this->getExistingIndexes('cbt_soal');
            if (!in_array('idx_soal_bank_nomor', $existing)) {
                Schema::table('cbt_soal', function (Blueprint $table) {
                    $table->index(['bank_id', 'nomor_soal'], 'idx_soal_bank_nomor');
                });
            }
        }

        // 4. Index pada siswa_rombel_tahun: ['kelas_id', 'tahun_ajaran_id']
        if (Schema::hasTable('siswa_rombel_tahun')) {
            $existing = $this->getExistingIndexes('siswa_rombel_tahun');
            if (!in_array('idx_rombel_kelas_tahun', $existing)) {
                Schema::table('siswa_rombel_tahun', function (Blueprint $table) {
                    $table->index(['kelas_id', 'tahun_ajaran_id'], 'idx_rombel_kelas_tahun');
                });
            }
        }

        // 5. Pastikan Storage Engine InnoDB dengan ROW_FORMAT=DYNAMIC
        $tablesToDynamic = [
            'cbt_ujian_siswa',
            'cbt_jawaban_siswa',
            'cbt_soal',
            'siswa_rombel_tahun',
            'cbt_siswa',
            'cbt_jadwal',
            'kelas_siswa'
        ];

        foreach ($tablesToDynamic as $tableName) {
            if (Schema::hasTable($tableName)) {
                try {
                    DB::statement("ALTER TABLE `{$tableName}` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;");
                } catch (\Throwable $e) {
                    // Abaikan jika database permission terbatas
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cbt_ujian_siswa')) {
            $existing = $this->getExistingIndexes('cbt_ujian_siswa');
            if (in_array('idx_ujian_jadwal_siswa_status', $existing)) {
                Schema::table('cbt_ujian_siswa', function (Blueprint $table) {
                    $table->dropIndex('idx_ujian_jadwal_siswa_status');
                });
            }
        }

        if (Schema::hasTable('cbt_soal')) {
            $existing = $this->getExistingIndexes('cbt_soal');
            if (in_array('idx_soal_bank_nomor', $existing)) {
                Schema::table('cbt_soal', function (Blueprint $table) {
                    $table->dropIndex('idx_soal_bank_nomor');
                });
            }
        }

        if (Schema::hasTable('siswa_rombel_tahun')) {
            $existing = $this->getExistingIndexes('siswa_rombel_tahun');
            if (in_array('idx_rombel_kelas_tahun', $existing)) {
                Schema::table('siswa_rombel_tahun', function (Blueprint $table) {
                    $table->dropIndex('idx_rombel_kelas_tahun');
                });
            }
        }
    }
};
