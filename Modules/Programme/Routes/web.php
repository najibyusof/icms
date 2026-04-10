<?php

use Illuminate\Support\Facades\Route;
use Modules\Programme\Http\Controllers\ProgrammeController;

Route::middleware('auth')->group(function (): void {
    // Main programme routes
    Route::prefix('programmes')->name('programmes.')->group(function (): void {
        Route::get('/', [ProgrammeController::class, 'index'])->name('index');
        Route::get('/api/list', [ProgrammeController::class, 'listJson'])->name('list');
        Route::get('/create', [ProgrammeController::class, 'create'])->name('create');
        Route::post('/', [ProgrammeController::class, 'store'])->name('store');
        Route::get('/{programme}', [ProgrammeController::class, 'show'])->name('show');
        Route::get('/{programme}/edit', [ProgrammeController::class, 'edit'])->name('edit');
        Route::put('/{programme}', [ProgrammeController::class, 'update'])->name('update');
        Route::delete('/{programme}', [ProgrammeController::class, 'destroy'])->name('destroy');

        // Programme Learning Outcomes (PLO)
        Route::post('/{programme}/plos', [ProgrammeController::class, 'storePLO'])->name('plos.store');
        Route::put('/plos/{plo}', [ProgrammeController::class, 'updatePLO'])->name('plos.update');
        Route::delete('/plos/{plo}', [ProgrammeController::class, 'deletePLO'])->name('plos.delete');

        // Programme Educational Objectives (PEO)
        Route::post('/{programme}/peos', [ProgrammeController::class, 'storePEO'])->name('peos.store');
        Route::put('/peos/{peo}', [ProgrammeController::class, 'updatePEO'])->name('peos.update');
        Route::delete('/peos/{peo}', [ProgrammeController::class, 'deletePEO'])->name('peos.delete');

        // Study Plans
        Route::post('/{programme}/study-plans', [ProgrammeController::class, 'storeStudyPlan'])->name('study-plans.store');
        Route::put('/study-plans/{studyPlan}', [ProgrammeController::class, 'updateStudyPlan'])->name('study-plans.update');
        Route::delete('/study-plans/{studyPlan}', [ProgrammeController::class, 'deleteStudyPlan'])->name('study-plans.delete');
        Route::get('/study-plans/{studyPlan}/courses', [ProgrammeController::class, 'getStudyPlanCourses'])->name('study-plans.courses');

        // CLO-PLO Mappings
        Route::post('/mappings', [ProgrammeController::class, 'storeMapping'])->name('mappings.store');
        Route::get('/{programme}/mappings/courses/{courseId}', [ProgrammeController::class, 'getCourseMappings'])->name('mappings.by-course');
        Route::get('/{programme}/mappings/matrix', [ProgrammeController::class, 'getMappingMatrix'])->name('mappings.matrix');
        Route::delete('/mappings/{mapping}', [ProgrammeController::class, 'deleteMapping'])->name('mappings.delete');
        Route::get('/{programme}/mappings/coverage', [ProgrammeController::class, 'getCLOCoverageReport'])->name('mappings.coverage');

        // Programme Chair & Workflow
        Route::post('/{programme}/assign-chair/{userId}', [ProgrammeController::class, 'assignProgrammeChair'])->name('assign-chair');
        Route::post('/{programme}/submit-for-approval', [ProgrammeController::class, 'submitForApproval'])->name('submit-for-approval');
    });
});

