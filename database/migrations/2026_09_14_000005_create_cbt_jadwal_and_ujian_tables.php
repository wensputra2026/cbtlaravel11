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
        if (!Schema::hasTable('cbt_jadwal_clean')) {
            Schema::create('cbt_jadwal_clean', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bank_id')->index();
                $table->string('nama_ujian', 255);
                $table->string('kode_jenis', 20)->default('PAT');
                $table->dateTime('waktu_mulai');
                $table->dateTime('waktu_selesai');
                $table->integer('durasi_menit')->default(90);
                $table->boolean('acak_soal')->default(true);
                $table->boolean('acak_opsi')->default(true);
                $table->boolean('pakai_token')->default(true);
                $table->string('token_default', 10)->nullable();
                $table->boolean('tampilkan_nilai')->default(false);
                $table->tinyInteger('status')->default(1)->comment('0=nonaktif, 1=aktif, 2=selesai');
                $table->timestamps();

                $table->foreign('bank_id')->references('id')->on('cbt_bank_soal_clean')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('cbt_ujian_siswa')) {
            Schema::create('cbt_ujian_siswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_id')->index();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->dateTime('waktu_mulai')->nullable();
                $table->dateTime('waktu_selesai')->nullable();
                $table->integer('sisa_detik')->default(0);
                $table->tinyInteger('status')->default(0)->comment('0=belum mulai, 1=sedang ujian, 2=selesai');
                $table->integer('pelanggaran_count')->default(0);
                $table->decimal('nilai_akhir', 5, 2)->nullable();
                $table->timestamps();

                $table->unique(['jadwal_id', 'siswa_id']);
                $table->foreign('jadwal_id')->references('id')->on('cbt_jadwal_clean')->cascadeOnDelete();
                $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('cbt_jawaban_siswa')) {
            Schema::create('cbt_jawaban_siswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_id')->index();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('soal_id')->index();
                $table->json('jawaban')->nullable();
                $table->boolean('ragu')->default(false);
                $table->decimal('skor_butir', 5, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['jadwal_id', 'siswa_id', 'soal_id']);
                $table->foreign('jadwal_id')->references('id')->on('cbt_jadwal_clean')->cascadeOnDelete();
                $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
                $table->foreign('soal_id')->references('id')->on('cbt_soal_clean')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_jawaban_siswa');
        Schema::dropIfExists('cbt_ujian_siswa');
        Schema::dropIfExists('cbt_jadwal_clean');
    }
};
