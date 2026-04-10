<?php

use Illuminate\Support\Facades\Route;
use Modules\Course\Http\Controllers\CourseController;

Route::middleware('auth')->prefix('courses')->name('courses.')->group(function (): void {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::post('/', [CourseController::class, 'store'])->name('store');
});
