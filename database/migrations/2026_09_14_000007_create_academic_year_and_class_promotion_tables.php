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
        // 1. Tabel Master Referensi Tahun Ajaran & Semester
        if (!Schema::hasTable('ref_tahun_ajaran')) {
            Schema::create('ref_tahun_ajaran', function (Blueprint $table) {
                $table->id();
                $table->string('tahun', 50)->comment('Contoh: 2025/2026');
                $table->enum('semester', ['1', '2'])->default('1')->comment('1=Ganjil, 2=Genap');
                $table->string('nama_lengkap', 100)->nullable();
                $table->boolean('is_active')->default(false)->index();
                $table->date('tgl_mulai')->nullable();
                $table->date('tgl_selesai')->nullable();
                $table->timestamps();

                $table->unique(['tahun', 'semester'], 'ref_tahun_semester_unique');
            });
        }

        // 2. Tabel Pivot Siklus Siswa Rombel per Tahun Ajaran (Kenaikan Kelas & Mutasi)
        if (!Schema::hasTable('siswa_rombel_tahun')) {
            Schema::create('siswa_rombel_tahun', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('kelas_id')->index();
                $table->unsignedBigInteger('tahun_ajaran_id')->index();
                $table->string('nomor_peserta', 50)->nullable()->index();
                $table->enum('status', ['aktif', 'naik', 'tinggal', 'lulus', 'pindah'])->default('aktif')->index();
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->unique(['siswa_id', 'tahun_ajaran_id'], 'siswa_tahun_unique');
            });
        }

        // 3. Modifikasi kolom tahun_ajaran_id pada tabel-tabel CBT clean jika ada
        if (Schema::hasTable('cbt_bank_soal_clean') && !Schema::hasColumn('cbt_bank_soal_clean', 'tahun_ajaran_id')) {
            Schema::table('cbt_bank_soal_clean', function (Blueprint $table) {
                $table->unsignedBigInteger('tahun_ajaran_id')->nullable()->after('mapel_id')->index();
            });
        }

        if (Schema::hasTable('cbt_jadwal_clean') && !Schema::hasColumn('cbt_jadwal_clean', 'tahun_ajaran_id')) {
            Schema::table('cbt_jadwal_clean', function (Blueprint $table) {
                $table->unsignedBigInteger('tahun_ajaran_id')->nullable()->after('bank_id')->index();
            });
        }

        if (Schema::hasTable('cbt_ujian_siswa') && !Schema::hasColumn('cbt_ujian_siswa', 'tahun_ajaran_id')) {
            Schema::table('cbt_ujian_siswa', function (Blueprint $table) {
                $table->unsignedBigInteger('tahun_ajaran_id')->nullable()->after('siswa_id')->index();
            });
        }

        // 4. Sinkronisasi Data Awal dari Master TP & Smt Legacy
        $this->seedInitialData();
    }

    /**
     * Sinkronisasi data lama dari master_tp & master_smt jika ref_tahun_ajaran masih kosong.
     */
    protected function seedInitialData(): void
    {
        try {
            if (DB::table('ref_tahun_ajaran')->count() === 0) {
                $tpList = DB::table('master_tp')->get();
                $smtAktif = DB::table('master_smt')->where('active', 1)->first()?->id_smt ?? 1;

                foreach ($tpList as $tp) {
                    foreach (['1', '2'] as $sem) {
                        $semText = $sem === '1' ? 'Ganjil' : 'Genap';
                        $isActive = ($tp->active == 1 && $sem == $smtAktif);

                        DB::table('ref_tahun_ajaran')->insertOrIgnore([
                            'tahun'        => $tp->tahun,
                            'semester'     => $sem,
                            'nama_lengkap' => "T.P. {$tp->tahun} ({$semText})",
                            'is_active'    => $isActive,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }

                // Jika belum ada yang aktif, aktifkan entri pertama
                if (DB::table('ref_tahun_ajaran')->where('is_active', true)->count() === 0) {
                    $first = DB::table('ref_tahun_ajaran')->orderBy('id', 'desc')->first();
                    if ($first) {
                        DB::table('ref_tahun_ajaran')->where('id', $first->id)->update(['is_active' => true]);
                    }
                }
            }

            // Sinkronkan kelas_siswa ke siswa_rombel_tahun
            if (DB::table('siswa_rombel_tahun')->count() === 0 && Schema::hasTable('kelas_siswa')) {
                $activeTa = DB::table('ref_tahun_ajaran')->where('is_active', true)->first();
                if ($activeTa) {
                    $enrollments = DB::table('kelas_siswa')->get();
                    $insertData = [];
                    foreach ($enrollments as $e) {
                        $insertData[] = [
                            'siswa_id'        => $e->id_siswa,
                            'kelas_id'        => $e->id_kelas,
                            'tahun_ajaran_id' => $activeTa->id,
                            'status'          => 'aktif',
                            'keterangan'      => 'Migrasi awal dari kelas_siswa',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                    if (!empty($insertData)) {
                        foreach (array_chunk($insertData, 200) as $chunk) {
                            DB::table('siswa_rombel_tahun')->insertOrIgnore($chunk);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore error during initial data seeding if tables differ
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_rombel_tahun');
        Schema::dropIfExists('ref_tahun_ajaran');

        if (Schema::hasColumn('cbt_bank_soal_clean', 'tahun_ajaran_id')) {
            Schema::table('cbt_bank_soal_clean', function (Blueprint $table) {
                $table->dropColumn('tahun_ajaran_id');
            });
        }
        if (Schema::hasColumn('cbt_jadwal_clean', 'tahun_ajaran_id')) {
            Schema::table('cbt_jadwal_clean', function (Blueprint $table) {
                $table->dropColumn('tahun_ajaran_id');
            });
        }
        if (Schema::hasColumn('cbt_ujian_siswa', 'tahun_ajaran_id')) {
            Schema::table('cbt_ujian_siswa', function (Blueprint $table) {
                $table->dropColumn('tahun_ajaran_id');
            });
        }
    }
};
