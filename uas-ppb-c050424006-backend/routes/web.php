<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GradingTemplateController;
use App\Http\Controllers\Admin\InstitusiReferensiController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\MataKuliahController;
use App\Http\Controllers\Portal\NilaiController;
use App\Http\Controllers\Portal\NilaiInputController;
use App\Http\Controllers\Portal\NilaiSayaController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\Portal\RekapitulasiController;
use App\Http\Controllers\Portal\TahunAkademikController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->role === 'super_admin'
        ? redirect('/admin/dashboard')
        : redirect('/portal/dashboard');
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->role === 'super_admin'
        ? redirect('/admin/dashboard')
        : redirect('/portal/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Portal User Routes
Route::prefix('portal')->middleware(['auth', 'portal.user'])->group(function () {
    Route::get('dashboard', [PortalDashboardController::class, 'index'])->name('portal.dashboard');
    Route::resource('tahun-akademik', TahunAkademikController::class)->names('portal.tahun-akademik');
    Route::resource('mata-kuliah', MataKuliahController::class)->names('portal.mata-kuliah');
    Route::get('nilai-saya', [NilaiSayaController::class, 'index'])->name('portal.nilai-saya');
    Route::get('mata-kuliah/{id}/nilai', [NilaiInputController::class, 'edit'])->name('portal.nilai-input.edit');
    Route::put('mata-kuliah/{id}/nilai', [NilaiInputController::class, 'update'])->name('portal.nilai-input.update');
    Route::post('mata-kuliah/{id}/nilai/template', [NilaiInputController::class, 'template'])->name('portal.nilai-input.template');
    Route::delete('mata-kuliah/{mataKuliahId}/nilai/{id}', [NilaiInputController::class, 'destroy'])->name('portal.nilai-input.destroy');
    Route::get('nilai-akhir/{mataKuliahId}', [NilaiController::class, 'nilaiAkhir'])->name('portal.nilai-akhir');
    Route::get('nilai/ips-ipk', [NilaiController::class, 'ipsIpk'])->name('portal.nilai.ips-ipk');
    Route::put('nilai/ips-ipk/target', [NilaiController::class, 'updateTargetIpK'])->name('portal.nilai.target-ipk.update');
    Route::get('rekapitulasi', [RekapitulasiController::class, 'index'])->name('portal.rekapitulasi');
    Route::get('rekapitulasi/export-pdf', [RekapitulasiController::class, 'exportPdf'])->name('portal.rekapitulasi.export-pdf');
    Route::get('profil', [PortalProfileController::class, 'show'])->name('portal.profil.show');
    Route::get('profil/edit', [PortalProfileController::class, 'edit'])->name('portal.profil.edit');
    Route::put('profil', [PortalProfileController::class, 'update'])->name('portal.profil.update');
    Route::get('grading-templates', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'index'])->name('portal.grading-templates.index');
    Route::post('grading-templates', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'store'])->name('portal.grading-templates.store');
    Route::post('grading-templates/{id}/items', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'storeItems'])->name('portal.grading-templates.items.store');
    // Grading management for students
    Route::get('grading', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'index'])->name('portal.grading');
    Route::post('grading/{id}/aktifkan', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'setActive'])->name('portal.grading.set-active');
    Route::get('grading/create', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'create'])->name('portal.grading.create');
    Route::post('grading', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'store'])->name('portal.grading.store');
    Route::delete('grading/{id}', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'destroy'])->name('portal.grading.destroy');
    Route::get('grading/{id}/edit', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'edit'])->name('portal.grading.edit');
    Route::put('grading/{id}', [\App\Http\Controllers\Portal\GradingTemplateController::class, 'update'])->name('portal.grading.update');
});

// Admin Super Admin Routes
Route::prefix('admin')->middleware(['auth', 'web.superadmin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    Route::get('user-management', [UserManagementController::class, 'index'])->name('admin.user-management.index');
    Route::post('user-management/{id}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('admin.user-management.toggle-active');
    Route::delete('users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('users-trashed', [UserManagementController::class, 'trashed'])->name('admin.users.trashed');
    Route::put('users/{id}/restore', [UserManagementController::class, 'restore'])->name('admin.users.restore');
    Route::get('users/{id}/force-confirm', [UserManagementController::class, 'confirmForceDelete'])->name('admin.users.force-delete.confirm');
    Route::delete('users/{id}/force', [UserManagementController::class, 'forceDelete'])->name('admin.users.force-delete');
    Route::get('user-management/{id}', [UserManagementController::class, 'showReadOnly'])->name('admin.user-management.show');
    
    Route::resource('grading-template', GradingTemplateController::class)->names('admin.grading-template');
    
    Route::resource('institusi-referensi', InstitusiReferensiController::class)->names('admin.institusi-referensi');
    
    Route::get('system/audit-logs', [SystemController::class, 'auditLogs'])->name('admin.system.audit-logs');
    Route::get('system/error-logs', [SystemController::class, 'errorLogs'])->name('admin.system.error-logs');
    Route::post('system/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('admin.system.toggle-maintenance');
});

require __DIR__.'/auth.php';
