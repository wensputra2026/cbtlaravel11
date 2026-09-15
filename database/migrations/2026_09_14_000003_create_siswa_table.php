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
        if (!Schema::hasTable('siswa')) {
            Schema::create('siswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('nis', 50)->unique();
                $table->string('nisn', 30)->nullable()->index();
                $table->string('nama_lengkap', 150);
                $table->char('jenis_kelamin', 1)->default('L'); // L atau P
                $table->unsignedBigInteger('kelas_id')->nullable()->index();
                $table->unsignedBigInteger('sesi_id')->nullable()->index();
                $table->unsignedBigInteger('ruang_id')->nullable()->index();
                $table->string('nomor_peserta', 50)->nullable()->index();
                $table->string('foto', 255)->nullable();
                $table->timestamps();

                $table->foreign('kelas_id')->references('id')->on('ref_kelas')->nullOnDelete();
                $table->foreign('sesi_id')->references('id')->on('ref_sesi')->nullOnDelete();
                $table->foreign('ruang_id')->references('id')->on('ref_ruang')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
