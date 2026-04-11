<?php

use Illuminate\Support\Facades\Route;
use Modules\Jsu\Http\Controllers\JsuController;

Route::middleware('auth')->prefix('jsu')->name('jsu.')->group(function (): void {

    // ── Web management pages ─────────────────────────────────────────────
    Route::prefix('/manage')->name('manage.')->group(function (): void {
        Route::get('/', [JsuController::class, 'manageIndex'])->name('index');
        Route::get('/create', [JsuController::class, 'manageCreate'])->name('create');
        Route::post('/', [JsuController::class, 'manageStore'])->name('store');
        Route::get('/{jsu}', [JsuController::class, 'manageShow'])->name('show');
        Route::post('/{jsu}/blueprints', [JsuController::class, 'manageStoreBlueprint'])->name('blueprints.store');
        Route::delete('/{jsu}/blueprints/{blueprint}', [JsuController::class, 'manageDestroyBlueprint'])->name('blueprints.destroy');
        Route::post('/{jsu}/submit', [JsuController::class, 'manageSubmit'])->name('submit');
        Route::post('/{jsu}/approve', [JsuController::class, 'manageApprove'])->name('approve');
        Route::post('/{jsu}/reject', [JsuController::class, 'manageReject'])->name('reject');
        Route::post('/{jsu}/activate', [JsuController::class, 'manageActivate'])->name('activate');
    });

    // ── Core CRUD ─────────────────────────────────────────────────────────
    Route::get('/',          [JsuController::class, 'index'])->name('index');
    Route::post('/',         [JsuController::class, 'store'])->name('store');
    Route::get('/{jsu}',    [JsuController::class, 'show'])->name('show');
    Route::put('/{jsu}',    [JsuController::class, 'update'])->name('update');
    Route::delete('/{jsu}', [JsuController::class, 'destroy'])->name('destroy');

    // ── Workflow actions ───────────────────────────────────────────────────
    Route::post('/{jsu}/submit',   [JsuController::class, 'submit'])->name('submit');
    Route::post('/{jsu}/approve',  [JsuController::class, 'approve'])->name('approve');
    Route::post('/{jsu}/reject',   [JsuController::class, 'reject'])->name('reject');
    Route::post('/{jsu}/activate', [JsuController::class, 'activate'])->name('activate');

    // ── Blueprint ─────────────────────────────────────────────────────────
    Route::get('/{jsu}/blueprints',               [JsuController::class, 'blueprints'])->name('blueprints.index');
    Route::post('/{jsu}/blueprints',              [JsuController::class, 'storeBlueprint'])->name('blueprints.store');
    Route::delete('/{jsu}/blueprints/{blueprint}', [JsuController::class, 'destroyBlueprint'])->name('blueprints.destroy');

    // ── Distribution report ────────────────────────────────────────────────
    Route::get('/{jsu}/distribution', [JsuController::class, 'distribution'])->name('distribution');

    // ── Audit logs ────────────────────────────────────────────────────────
    Route::get('/{jsu}/logs', [JsuController::class, 'logs'])->name('logs');
});
