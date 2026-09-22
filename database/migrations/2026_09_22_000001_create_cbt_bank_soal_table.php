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
        if (!Schema::hasTable('cbt_bank_soal')) {
            Schema::create('cbt_bank_soal', function (Blueprint $table) {
                $table->increments('id_bank');
                $table->string('bank_kode', 100)->unique();
                $table->string('bank_nama', 250)->nullable();

                // Relasi Master
                $table->unsignedInteger('bank_mapel_id')->nullable()->index();
                $table->unsignedInteger('bank_guru_id')->nullable()->index();
                $table->string('bank_level', 50)->nullable()->index();
                $table->longText('bank_kelas')->nullable()->comment('JSON alokasi kelas target');
                $table->unsignedInteger('bank_jenis_id')->default(0);
                $table->unsignedInteger('bank_jurusan_id')->default(0);

                // Komposisi 5 Tipe Soal & Bobot Nilai (%)
                $table->integer('jml_soal')->default(0)->comment('Kapasitas PG');
                $table->integer('tampil_pg')->default(0);
                $table->integer('bobot_pg')->default(0);
                $table->integer('opsi')->default(5)->comment('Jumlah opsi PG: 3, 4, atau 5');

                $table->integer('jml_kompleks')->default(0);
                $table->integer('tampil_kompleks')->default(0);
                $table->integer('bobot_kompleks')->default(0);

                $table->integer('jml_jodohkan')->default(0);
                $table->integer('tampil_jodohkan')->default(0);
                $table->integer('bobot_jodohkan')->default(0);

                $table->integer('jml_isian')->default(0);
                $table->integer('tampil_isian')->default(0);
                $table->integer('bobot_isian')->default(0);

                $table->integer('jml_esai')->default(0);
                $table->integer('tampil_esai')->default(0);
                $table->integer('bobot_esai')->default(0);

                // Pengaturan Ujian & KKM
                $table->integer('kkm')->default(75);
                $table->string('soal_agama', 20)->default('-')->index();
                $table->tinyInteger('status')->default(1)->comment('0=Nonaktif, 1=Aktif');
                $table->tinyInteger('status_soal')->default(0)->comment('0=Belum Lengkap, 1=Selesai');
                $table->longText('deskripsi')->nullable();

                // Tahun Pelajaran & Semester Aktif
                $table->unsignedInteger('id_tp')->nullable()->index();
                $table->unsignedInteger('id_smt')->nullable()->index();
                $table->timestamp('date')->useCurrent();

                // Composite Index untuk percepatan query list bank soal
                $table->index(['id_tp', 'id_smt', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_bank_soal');
    }
};
