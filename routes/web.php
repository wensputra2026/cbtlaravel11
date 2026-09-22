<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CbtAlokasiController;
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
use App\Http\Controllers\ProctorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Garuda CBT Next Gen (Laravel 11 Engine)
|--------------------------------------------------------------------------
*/

// Autentikasi Publik & Kompatibilitas Garuda CBT (/auth & /login)
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Kompatibel Penuh Garuda CBT (/auth, /auth/login, /auth/logout)
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('index');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
});

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
            Route::post('/jurusan/update/{id}', [MasterDataController::class, 'updateJurusan'])->name('jurusan.update.post');
            Route::delete('/jurusan/{id}', [MasterDataController::class, 'destroyJurusan'])->name('jurusan.destroy');
            Route::post('/jurusan/delete/{id}', [MasterDataController::class, 'destroyJurusan'])->name('jurusan.delete.post');

            Route::get('/kelas', [MasterDataController::class, 'indexKelas'])->name('kelas');
            Route::post('/kelas', [MasterDataController::class, 'storeKelas'])->name('kelas.store');

            Route::get('/mapel', [MasterDataController::class, 'indexMapel'])->name('mapel');
            Route::post('/mapel', [MasterDataController::class, 'storeMapel'])->name('mapel.store');
            Route::put('/mapel/{id}', [MasterDataController::class, 'updateMapel'])->name('mapel.update');
            Route::post('/mapel/update/{id}', [MasterDataController::class, 'updateMapel'])->name('mapel.update.post');
            Route::delete('/mapel/{id}', [MasterDataController::class, 'destroyMapel'])->name('mapel.destroy');
            Route::post('/mapel/delete/{id}', [MasterDataController::class, 'destroyMapel'])->name('mapel.delete.post');
            Route::post('/mapel/toggle-status/{id}', [MasterDataController::class, 'toggleStatusMapel'])->name('mapel.toggle_status');
            Route::post('/mapel/kelompok', [MasterDataController::class, 'storeKelompokMapel'])->name('mapel.kelompok.store');
            Route::delete('/mapel/kelompok/{id}', [MasterDataController::class, 'destroyKelompokMapel'])->name('mapel.kelompok.destroy');
            Route::post('/mapel/kelompok/delete/{id}', [MasterDataController::class, 'destroyKelompokMapel'])->name('mapel.kelompok.delete.post');
            Route::post('/mapel/import', [MasterDataController::class, 'importMapel'])->name('mapel.import');

            Route::get('/guru', [MasterDataController::class, 'indexGuru'])->name('guru');
            Route::post('/guru', [MasterDataController::class, 'storeGuru'])->name('guru.store');

            Route::get('/siswa', [MasterDataController::class, 'indexSiswa'])->name('siswa');
            Route::post('/siswa', [MasterDataController::class, 'storeSiswa'])->name('siswa.store');
            Route::post('/siswa/import', [MasterDataController::class, 'importSiswa'])->name('siswa.import');

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
            Route::get('/bank-soal/create', [CbtBankSoalController::class, 'addBank'])->name('bank_soal.create');
            Route::get('/bank-soal/{id}/edit', [CbtBankSoalController::class, 'editBank'])->name('bank_soal.edit');
            Route::post('/bank-soal', [CbtBankSoalController::class, 'store'])->name('bank_soal.store');
            Route::post('/bank-soal/save-bank', [CbtBankSoalController::class, 'saveBank'])->name('bank_soal.save_bank');
            Route::get('/bank-soal/get-kelas-level', [CbtBankSoalController::class, 'getKelasLevel'])->name('bank_soal.get_kelas_level');
            Route::get('/bank-soal/get-guru-mapel', [CbtBankSoalController::class, 'getGuruMapel'])->name('bank_soal.get_guru_mapel');
            Route::post('/bank-soal/update/{id}', [CbtBankSoalController::class, 'update'])->name('bank_soal.update');
            Route::get('/bank-soal/data/{id}', [CbtBankSoalController::class, 'getBankJson'])->name('bank_soal.json');
            Route::match(['get', 'post'], '/bank-soal/duplicate/{id}', [CbtBankSoalController::class, 'duplicate'])->name('bank_soal.duplicate');
            Route::delete('/bank-soal/{id}', [CbtBankSoalController::class, 'destroy'])->name('bank_soal.destroy');
            Route::post('/bank-soal/bulk-delete', [CbtBankSoalController::class, 'bulkDelete'])->name('bank_soal.bulk_delete');
            Route::get('/bank-soal/soal-siswa/{id}', [CbtBankSoalController::class, 'getSoalSiswa'])->name('bank_soal.soal_siswa');
            Route::get('/bank-soal/download-word/{id}', [CbtBankSoalController::class, 'downloadDocx'])->name('bank_soal.download_word');
            Route::get('/bank-soal/template/{format?}', [CbtBankSoalController::class, 'downloadTemplate'])->name('bank_soal.template');
            Route::get('/bank-soal/{id}/import', [CbtBankSoalController::class, 'importView'])->name('bank_soal.import');
            Route::post('/bank-soal/{id}/import', [CbtBankSoalController::class, 'importSoal'])->name('bank_soal.import_process');
            Route::get('/bank-soal/{id}', [CbtBankSoalController::class, 'show'])->name('bank_soal.show');
            Route::post('/bank-soal/save-selected', [CbtBankSoalController::class, 'saveSelected'])->name('bank_soal.save_selected');
            Route::post('/bank-soal/hapus-soal', [CbtBankSoalController::class, 'hapusSoalAjax'])->name('bank_soal.hapus_soal_ajax');
            Route::post('/bank-soal/reset-number', [CbtBankSoalController::class, 'resetNumber'])->name('bank_soal.reset_number');
            Route::post('/bank-soal/get-detail', [CbtBankSoalController::class, 'getDetailAjax'])->name('bank_soal.get_detail');
            Route::post('/bank-soal/{id}/soal', [CbtBankSoalController::class, 'storeSoal'])->name('bank_soal.store_soal');
            Route::post('/bank-soal/{id}/soal/create', [CbtBankSoalController::class, 'storeSoal'])->name('bank_soal.soal.store');
            Route::delete('/bank-soal/soal/{id}', [CbtBankSoalController::class, 'deleteSoal'])->name('bank_soal.delete_soal');
            Route::delete('/bank-soal/soal/{id}/destroy', [CbtBankSoalController::class, 'deleteSoal'])->name('bank_soal.soal.destroy');

            // Legacy Garuda CBT AJAX endpoint compatibility
            Route::post('/cbtbanksoal/saveSelected', [CbtBankSoalController::class, 'saveSelected']);
            Route::post('/cbtbanksoal/hapussoal', [CbtBankSoalController::class, 'hapusSoalAjax']);
            Route::post('/cbtbanksoal/resetNumber', [CbtBankSoalController::class, 'resetNumber']);
            Route::post('/cbtbanksoal/get_detail', [CbtBankSoalController::class, 'getDetailAjax']);

            // Jadwal & Jenis Ujian
            Route::get('/jadwal', [CbtJadwalController::class, 'index'])->name('jadwal.index');
            Route::post('/jadwal', [CbtJadwalController::class, 'store'])->name('jadwal.store');
            Route::post('/jadwal/toggle/{id}', [CbtJadwalController::class, 'toggleStatus'])->name('jadwal.toggle');
            Route::delete('/jadwal/{id}', [CbtJadwalController::class, 'destroy'])->name('jadwal.destroy');

            Route::get('/jenis', [CbtJadwalController::class, 'indexJenis'])->name('jenis.index');
            Route::post('/jenis', [CbtJadwalController::class, 'storeJenis'])->name('jenis.store');
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
        Route::get('/bank-soal/create', [GuruBankSoalController::class, 'addBank'])->name('bank_soal.create');
        Route::get('/bank-soal/{id}/edit', [GuruBankSoalController::class, 'editBank'])->name('bank_soal.edit');
        Route::post('/bank-soal', [GuruBankSoalController::class, 'store'])->name('bank_soal.store');
        Route::post('/bank-soal/save-bank', [GuruBankSoalController::class, 'saveBank'])->name('bank_soal.save_bank');
        Route::get('/bank-soal/get-kelas-level', [GuruBankSoalController::class, 'getKelasLevel'])->name('bank_soal.get_kelas_level');
        Route::get('/bank-soal/get-guru-mapel', [GuruBankSoalController::class, 'getGuruMapel'])->name('bank_soal.get_guru_mapel');
        Route::post('/bank-soal/update/{id}', [GuruBankSoalController::class, 'update'])->name('bank_soal.update');
        Route::get('/bank-soal/data/{id}', [GuruBankSoalController::class, 'getBankJson'])->name('bank_soal.json');
        Route::match(['get', 'post'], '/bank-soal/duplicate/{id}', [GuruBankSoalController::class, 'duplicate'])->name('bank_soal.duplicate');
        Route::delete('/bank-soal/{id}', [GuruBankSoalController::class, 'destroy'])->name('bank_soal.destroy');
        Route::post('/bank-soal/bulk-delete', [GuruBankSoalController::class, 'bulkDelete'])->name('bank_soal.bulk_delete');
        Route::get('/bank-soal/soal-siswa/{id}', [GuruBankSoalController::class, 'getSoalSiswa'])->name('bank_soal.soal_siswa');
        Route::get('/bank-soal/download-word/{id}', [GuruBankSoalController::class, 'downloadDocx'])->name('bank_soal.download_word');
        Route::get('/bank-soal/template/{format?}', [GuruBankSoalController::class, 'downloadTemplate'])->name('bank_soal.template');
        Route::get('/bank-soal/{id}', [GuruBankSoalController::class, 'show'])->name('bank_soal.show');
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
        Route::post('/koreksi/tandai/{cbtSiswaId}', [GuruKoreksiController::class, 'tandaiDikoreksi'])->name('koreksi.tandai');

        // Profil Guru
        Route::get('/profil', [GuruProfilController::class, 'index'])->name('profil');
        Route::post('/profil/password', [GuruProfilController::class, 'updatePassword'])->name('profil.password');
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
        Route::post('/api/violation', [ExamSessionController::class, 'apiRecordViolation'])->name('api.violation');
        Route::get('/api/sync-timer/{jadwalId}', [ExamSessionController::class, 'apiSyncTimer'])->name('api.sync_timer');
        Route::post('/api/finish/{jadwalId}', [ExamSessionController::class, 'apiFinishExam'])->name('api.finish');
    });

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

    // Compatibility for GarudaCBT Koreksi URL: /cbtnilai/koreksi?id_siswa=X&id_jadwal=Y
    Route::get('/cbtnilai/koreksi', [GuruKoreksiController::class, 'legacyKoreksiRedirect']);

    // Compatibility for GarudaCBT Bank Soal URLs: /cbtbanksoal?type=0&mode=1
    Route::get('/cbtbanksoal', [GuruBankSoalController::class, 'index'])->name('cbtbanksoal.legacy');
    Route::match(['get', 'post'], '/cbtbanksoal/addBank', [GuruBankSoalController::class, 'addBank']);
    Route::match(['get', 'post'], '/cbtbanksoal/editBank', [GuruBankSoalController::class, 'editBank']);
    Route::post('/cbtbanksoal/saveBank', [GuruBankSoalController::class, 'saveBank']);
    Route::get('/cbtbanksoal/getkelaslevel', [GuruBankSoalController::class, 'getKelasLevel']);
    Route::get('/cbtbanksoal/getgurumapel', [GuruBankSoalController::class, 'getGuruMapel']);
    Route::match(['get', 'post'], '/cbtbanksoal/copybanksoal/{id}', [GuruBankSoalController::class, 'duplicate']);
    Route::match(['get', 'post'], '/cbtbanksoal/copyBankSoal/{id}', [GuruBankSoalController::class, 'duplicate']);
    Route::match(['get', 'post'], '/cbtbanksoal/deleteBank', [GuruBankSoalController::class, 'destroyLegacy']);
    Route::match(['get', 'post'], '/cbtbanksoal/deleteallbank', [GuruBankSoalController::class, 'bulkDelete']);
    Route::match(['get', 'post'], '/cbtbanksoal/deleteAllBank', [GuruBankSoalController::class, 'bulkDelete']);
    Route::get('/cbtbanksoal/getsoalsiswa/{id}', [GuruBankSoalController::class, 'getSoalSiswa']);
    Route::get('/cbtbanksoal/importsoal/{id}', [GuruBankSoalController::class, 'importView']);
    Route::get('/cbtbanksoal/detail/{id}', [GuruBankSoalController::class, 'show']);

});

