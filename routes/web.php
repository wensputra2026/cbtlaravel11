<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CbtAlokasiController;
use App\Http\Controllers\Admin\CbtAlokasiPilihanController;
use App\Http\Controllers\Admin\CbtAnalisisController;
use App\Http\Controllers\Admin\CbtBankSoalController;
use App\Http\Controllers\Admin\CbtJadwalController;
use App\Http\Controllers\Admin\CbtNilaiController;
use App\Http\Controllers\Admin\CbtSesiRuangController;
use App\Http\Controllers\Admin\CbtTokenController;
use App\Http\Controllers\Admin\ClassPromotionController;
use App\Http\Controllers\Admin\DatabaseManagerController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\SchoolSettingController;
use App\Http\Controllers\Admin\SystemMaintenanceController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentPrintController;
use App\Http\Controllers\ExamSessionController;
use App\Http\Controllers\ExamViewController;
use App\Http\Controllers\Guru\GuruBankSoalController;
use App\Http\Controllers\Guru\GuruDashboardController;
use App\Http\Controllers\Guru\GuruHasilController;
use App\Http\Controllers\Guru\GuruJadwalController;
use App\Http\Controllers\Guru\GuruKoreksiController;
use App\Http\Controllers\Guru\GuruPengawasanController;
use App\Http\Controllers\Guru\GuruProfilController;
use App\Http\Controllers\Guru\GuruTokenController;
use App\Http\Controllers\Guru\GuruWaliKelasController;
use App\Http\Controllers\ProctorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Garuda CBT Next Gen (Laravel 11 Engine)
|--------------------------------------------------------------------------
*/

