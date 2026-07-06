<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardStatsController;
use App\Http\Controllers\Api\GradingTemplateController;
use App\Http\Controllers\Api\KomponenNilaiController;
use App\Http\Controllers\Api\MataKuliahController;
use App\Http\Controllers\Api\NilaiController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RekapitulasiController;
use App\Http\Controllers\Api\TahunAkademikController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('rekapitulasi/pdf-signed', [RekapitulasiController::class, 'streamSignedPdf'])
    ->middleware('signed')
    ->name('api.rekapitulasi.pdf.signed');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/profile/photo', [ProfileController::class, 'photo']);

    Route::apiResource('tahun-akademik', TahunAkademikController::class);
    Route::apiResource('mata-kuliah', MataKuliahController::class);
    Route::apiResource('komponen-nilai', KomponenNilaiController::class);
    Route::get('nilai-akhir/{mataKuliahId}', [NilaiController::class, 'nilaiAkhir']);
    Route::get('ips-ipk', [NilaiController::class, 'ipsIpk']);

    Route::get('rekapitulasi', [RekapitulasiController::class, 'index']);
    Route::get('rekapitulasi/pdf-url', [RekapitulasiController::class, 'generateSignedPdfUrl']);

    Route::apiResource('grading-templates', GradingTemplateController::class);
    Route::post('grading-templates/{id}/terapkan', [GradingTemplateController::class, 'terapkan']);

    Route::get('dashboard-stats', [DashboardStatsController::class, 'index']);

    Route::prefix('admin')->middleware(['role.superadmin'])->group(function () {
        // akan diisi di tahap berikutnya: users, dll
    });
});
