<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cbt_siswa')) {
            // View kompatibilitas untuk cbt_durasi_siswa
            DB::statement("
                CREATE OR REPLACE VIEW cbt_durasi_siswa AS 
                SELECT 
                    CONCAT(id_siswa, id_jadwal) AS id_durasi,
                    id_siswa,
                    id_jadwal,
                    status,
                    mulai,
                    selesai,
                    lama_ujian,
                    reset_izin AS reset,
                    reset_waktu,
                    pelanggaran
                FROM cbt_siswa
            ");

            // View kompatibilitas untuk cbt_nilai
            DB::statement("
                CREATE OR REPLACE VIEW cbt_nilai AS 
                SELECT 
                    CONCAT(id_siswa, id_jadwal) AS id_nilai,
                    id_siswa,
                    id_jadwal,
                    nilai,
                    nilai_input,
                    status
                FROM cbt_siswa
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS cbt_durasi_siswa");
        DB::statement("DROP VIEW IF EXISTS cbt_nilai");
    }
};