// Autentikasi Publik
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =============================================================================
// RUTE TERAUTENTIKASI
// =============================================================================
Route::middleware(['auth'])->group(function () {

    // =========================================================================
    // 1. LEVEL ADMIN / ADMINISTRATOR (ROLE: ADMIN)
    // =========================================================================
    Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Data Master
        Route::prefix('master')->name('master.')->group(function () {
            Route::get('/tp', [MasterDataController::class, 'indexTp'])->name('tp');
            Route::post('/tp', [MasterDataController::class, 'storeTp'])->name('tp.store');
            Route::post('/tp/aktif/{id}', [MasterDataController::class, 'setAktifTp'])->name('tp.aktif');
            Route::post('/tp/set-active/{id}', [MasterDataController::class, 'setAktifTp'])->name('tp.set_active');
            Route::post('/smt/aktif/{id}', [MasterDataController::class, 'setAktifSmt'])->name('smt.aktif');
            Route::post('/smt/set-active/{id}', [MasterDataController::class, 'setAktifSmt'])->name('smt.set_active');

            Route::get('/jurusan', [MasterDataController::class, 'indexJurusan'])->name('jurusan');
            Route::post('/jurusan', [MasterDataController::class, 'storeJurusan'])->name('jurusan.store');
            Route::put('/jurusan/{id}', [MasterDataController::class, 'updateJurusan'])->name('jurusan.update');
            Route::delete('/jurusan/{id}', [MasterDataController::class, 'destroyJurusan'])->name('jurusan.destroy');

            Route::get('/kelas', [MasterDataController::class, 'indexKelas'])->name('kelas');
            Route::post('/kelas', [MasterDataController::class, 'storeKelas'])->name('kelas.store');
            Route::post('/kelas/bulk', [MasterDataController::class, 'bulkStoreKelas'])->name('kelas.bulk');
            Route::get('/kelas/{id}/detail', [MasterDataController::class, 'detailKelas'])->name('kelas.detail');
            Route::post('/kelas/sync-semester', [MasterDataController::class, 'syncSemesterKelas'])->name('kelas.sync_semester');
            Route::put('/kelas/{id}', [MasterDataController::class, 'updateKelas'])->name('kelas.update');
            Route::delete('/kelas/{id}', [MasterDataController::class, 'destroyKelas'])->name('kelas.destroy');

            Route::get('/mapel', [MasterDataController::class, 'indexMapel'])->name('mapel');
            Route::post('/mapel', [MasterDataController::class, 'storeMapel'])->name('mapel.store');
            Route::put('/mapel/{id}', [MasterDataController::class, 'updateMapel'])->name('mapel.update');
            Route::delete('/mapel/{id}', [MasterDataController::class, 'destroyMapel'])->name('mapel.destroy');
            Route::post('/mapel/bulk-delete', [MasterDataController::class, 'bulkDestroyMapel'])->name('mapel.bulk_destroy');
            Route::post('/mapel/{id}/toggle-status', [MasterDataController::class, 'toggleStatusMapel'])->name('mapel.toggle_status');
            Route::get('/mapel/template', [MasterDataController::class, 'downloadTemplateMapel'])->name('mapel.template');
            Route::post('/mapel/import', [MasterDataController::class, 'importMapel'])->name('mapel.import');

            // Kelompok & Sub Kelompok Mapel
            Route::post('/mapel/kelompok', [MasterDataController::class, 'storeKelompokMapel'])->name('mapel.kelompok.store');
            Route::put('/mapel/kelompok/{id}', [MasterDataController::class, 'updateKelompokMapel'])->name('mapel.kelompok.update');
            Route::delete('/mapel/kelompok/{id}', [MasterDataController::class, 'destroyKelompokMapel'])->name('mapel.kelompok.destroy');

            Route::get('/guru', [MasterDataController::class, 'indexGuru'])->name('guru');
            Route::post('/guru', [MasterDataController::class, 'storeGuru'])->name('guru.store');
            Route::put('/guru/{id}', [MasterDataController::class, 'updateGuru'])->name('guru.update');
            Route::delete('/guru/{id}', [MasterDataController::class, 'destroyGuru'])->name('guru.destroy');
            Route::post('/guru/bulk-delete', [MasterDataController::class, 'bulkDestroyGuru'])->name('guru.bulk_destroy');
            Route::post('/guru/{id}/toggle-status', [MasterDataController::class, 'toggleStatusGuru'])->name('guru.toggle_status');
            Route::get('/guru/{id}/edit-jabatan', [MasterDataController::class, 'editJabatanGuru'])->name('guru.edit_jabatan');
            Route::post('/guru/{id}/jabatan', [MasterDataController::class, 'updateJabatanGuru'])->name('guru.jabatan.update');
            Route::post('/guru/{id}/jabatan/copy', [MasterDataController::class, 'copyJabatanGuru'])->name('guru.jabatan.copy');
            Route::post('/guru/jabatan/level', [MasterDataController::class, 'storeLevelGuru'])->name('guru.jabatan.level.store');
            Route::get('/guru/template', [MasterDataController::class, 'downloadTemplateGuru'])->name('guru.template');
            Route::post('/guru/import', [MasterDataController::class, 'importGuru'])->name('guru.import');

            Route::get('/siswa', [MasterDataController::class, 'indexSiswa'])->name('siswa');
            Route::post('/siswa', [MasterDataController::class, 'storeSiswa'])->name('siswa.store');
            Route::put('/siswa/{id}', [MasterDataController::class, 'updateSiswa'])->name('siswa.update');
            Route::delete('/siswa/{id}', [MasterDataController::class, 'destroySiswa'])->name('siswa.destroy');
            Route::post('/siswa/bulk-action', [MasterDataController::class, 'bulkActionSiswa'])->name('siswa.bulk_action');
            Route::post('/siswa/{id}/reset-password', [MasterDataController::class, 'resetPasswordSiswa'])->name('siswa.reset_password');
            Route::get('/siswa/template', [MasterDataController::class, 'downloadTemplateSiswa'])->name('siswa.template');
            Route::get('/siswa/export', [MasterDataController::class, 'exportSiswa'])->name('siswa.export');
            Route::post('/siswa/import', [MasterDataController::class, 'importSiswa'])->name('siswa.import');
            Route::post('/siswa/list', [MasterDataController::class, 'ajaxListSiswa'])->name('siswa.list');

            Route::get('/alumni', [MasterDataController::class, 'indexAlumni'])->name('alumni');

            // Kenaikan Kelas, Mutasi, dan Kelulusan Alumni
            Route::get('/kenaikan-kelas', [ClassPromotionController::class, 'index'])->name('kenaikan_kelas');
            Route::post('/kenaikan-kelas/promote', [ClassPromotionController::class, 'promoteClass'])->name('kenaikan_kelas.promote');
            Route::post('/kenaikan-kelas/switch', [ClassPromotionController::class, 'switchClass'])->name('kenaikan_kelas.switch');
            Route::post('/kenaikan-kelas/graduate', [ClassPromotionController::class, 'graduateStudents'])->name('kenaikan_kelas.graduate');
        });

        // Global Academic Year Switcher
        Route::post('/academic-year/switch', [ClassPromotionController::class, 'switchAcademicYear'])->name('academic_year.switch');

        // Manajemen CBT
        Route::prefix('cbt')->name('cbt.')->group(function () {
            // Bank Soal
            Route::get('/bank-soal', [CbtBankSoalController::class, 'index'])->name('bank_soal.index');
            Route::post('/bank-soal', [CbtBankSoalController::class, 'store'])->name('bank_soal.store');
            Route::get('/bank-soal/{id}', [CbtBankSoalController::class, 'show'])->name('bank_soal.show');
            Route::get('/bank-soal/{id}/buat-soal', [CbtBankSoalController::class, 'buatSoal'])->name('bank_soal.buat_soal');
            Route::get('/bank-soal/{id}/soal/{soalId}/edit', [CbtBankSoalController::class, 'editSoal'])->name('bank_soal.edit_soal');
            Route::put('/bank-soal/soal/{soalId}', [CbtBankSoalController::class, 'updateSoal'])->name('bank_soal.update_soal');
            Route::post('/bank-soal/soal/{soalId}', [CbtBankSoalController::class, 'updateSoal'])->name('bank_soal.update_soal_post');
            Route::post('/bank-soal/{id}/soal', [CbtBankSoalController::class, 'storeSoal'])->name('bank_soal.store_soal');
            Route::post('/bank-soal/{id}/soal/store', [CbtBankSoalController::class, 'storeSoal'])->name('bank_soal.soal.store');
            Route::delete('/bank-soal/soal/{id}', [CbtBankSoalController::class, 'deleteSoal'])->name('bank_soal.delete_soal');
            Route::delete('/bank-soal/soal/{id}/destroy', [CbtBankSoalController::class, 'deleteSoal'])->name('bank_soal.soal.destroy');
            Route::post('/bank-soal/{id}/duplicate', [CbtBankSoalController::class, 'duplicate'])->name('bank_soal.duplicate');
            Route::delete('/bank-soal/{id}', [CbtBankSoalController::class, 'destroy'])->name('bank_soal.destroy');

            // Jadwal & Jenis Ujian
            Route::get('/jadwal', [CbtJadwalController::class, 'index'])->name('jadwal.index');
            Route::post('/jadwal', [CbtJadwalController::class, 'store'])->name('jadwal.store');
            Route::put('/jadwal/{id}', [CbtJadwalController::class, 'update'])->name('jadwal.update');
            Route::post('/jadwal/{id}', [CbtJadwalController::class, 'update'])->name('jadwal.update_post');
            Route::post('/jadwal/toggle/{id}', [CbtJadwalController::class, 'toggleStatus'])->name('jadwal.toggle');
            Route::delete('/jadwal/{id}', [CbtJadwalController::class, 'destroy'])->name('jadwal.destroy');

            Route::get('/jenis', [CbtJadwalController::class, 'indexJenis'])->name('jenis.index');
            Route::post('/jenis', [CbtJadwalController::class, 'storeJenis'])->name('jenis.store');
            Route::put('/jenis/{id}', [CbtJadwalController::class, 'updateJenis'])->name('jenis.update');
            Route::post('/jenis/{id}', [CbtJadwalController::class, 'updateJenis'])->name('jenis.update_post');
            Route::delete('/jenis/{id}', [CbtJadwalController::class, 'destroyJenis'])->name('jenis.destroy');

            // Sesi & Ruang
            Route::get('/sesi-ruang', [CbtSesiRuangController::class, 'index'])->name('sesi_ruang.index');
            Route::post('/ruang', [CbtSesiRuangController::class, 'storeRuang'])->name('ruang.store');
            Route::post('/sesi-ruang/ruang', [CbtSesiRuangController::class, 'storeRuang'])->name('sesi_ruang.ruang.store');
            Route::post('/sesi', [CbtSesiRuangController::class, 'storeSesi'])->name('sesi.store');
            Route::post('/sesi-ruang/sesi', [CbtSesiRuangController::class, 'storeSesi'])->name('sesi_ruang.sesi.store');
            Route::post('/pengawas', [CbtSesiRuangController::class, 'assignPengawas'])->name('pengawas.store');
            Route::post('/sesi-ruang/assign-pengawas', [CbtSesiRuangController::class, 'assignPengawas'])->name('sesi_ruang.assign_pengawas');
            Route::delete('/pengawas/{id}', [CbtSesiRuangController::class, 'deletePengawas'])->name('pengawas.destroy');
            Route::delete('/sesi-ruang/delete-pengawas/{id}', [CbtSesiRuangController::class, 'deletePengawas'])->name('sesi_ruang.delete_pengawas');

            // Token Ujian
            Route::get('/token', [CbtTokenController::class, 'index'])->name('token.index');
            Route::post('/token/generate', [CbtTokenController::class, 'generate'])->name('token.generate');
            Route::get('/token/api-get', [CbtTokenController::class, 'apiGetToken'])->name('token.api_get');
            Route::get('/token/api', [CbtTokenController::class, 'apiGetToken'])->name('token.api');

            // Alokasi Sesi & Ruang Peserta
            Route::get('/alokasi-sesi', [CbtAlokasiController::class, 'indexSesiSiswa'])->name('alokasi.sesi');
            Route::post('/alokasi-sesi', [CbtAlokasiController::class, 'storeSesiSiswa'])->name('alokasi.sesi.store');
            Route::post('/alokasi-sesi/auto', [CbtAlokasiController::class, 'autoAlokasi'])->name('alokasi.sesi.auto');

            // Nomor Peserta
            Route::get('/nomor-peserta', [CbtAlokasiController::class, 'indexNomorPeserta'])->name('alokasi.nomor');
            Route::post('/nomor-peserta/generate', [CbtAlokasiController::class, 'generateNomorPeserta'])->name('alokasi.nomor.generate');

            // Alokasi Mapel Pilihan Siswa (Kurikulum Merdeka Fase F)
            Route::get('/alokasi-pilihan', [CbtAlokasiPilihanController::class, 'index'])->name('alokasi.pilihan');
            Route::post('/alokasi-pilihan/save', [CbtAlokasiPilihanController::class, 'saveMatrix'])->name('alokasi.pilihan.save');
            Route::post('/alokasi-pilihan/matrix', [CbtAlokasiPilihanController::class, 'saveMatrix'])->name('alokasi.pilihan.matrix');
            Route::get('/alokasi-pilihan/template', [CbtAlokasiPilihanController::class, 'downloadTemplate'])->name('alokasi.pilihan.template');
            Route::post('/alokasi-pilihan/import', [CbtAlokasiPilihanController::class, 'import'])->name('alokasi.pilihan.import');

            // Analisis Butir Soal & Rekap Nilai
            Route::get('/analisis', [CbtAnalisisController::class, 'index'])->name('analisis');

            // Rekap Nilai & Koreksi
            Route::get('/nilai', [CbtNilaiController::class, 'index'])->name('nilai.index');
            Route::get('/nilai/koreksi/{id}', [CbtNilaiController::class, 'showJawaban'])->name('nilai.koreksi');
            Route::post('/nilai/koreksi/{id}', [CbtNilaiController::class, 'updateKoreksi'])->name('nilai.update_koreksi');
            Route::get('/nilai/export/{jadwalId}', [CbtNilaiController::class, 'export'])->name('nilai.export');
        });

        // Manajemen Pengguna (User Management)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/admin', [UserManagementController::class, 'indexAdmin'])->name('admin');
            Route::post('/admin', [UserManagementController::class, 'storeAdmin'])->name('admin.store');
            Route::get('/guru', [UserManagementController::class, 'indexGuru'])->name('guru');
            Route::get('/siswa', [UserManagementController::class, 'indexSiswa'])->name('siswa');
            Route::post('/reset-password/{id}', [UserManagementController::class, 'resetPassword'])->name('reset_password');
            Route::post('/toggle-active/{id}', [UserManagementController::class, 'toggleActive'])->name('toggle_active');
            Route::get('/logs', [UserManagementController::class, 'indexLogs'])->name('logs');
        });

        // Pengaturan Sekolah & Pemeliharaan Database
        Route::prefix('setting')->name('setting.')->group(function () {
            Route::get('/identitas', [SchoolSettingController::class, 'index'])->name('identitas');
            Route::post('/identitas', [SchoolSettingController::class, 'update'])->name('identitas.update');

            Route::get('/database', [DatabaseManagerController::class, 'index'])->name('database');
            Route::get('/database/backup', [DatabaseManagerController::class, 'backup'])->name('database.backup');
            Route::post('/database/clear', [DatabaseManagerController::class, 'clearData'])->name('database.clear');

            Route::get('/maintenance', [SystemMaintenanceController::class, 'index'])->name('maintenance');
            Route::post('/maintenance/reset-locks', [SystemMaintenanceController::class, 'resetAllDeviceLocks'])->name('maintenance.reset_locks');
            Route::post('/maintenance/clear-completed', [SystemMaintenanceController::class, 'clearCompletedSessions'])->name('maintenance.clear_completed');
        });
    });

    // =========================================================================
    // 2. LEVEL GURU / PENGAWAS (ROLE: GURU ATAU ADMIN)
    // =========================================================================
    Route::prefix('guru')->name('guru.')->middleware(['role:guru,admin'])->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

        // Bank Soal
        Route::get('/bank-soal', [GuruBankSoalController::class, 'index'])->name('bank_soal.index');
        Route::post('/bank-soal', [GuruBankSoalController::class, 'store'])->name('bank_soal.store');
        Route::get('/bank-soal/template/{format?}', [GuruBankSoalController::class, 'downloadTemplate'])->name('bank_soal.template');
        Route::get('/bank-soal/{id}', [GuruBankSoalController::class, 'show'])->name('bank_soal.show');
        Route::get('/bank-soal/{id}/buat-soal', [GuruBankSoalController::class, 'buatSoal'])->name('bank_soal.buat_soal');
        Route::get('/bank-soal/{id}/soal/{soalId}/edit', [GuruBankSoalController::class, 'editSoal'])->name('bank_soal.edit_soal');
        Route::put('/bank-soal/soal/{soalId}', [GuruBankSoalController::class, 'updateSoal'])->name('bank_soal.update_soal');
        Route::post('/bank-soal/soal/{soalId}', [GuruBankSoalController::class, 'updateSoal'])->name('bank_soal.update_soal_post');
        Route::post('/bank-soal/{id}/soal', [GuruBankSoalController::class, 'storeSoal'])->name('bank_soal.store_soal');
        Route::post('/bank-soal/{id}/store-soal', [GuruBankSoalController::class, 'storeSoal'])->name('bank_soal.soal.store');
        Route::delete('/bank-soal/soal/{id}', [GuruBankSoalController::class, 'deleteSoal'])->name('bank_soal.delete_soal');
        Route::get('/bank-soal/{id}/import', [GuruBankSoalController::class, 'importView'])->name('bank_soal.import');
        Route::post('/bank-soal/{id}/import', [GuruBankSoalController::class, 'importSoal'])->name('bank_soal.import_process');

        // Jadwal & Pengawasan
        Route::get('/jadwal', [GuruJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/pengawasan', [GuruPengawasanController::class, 'index'])->name('pengawasan.index');
        Route::get('/pengawasan/monitor/{jadwalId}', [GuruPengawasanController::class, 'monitor'])->name('pengawasan.monitor');
        Route::get('/token', [GuruTokenController::class, 'index'])->name('token.index');
        Route::get('/token/api', [GuruTokenController::class, 'apiGetToken'])->name('token.api');

        // Hasil & Penilaian
        Route::get('/hasil', [GuruHasilController::class, 'index'])->name('hasil.index');
        Route::get('/hasil/export/{jadwalId}', [GuruHasilController::class, 'export'])->name('hasil.export');
        Route::get('/analisis', [GuruHasilController::class, 'analisis'])->name('analisis.index');
        Route::get('/koreksi', [GuruKoreksiController::class, 'index'])->name('koreksi.index');
        Route::get('/koreksi/peserta/{jadwalId}', [GuruKoreksiController::class, 'showPeserta'])->name('koreksi.peserta');
        Route::get('/koreksi/form/{cbtSiswaId}', [GuruKoreksiController::class, 'showFormKoreksi'])->name('koreksi.form');
        Route::post('/koreksi/form/{cbtSiswaId}', [GuruKoreksiController::class, 'storeKoreksi'])->name('koreksi.store');

        // Profil Guru
        Route::get('/profil', [GuruProfilController::class, 'index'])->name('profil');
        Route::post('/profil/password', [GuruProfilController::class, 'updatePassword'])->name('profil.password');

        // Wali Kelas (Khusus Guru yang ditugaskan sebagai Wali Kelas)
        Route::prefix('wali')->name('wali.')->group(function () {
            Route::get('/siswa', [GuruWaliKelasController::class, 'indexSiswa'])->name('siswa');
            Route::get('/struktur', [GuruWaliKelasController::class, 'indexStruktur'])->name('struktur');
            Route::post('/struktur', [GuruWaliKelasController::class, 'saveStruktur'])->name('struktur.save');
            Route::get('/catatan', [GuruWaliKelasController::class, 'indexCatatan'])->name('catatan');
            Route::post('/catatan', [GuruWaliKelasController::class, 'storeCatatan'])->name('catatan.store');
        });
    });

    // =========================================================================
    // 3. LEVEL SISWA (PELAKSANAAN UJIAN CBT ZERO-LATENCY)
    // =========================================================================
    Route::get('/cbt/ujian/{jadwalId}', [ExamViewController::class, 'showExam'])->name('cbt.ujian');

    Route::prefix('exam')->name('exam.')->group(function () {
        Route::get('/', [ExamSessionController::class, 'index'])->name('index');
        Route::get('/konfirmasi/{jadwalId}', [ExamSessionController::class, 'konfirmasi'])->name('konfirmasi');
        Route::post('/konfirmasi/{jadwalId}', [ExamSessionController::class, 'prosesKonfirmasi'])->name('konfirmasi.proses');
        Route::get('/hasil/{jadwalId}', [ExamSessionController::class, 'hasil'])->name('hasil');
        Route::get('/session/{jadwalId}', [ExamViewController::class, 'showExam'])->name('session');

        // High-Concurrency API Endpoints
        Route::post('/api/start/{jadwalId}', [ExamSessionController::class, 'apiStartExam'])->name('api.start');
        Route::post('/api/autosave', [ExamSessionController::class, 'apiAutoSave'])->name('api.autosave');
        Route::post('/api/autosave/{jadwalId}', [ExamSessionController::class, 'apiAutoSave'])->name('api.autosave_param');
        Route::post('/api/violation', [ExamSessionController::class, 'apiRecordViolation'])->name('api.violation');
        Route::get('/api/sync-timer/{jadwalId}', [ExamSessionController::class, 'apiSyncTimer'])->name('api.sync_timer');
        Route::post('/api/finish/{jadwalId}', [ExamSessionController::class, 'apiFinishExam'])->name('api.finish');
    });

    // Alias High-Performance CBT Endpoint: /api/cbt/...
    Route::post('/api/cbt/autosave/{jadwalId}', [ExamSessionController::class, 'apiAutoSave'])->name('cbt.api.autosave');
    Route::post('/api/cbt/finish/{jadwalId}', [ExamSessionController::class, 'apiFinishExam'])->name('cbt.api.finish');

    // =========================================================================
    // 4. MODUL PROKTOR & LIVE MONITORING (PENGAWAS RUANG UJIAN)
    // =========================================================================
    Route::prefix('proctor')->name('proctor.')->group(function () {
        Route::get('/', [ProctorController::class, 'index'])->name('index');
        Route::get('/monitor/{jadwalId}', [ProctorController::class, 'monitor'])->name('monitor');

        // Live Real-Time Poll & Remote Actions
        Route::get('/api/live/{jadwalId}', [ProctorController::class, 'apiLiveStatus'])->name('api.live');
        Route::get('/api/poll/{jadwalId}', [ProctorController::class, 'apiLiveStatus'])->name('api.poll');
        Route::post('/api/reset-login/{jadwalId}/{siswaId}', [ProctorController::class, 'apiResetLogin'])->name('api.reset_login');
        Route::post('/api/force-submit/{jadwalId}/{siswaId}', [ProctorController::class, 'apiForceSubmit'])->name('api.force_submit');
        Route::post('/api/extra-time/{jadwalId}/{siswaId}', [ProctorController::class, 'apiAddExtraTime'])->name('api.extra_time');
        Route::post('/action/reset-login', [ProctorController::class, 'actionResetLogin'])->name('action.reset_login');
        Route::post('/action/extend-time', [ProctorController::class, 'actionExtendTime'])->name('action.extend_time');
        Route::post('/action/force-submit', [ProctorController::class, 'actionForceSubmit'])->name('action.force_submit');

        // Token Management
        Route::post('/api/token/generate', [ProctorController::class, 'apiGenerateToken'])->name('api.token.generate');
        Route::get('/api/token/get', [ProctorController::class, 'apiGetToken'])->name('api.token.get');
    });

    // =========================================================================
    // 5. MODUL CETAK DOKUMEN UJIAN (CSS PRINT-FRIENDLY)
    // =========================================================================
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/kartu-peserta', [DocumentPrintController::class, 'kartuPeserta'])->name('kartu_peserta');
        Route::get('/daftar-hadir/{jadwalId}', [DocumentPrintController::class, 'daftarHadir'])->name('daftar_hadir');
        Route::get('/daftar-hadir-pengawas', [DocumentPrintController::class, 'daftarHadirPengawas'])->name('daftar_hadir_pengawas');
        Route::get('/berita-acara/{jadwalId}', [DocumentPrintController::class, 'beritaAcara'])->name('berita_acara');
        Route::get('/denah-ruang', [DocumentPrintController::class, 'denahRuang'])->name('denah_ruang');
        Route::get('/jadwal-pengawas', [DocumentPrintController::class, 'jadwalPengawas'])->name('jadwal_pengawas');
        Route::get('/kartu-login', [DocumentPrintController::class, 'kartuLogin'])->name('kartu_login');
    });

});

