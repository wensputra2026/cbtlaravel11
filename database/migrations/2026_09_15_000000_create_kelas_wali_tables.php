<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelas_struktur')) {
            Schema::create('kelas_struktur', function (Blueprint $table) {
                $table->integer('id_kelas')->primary();
                $table->integer('ketua')->nullable();
                $table->integer('wakil_ketua')->nullable();
                $table->integer('sekretaris_1')->nullable();
                $table->integer('sekretaris_2')->nullable();
                $table->integer('bendahara_1')->nullable();
                $table->integer('bendahara_2')->nullable();
                $table->integer('sie_ekstrakurikuler')->nullable();
                $table->integer('sie_upacara')->nullable();
                $table->integer('sie_olahraga')->nullable();
                $table->integer('sie_keagamaan')->nullable();
                $table->integer('sie_keamanan')->nullable();
                $table->integer('sie_ketertiban')->nullable();
                $table->integer('sie_kebersihan')->nullable();
                $table->integer('sie_keindahan')->nullable();
                $table->integer('sie_kesehatan')->nullable();
                $table->integer('sie_kekeluargaan')->nullable();
                $table->integer('sie_humas')->nullable();
            });
        }

        if (!Schema::hasTable('kelas_catatan_wali')) {
            Schema::create('kelas_catatan_wali', function (Blueprint $table) {
                $table->increments('id_catatan');
                $table->integer('id_tp');
                $table->integer('id_smt');
                $table->integer('type')->default(1)->comment('1=semua siswa, 2=per siswa');
                $table->string('level', 1)->default('1')->comment('1=saran, 2=teguran, 3=peringatan, 4=sangsi');
                $table->timestamp('tgl')->useCurrent();
                $table->integer('id_siswa')->nullable();
                $table->integer('id_kelas')->nullable();
                $table->mediumText('text');
                $table->string('readed', 22)->default('0');
                $table->longText('reading')->nullable();
                $table->integer('jml')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_catatan_wali');
        Schema::dropIfExists('kelas_struktur');
    }
};
