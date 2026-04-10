<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflow\Http\Controllers\WorkflowController;

Route::middleware('auth')->prefix('workflows')->name('workflows.')->group(function (): void {
    Route::get('/pending', [WorkflowController::class, 'pending'])->name('pending');
    Route::post('/decide', [WorkflowController::class, 'decide'])->name('decide');
});