// Legacy CodeIgniter route compatibility aliases (support http://localhost/us1/dataguru/editJabatan/1)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dataguru', fn() => redirect()->route('admin.master.guru'));
    Route::get('/us1/dataguru', fn() => redirect()->route('admin.master.guru'));
    Route::get('/dataguru/editJabatan/{id}', fn($id) => redirect()->route('admin.master.guru.edit_jabatan', $id));
    Route::get('/dataguru/editjabatan/{id}', fn($id) => redirect()->route('admin.master.guru.edit_jabatan', $id));
    Route::get('/us1/dataguru/editJabatan/{id}', fn($id) => redirect()->route('admin.master.guru.edit_jabatan', $id));
    Route::get('/us1/dataguru/editjabatan/{id}', fn($id) => redirect()->route('admin.master.guru.edit_jabatan', $id));
    Route::post('/dataguru/saveJabatan', function (\Illuminate\Http\Request $request) {
        $guruId = $request->input('id_guru');
        if ($request->filled('copy')) {
            return app(\App\Http\Controllers\Admin\MasterDataController::class)->copyJabatanGuru($request, (int)$guruId);
        }
        return app(\App\Http\Controllers\Admin\MasterDataController::class)->updateJabatanGuru($request, (int)$guruId);
    });
    Route::post('/us1/dataguru/saveJabatan', function (\Illuminate\Http\Request $request) {
        $guruId = $request->input('id_guru');
        if ($request->filled('copy')) {
            return app(\App\Http\Controllers\Admin\MasterDataController::class)->copyJabatanGuru($request, (int)$guruId);
        }
        return app(\App\Http\Controllers\Admin\MasterDataController::class)->updateJabatanGuru($request, (int)$guruId);
    });

    // Datasiswa legacy routes
    Route::get('/datasiswa', fn() => redirect()->route('admin.master.siswa'));
    Route::get('/us1/datasiswa', fn() => redirect()->route('admin.master.siswa'));
    Route::post('/datasiswa/list', [MasterDataController::class, 'ajaxListSiswa']);
    Route::post('/us1/datasiswa/list', [MasterDataController::class, 'ajaxListSiswa']);
    Route::post('/datasiswa/create', [MasterDataController::class, 'storeSiswa']);
    Route::post('/us1/datasiswa/create', [MasterDataController::class, 'storeSiswa']);
    Route::post('/datasiswa/delete', [MasterDataController::class, 'bulkActionSiswa']);
    Route::post('/us1/datasiswa/delete', [MasterDataController::class, 'bulkActionSiswa']);
    Route::get('/datasiswa/downloadData/{id_kelas?}', [MasterDataController::class, 'exportSiswa']);
    Route::get('/us1/datasiswa/downloadData/{id_kelas?}', [MasterDataController::class, 'exportSiswa']);
});


