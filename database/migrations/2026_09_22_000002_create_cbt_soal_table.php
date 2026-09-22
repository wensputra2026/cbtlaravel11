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
        if (!Schema::hasTable('cbt_soal')) {
            Schema::create('cbt_soal', function (Blueprint $table) {
                $table->increments('id_soal');
                $table->unsignedInteger('bank_id')->index();
                $table->unsignedInteger('mapel_id')->default(0);

                // Jenis Soal: 1=PG, 2=PG Kompleks, 3=Jodohkan, 4=Isian Singkat, 5=Esai
                $table->tinyInteger('jenis')->default(1)->index();
                $table->integer('nomor_soal')->default(1);

                // Konten Pertanyaan & Media
                $table->longText('soal')->nullable();
                $table->string('file', 255)->nullable()->comment('Media stimulus utama (audio/image/video)');
                $table->longText('file1')->nullable();
                $table->string('tipe_file', 50)->nullable();

                // Opsi Jawaban (Khusus PG / PG Kompleks)
                $table->longText('opsi_a')->nullable();
                $table->longText('opsi_b')->nullable();
                $table->longText('opsi_c')->nullable();
                $table->longText('opsi_d')->nullable();
                $table->longText('opsi_e')->nullable();
                $table->string('file_a', 255)->nullable();
                $table->string('file_b', 255)->nullable();
                $table->string('file_c', 255)->nullable();
                $table->string('file_d', 255)->nullable();
                $table->string('file_e', 255)->nullable();

                // Kunci Jawaban (Dapat berupa Huruf, CSV, atau JSON Pasangan)
                $table->longText('jawaban')->nullable();
                $table->decimal('bobot', 5, 2)->default(1.00);

                // Pengaturan Tampilan & Tingkat Kesulitan
                $table->tinyInteger('tampilkan')->default(1)->comment('1=Tampil saat ujian, 0=Draft');
                $table->tinyInteger('kesulitan')->default(1)->comment('1=Mudah, 2=Sedang, 3=Sukar');
                $table->integer('timer')->default(0);
                $table->integer('timer_menit')->default(0);
                $table->longText('deskripsi')->nullable();

                $table->integer('created_on')->nullable();
                $table->integer('updated_on')->nullable();

                // Indeks gabungan krusial untuk rendering cepat saat siswa load soal ujian
                $table->index(['bank_id', 'jenis', 'tampilkan', 'nomor_soal']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_soal');
    }
};
