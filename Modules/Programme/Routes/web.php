<?php

use Illuminate\Support\Facades\Route;
use Modules\Programme\Http\Controllers\ProgrammeController;

Route::middleware('auth')->prefix('programmes')->name('programmes.')->group(function (): void {
    Route::get('/', [ProgrammeController::class, 'index'])->name('index');
    Route::post('/', [ProgrammeController::class, 'store'])->name('store');
});
