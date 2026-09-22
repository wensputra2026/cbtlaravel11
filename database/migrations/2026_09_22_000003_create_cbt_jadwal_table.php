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
        if (!Schema::hasTable('cbt_jadwal')) {
            Schema::create('cbt_jadwal', function (Blueprint $table) {
                $table->increments('id_jadwal');
                $table->char('id_tp', 2)->index();
                $table->char('id_smt', 2)->index();
                $table->unsignedInteger('id_bank')->index();
                $table->unsignedInteger('id_jenis')->nullable()->index()->comment('Jenis penilaian, e.g. STS, SAS, PAT');

                // Rentang Waktu Pelaksanaan Ujian
                $table->string('tgl_mulai', 25)->comment('Format YYYY-MM-DD HH:mm:ss');
                $table->string('tgl_selesai', 25)->comment('Batas akhir pengerjaan');
                $table->integer('durasi_ujian')->default(60)->comment('Durasi dalam satuan menit');

                // Kontrol Keamanan & Perilaku Ujian Siswa
                $table->tinyInteger('acak_soal')->default(1)->comment('1=Acak nomor soal, 0=Urut');
                $table->tinyInteger('acak_opsi')->default(1)->comment('1=Acak opsi A-E, 0=Urut');
                $table->tinyInteger('hasil_tampil')->default(0)->comment('1=Tampilkan nilai instan ke siswa');
                $table->tinyInteger('token')->default(1)->comment('1=Wajib input token ujian');
                $table->tinyInteger('status')->default(0)->comment('1=Jadwal Aktif, 0=Nonaktif');
                $table->tinyInteger('ulang')->default(0)->comment('Jumlah kesempatan re-test');
                $table->tinyInteger('reset_login')->default(0)->comment('Otorisasi reset login perangkat');
                $table->tinyInteger('rekap')->default(0);
                $table->tinyInteger('jam_ke')->default(1);
                $table->integer('jarak')->default(0);
                $table->char('pelanggaran', 2)->nullable()->default('0');

                $table->dateTime('time_create')->nullable();

                // Indeks penunjang pengecekan jadwal aktif siswa saat dashboard dibuka
                $table->index(['id_bank', 'status', 'tgl_mulai', 'tgl_selesai']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_jadwal');
    }
};
