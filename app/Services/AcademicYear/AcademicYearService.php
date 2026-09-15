<?php

namespace App\Services\AcademicYear;

use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\RefTahunAjaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicYearService
{
    protected const CACHE_KEY_ACTIVE = 'cbt_academic_year_active';
    protected const CACHE_KEY_ALL    = 'cbt_academic_year_all';
    protected const CACHE_TTL        = 86400; // 24 jam

    /**
     * Memastikan skema dan tabel tahun ajaran tersedia.
     */
    public function ensureSchema(): void
    {
        if (!Schema::hasTable('ref_tahun_ajaran') || !Schema::hasTable('siswa_rombel_tahun')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }
    }

    /**
     * Mengambil tahun ajaran aktif sistem (di-cache).
     */
    public function getActiveYear(): ?RefTahunAjaran
    {
        $this->ensureSchema();

        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, function () {
            $year = RefTahunAjaran::active()->first();
            if (!$year) {
                // Fallback ke entri pertama jika belum ada yang diset aktif
                $year = RefTahunAjaran::orderBy('id', 'desc')->first();
                if ($year) {
                    $year->update(['is_active' => true]);
                }
            }
            return $year;
        });
    }

    /**
     * Mengambil tahun ajaran yang sedang dipilih untuk filter (berdasarkan sesi atau default aktif).
     */
    public function getSelectedYear(): ?RefTahunAjaran
    {
        $selectedId = session('filter_tahun_ajaran_id');
        if ($selectedId) {
            $year = RefTahunAjaran::find($selectedId);
            if ($year) {
                return $year;
            }
        }
        return $this->getActiveYear();
    }

    /**
     * Mengubah tahun ajaran yang sedang aktif sistem secara global.
     */
    public function setActiveYear(int $id): bool
    {
        $targetYear = RefTahunAjaran::findOrFail($id);

        DB::transaction(function () use ($targetYear) {
            RefTahunAjaran::query()->update(['is_active' => false]);
            $targetYear->update(['is_active' => true]);

            // Sinkronkan ke tabel legacy master_tp & master_smt
            try {
                if (Schema::hasTable('master_tp')) {
                    DB::table('master_tp')->update(['active' => 0]);
                    DB::table('master_tp')->where('tahun', $targetYear->tahun)->update(['active' => 1]);
                }
                if (Schema::hasTable('master_smt')) {
                    DB::table('master_smt')->update(['active' => 0]);
                    DB::table('master_smt')->where('id_smt', $targetYear->semester)->update(['active' => 1]);
                }
            } catch (\Throwable $e) {}
        });

        $this->clearCache();
        return true;
    }

    /**
     * Menyimpan pilihan tahun ajaran pada sesi untuk melihat arsip data masa lalu.
     */
    public function setSelectedYear(int $id): void
    {
        session(['filter_tahun_ajaran_id' => $id]);
    }

    /**
     * Mereset filter sesi ke tahun ajaran aktif saat ini.
     */
    public function resetSelectedYear(): void
    {
        session()->forget('filter_tahun_ajaran_id');
    }

    /**
     * Mengambil seluruh daftar tahun ajaran.
     */
    public function getAllYears(): Collection
    {
        $this->ensureSchema();

        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            return RefTahunAjaran::orderBy('tahun', 'desc')->orderBy('semester', 'asc')->get();
        });
    }

    /**
     * Menghapus cache terkait tahun ajaran.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE);
        Cache::forget(self::CACHE_KEY_ALL);
    }
}
