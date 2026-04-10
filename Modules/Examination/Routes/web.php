<?php

use Illuminate\Support\Facades\Route;
use Modules\Examination\Http\Controllers\ExaminationController;

Route::middleware('auth')->prefix('examinations')->name('examinations.')->group(function (): void {
    Route::get('/', [ExaminationController::class, 'index'])->name('index');
    Route::post('/', [ExaminationController::class, 'store'])->name('store');
});
