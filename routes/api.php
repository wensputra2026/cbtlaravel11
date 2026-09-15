<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamSessionController;
use App\Http\Controllers\Api\ProctorActionController;
use App\Http\Middleware\EnsureSingleExamDevice;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Garuda CBT Next (Laravel 11 High-Concurrency Engine)
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------------
// 1. AUTENTIKASI SISWA & PENGGUNA
// -------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// -------------------------------------------------------------
// 2. PANEL AKSI PROKTOR / PENGAWAS
// -------------------------------------------------------------
Route::prefix('proctor')->group(function () {
    Route::post('/reset-login', [ProctorActionController::class, 'resetLogin']);
    Route::post('/token/generate', [ProctorActionController::class, 'generateToken']);
    Route::get('/token/{jadwalId}', [ProctorActionController::class, 'getToken']);
});

// -------------------------------------------------------------
// 3. CORE CBT EXAM API (TAHAP 3 - ZERO LATENCY + FULL ANTI-CHEAT)
// -------------------------------------------------------------
Route::prefix('cbt')->middleware([EnsureSingleExamDevice::class])->group(function () {
    // Sesuai spesifikasi Tahap 3
    Route::match(['GET', 'POST'], '/start/{jadwalId}', [ExamSessionController::class, 'startExam']);
    Route::post('/autosave/{jadwalId}', [ExamSessionController::class, 'autoSave']);
    Route::post('/violation/{jadwalId}', [ExamSessionController::class, 'logCheatViolation']);
    Route::match(['GET', 'POST'], '/sync-timer/{jadwalId}', [ExamSessionController::class, 'syncTimer']);
    Route::post('/finish/{jadwalId}', [ExamSessionController::class, 'finishExam']);
});

// -------------------------------------------------------------
// 4. ALIAS /exam/... UNTUK KOMPATIBILITAS CLIENT
// -------------------------------------------------------------
Route::prefix('exam')->middleware([EnsureSingleExamDevice::class])->group(function () {
    Route::match(['GET', 'POST'], '/{jadwalId}/start', [ExamSessionController::class, 'startExam']);
    Route::post('/{jadwalId}/autosave', [ExamSessionController::class, 'autoSave']);
    Route::post('/{jadwalId}/violation', [ExamSessionController::class, 'logCheatViolation']);
    Route::match(['GET', 'POST'], '/{jadwalId}/sync-timer', [ExamSessionController::class, 'syncTimer']);
    Route::post('/{jadwalId}/finish', [ExamSessionController::class, 'finishExam']);

    Route::match(['GET', 'POST'], '/start/{jadwalId}', [ExamSessionController::class, 'startExam']);
    Route::post('/autosave/{jadwalId}', [ExamSessionController::class, 'autoSave']);
    Route::post('/violation/{jadwalId}', [ExamSessionController::class, 'logCheatViolation']);
    Route::match(['GET', 'POST'], '/sync-timer/{jadwalId}', [ExamSessionController::class, 'syncTimer']);
    Route::post('/finish/{jadwalId}', [ExamSessionController::class, 'finishExam']);
});
