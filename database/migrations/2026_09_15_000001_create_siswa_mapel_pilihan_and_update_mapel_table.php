<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi Kolom pada master_mapel
        if (Schema::hasTable('master_mapel')) {
            Schema::table('master_mapel', function (Blueprint $table) {
                if (!Schema::hasColumn('master_mapel', 'is_pilihan')) {
                    $table->boolean('is_pilihan')->default(false)->after('kelompok')->index();
                }
                if (!Schema::hasColumn('master_mapel', 'agama')) {
                    $table->string('agama', 50)->nullable()->after('mapel_agama')->index();
                }
            });
        }

        // 2. Modifikasi Kolom pada ref_mapel
        if (Schema::hasTable('ref_mapel')) {
            Schema::table('ref_mapel', function (Blueprint $table) {
                if (!Schema::hasColumn('ref_mapel', 'is_pilihan')) {
                    $table->boolean('is_pilihan')->default(false)->after('kelompok')->index();
                }
                if (!Schema::hasColumn('ref_mapel', 'agama')) {
                    $table->string('agama', 50)->nullable()->after('is_pilihan')->index();
                }
            });
        }

        // 3. Modifikasi Kolom pada siswa (sinkronisasi agama dari master_siswa)
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('siswa', 'agama')) {
                    $table->string('agama', 50)->nullable()->after('jenis_kelamin')->index();
                }
            });

            // Salin data agama dari master_siswa ke siswa
            if (Schema::hasTable('master_siswa')) {
                DB::statement("
                    UPDATE `siswa` s
                    INNER JOIN `master_siswa` ms ON (s.nisn = ms.nisn OR s.nis = ms.nis OR s.id = ms.id_siswa)
                    SET s.agama = ms.agama
                    WHERE ms.agama IS NOT NULL AND ms.agama != '' AND ms.agama != '0'
                ");
            }
        }

        // 4. Tabel Pivot Alokasi Mapel Pilihan (Kurikulum Merdeka Fase F)
        if (!Schema::hasTable('siswa_mapel_pilihan')) {
            Schema::create('siswa_mapel_pilihan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('mapel_id')->index();
                $table->unsignedBigInteger('tahun_ajaran_id')->index();
                $table->timestamps();

                // Composite Unique Constraint
                $table->unique(['siswa_id', 'mapel_id', 'tahun_ajaran_id'], 'siswa_mapel_ta_unique');

                // Foreign Keys
                if (Schema::hasTable('siswa')) {
                    $table->foreign('siswa_id')
                          ->references('id')
                          ->on('siswa')
                          ->onDelete('cascade');
                }

                if (Schema::hasTable('ref_tahun_ajaran')) {
                    $table->foreign('tahun_ajaran_id')
                          ->references('id')
                          ->on('ref_tahun_ajaran')
                          ->onDelete('cascade');
                }
            });
        }

        // 5. Seeding Default untuk Mapel Pilihan & Mapel Agama
        $this->seedInitialMapelSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_mapel_pilihan');

        if (Schema::hasTable('master_mapel')) {
            Schema::table('master_mapel', function (Blueprint $table) {
                if (Schema::hasColumn('master_mapel', 'is_pilihan')) {
                    $table->dropColumn('is_pilihan');
                }
                if (Schema::hasColumn('master_mapel', 'agama')) {
                    $table->dropColumn('agama');
                }
            });
        }

        if (Schema::hasTable('ref_mapel')) {
            Schema::table('ref_mapel', function (Blueprint $table) {
                if (Schema::hasColumn('ref_mapel', 'is_pilihan')) {
                    $table->dropColumn('is_pilihan');
                }
                if (Schema::hasColumn('ref_mapel', 'agama')) {
                    $table->dropColumn('agama');
                }
            });
        }

        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                if (Schema::hasColumn('siswa', 'agama')) {
                    $table->dropColumn('agama');
                }
            });
        }
    }

    /**
     * Set data awal is_pilihan dan agama untuk mapel yang sudah ada.
     */
    private function seedInitialMapelSettings(): void
    {
        if (Schema::hasTable('master_mapel')) {
            // Tandai mapel kelompok PEM atau mengandung TL (Tingkat Lanjut) sebagai pilihan
            DB::table('master_mapel')
                ->where('kelompok', 'PEM')
                ->orWhere('kode', 'like', '%_TL')
                ->orWhere('nama_mapel', 'like', '%Tingkat Lanjut%')
                ->update(['is_pilihan' => true]);

            // Set agama
            DB::table('master_mapel')
                ->where('nama_mapel', 'like', '%Islam%')
                ->orWhere('kode', 'like', '%PAI%')
                ->update(['agama' => 'Islam', 'mapel_agama' => '1']);

            DB::table('master_mapel')
                ->where(function ($q) {
                    $q->where('nama_mapel', 'like', '%Kristen%')
                      ->orWhere('nama_mapel', 'like', '%Protestan%')
                      ->orWhere('kode', 'KRIS')
                      ->orWhere('kode', 'PAKP');
                })
                ->update(['agama' => 'Kristen', 'mapel_agama' => '1']);

            DB::table('master_mapel')
                ->where(function ($q) {
                    $q->where('nama_mapel', 'like', '%Katholik%')
                      ->orWhere('nama_mapel', 'like', '%Katolik%')
                      ->orWhere('kode', 'PAKAT')
                      ->orWhere('kode', 'KAT');
                })
                ->update(['agama' => 'Katolik', 'mapel_agama' => '1']);

            DB::table('master_mapel')
                ->where('nama_mapel', 'like', '%Hindu%')
                ->orWhere('kode', 'like', '%HIN%')
                ->update(['agama' => 'Hindu', 'mapel_agama' => '1']);

            DB::table('master_mapel')
                ->where('nama_mapel', 'like', '%Buddha%')
                ->orWhere('kode', 'like', '%BUD%')
                ->update(['agama' => 'Buddha', 'mapel_agama' => '1']);
        }

        if (Schema::hasTable('ref_mapel') && Schema::hasTable('master_mapel')) {
            // Sinkronkan ke ref_mapel
            DB::statement("
                UPDATE `ref_mapel` rm
                INNER JOIN `master_mapel` mm ON rm.id = mm.id_mapel
                SET rm.is_pilihan = mm.is_pilihan, rm.agama = mm.agama
            ");
        }
    }
};
