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
        if (!Schema::hasTable('cbt_bank_soal_clean')) {
            Schema::create('cbt_bank_soal_clean', function (Blueprint $table) {
                $table->id();
                $table->string('kode_bank', 100)->unique();
                $table->unsignedBigInteger('mapel_id')->nullable()->index();
                $table->unsignedBigInteger('guru_id')->nullable()->index();
                $table->string('nama_bank', 255);
                $table->tinyInteger('tingkat')->default(10);
                $table->json('alokasi_kelas')->nullable();
                $table->integer('jml_pg')->default(0);
                $table->integer('jml_kompleks')->default(0);
                $table->integer('jml_jodoh')->default(0);
                $table->integer('jml_isian')->default(0);
                $table->integer('jml_esai')->default(0);
                $table->decimal('bobot_pg', 5, 2)->default(0);
                $table->decimal('bobot_kompleks', 5, 2)->default(0);
                $table->decimal('bobot_jodoh', 5, 2)->default(0);
                $table->decimal('bobot_isian', 5, 2)->default(0);
                $table->decimal('bobot_esai', 5, 2)->default(0);
                $table->integer('kkm')->default(75);
                $table->enum('status', ['draft', 'aktif', 'arsip'])->default('draft');
                $table->timestamps();

                $table->foreign('mapel_id')->references('id')->on('ref_mapel')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('cbt_soal_clean')) {
            Schema::create('cbt_soal_clean', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bank_id')->index();
                $table->integer('nomor_urut')->default(1);
                $table->tinyInteger('jenis_soal')->default(1)->comment('1=PG, 2=Kompleks, 3=Jodohkan, 4=Isian, 5=Esai');
                $table->longText('pertanyaan');
                $table->json('opsi')->nullable();
                $table->json('kunci_jawaban')->nullable();
                $table->string('media', 255)->nullable();
                $table->decimal('bobot', 5, 2)->default(1.00);
                $table->timestamps();

                $table->index(['bank_id', 'nomor_urut']);
                $table->foreign('bank_id')->references('id')->on('cbt_bank_soal_clean')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_soal_clean');
        Schema::dropIfExists('cbt_bank_soal_clean');
    }
};
