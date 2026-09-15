<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ref_kelas')) {
            Schema::create('ref_kelas', function (Blueprint $table) {
                $table->id();
                $table->string('kode_kelas', 50)->unique();
                $table->string('nama_kelas', 100);
                $table->tinyInteger('tingkat')->default(10); // 7,8,9 atau 10,11,12
                $table->string('jurusan', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ref_mapel')) {
            Schema::create('ref_mapel', function (Blueprint $table) {
                $table->id();
                $table->string('kode_mapel', 50)->unique();
                $table->string('nama_mapel', 150);
                $table->string('kelompok', 20)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ref_sesi')) {
            Schema::create('ref_sesi', function (Blueprint $table) {
                $table->id();
                $table->string('kode_sesi', 50)->unique();
                $table->string('nama_sesi', 100);
                $table->time('waktu_mulai')->nullable();
                $table->time('waktu_selesai')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ref_ruang')) {
            Schema::create('ref_ruang', function (Blueprint $table) {
                $table->id();
                $table->string('kode_ruang', 50)->unique();
                $table->string('nama_ruang', 100);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_ruang');
        Schema::dropIfExists('ref_sesi');
        Schema::dropIfExists('ref_mapel');
        Schema::dropIfExists('ref_kelas');
    }
};
