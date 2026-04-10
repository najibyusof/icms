<?php

use Illuminate\Support\Facades\Route;
use Modules\Group\Http\Controllers\GroupController;

Route::middleware('auth')->prefix('groups')->name('groups.')->group(function (): void {
    Route::get('/', [GroupController::class, 'index'])->name('index');
    Route::post('/', [GroupController::class, 'store'])->name('store');
});
