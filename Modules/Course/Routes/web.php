<?php

use Illuminate\Support\Facades\Route;
use Modules\Course\Http\Controllers\CourseController;

Route::middleware('auth')->prefix('courses')->name('courses.')->group(function (): void {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::post('/', [CourseController::class, 'store'])->name('store');

    Route::get('/manage/create', [CourseController::class, 'create'])->name('create');
    Route::post('/manage', [CourseController::class, 'storeWeb'])->name('store.web');
    Route::get('/manage/{course}/edit', [CourseController::class, 'edit'])->name('edit');
    Route::put('/manage/{course}', [CourseController::class, 'update'])->name('update');
    Route::delete('/manage/{course}', [CourseController::class, 'destroy'])->name('destroy');
    Route::post('/manage/{course}/submit', [CourseController::class, 'submit'])->name('submit');
    Route::post('/manage/{course}/workflow-decision', [CourseController::class, 'decideWorkflow'])->name('workflow.decide');
    Route::get('/manage/{course}/export-slt', [CourseController::class, 'exportSlt'])->name('slt.export');
});
