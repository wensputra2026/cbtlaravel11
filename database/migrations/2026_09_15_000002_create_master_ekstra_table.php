<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('master_ekstra')) {
            Schema::create('master_ekstra', function (Blueprint $table) {
                $table->increments('id_ekstra');
                $table->string('nama_ekstra', 100);
                $table->string('kode_ekstra', 20);
                $table->string('mapel_agama', 2)->nullable();
                $table->string('mapel_gabungan', 2)->nullable();
            });

            // Default initial extracurricular activities
            DB::table('master_ekstra')->insert([
                ['id_ekstra' => 1, 'nama_ekstra' => 'Pramuka', 'kode_ekstra' => 'PRAM', 'mapel_agama' => null, 'mapel_gabungan' => null],
                ['id_ekstra' => 2, 'nama_ekstra' => 'Baca Tulis Al Quran', 'kode_ekstra' => 'BTQ', 'mapel_agama' => null, 'mapel_gabungan' => null],
                ['id_ekstra' => 3, 'nama_ekstra' => 'Tahfidz', 'kode_ekstra' => 'TFZ', 'mapel_agama' => null, 'mapel_gabungan' => null],
                ['id_ekstra' => 4, 'nama_ekstra' => 'Palang Merah Remaja', 'kode_ekstra' => 'PMR', 'mapel_agama' => null, 'mapel_gabungan' => null],
                ['id_ekstra' => 5, 'nama_ekstra' => 'Paskibra', 'kode_ekstra' => 'PASKIB', 'mapel_agama' => null, 'mapel_gabungan' => null],
                ['id_ekstra' => 6, 'nama_ekstra' => 'Karya Ilmiah Remaja', 'kode_ekstra' => 'KIR', 'mapel_agama' => null, 'mapel_gabungan' => null],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ekstra');
    }
};
