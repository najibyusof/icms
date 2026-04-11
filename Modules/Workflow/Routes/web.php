<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflow\Http\Controllers\WorkflowController;

Route::middleware('auth')->group(function (): void {
    Route::prefix('workflows/manage')->name('workflows.manage.')->group(function (): void {
        Route::get('/definitions', [WorkflowController::class, 'manageDefinitions'])->name('definitions');
        Route::post('/definitions', [WorkflowController::class, 'storeDefinitionWeb'])->name('definitions.store');
        Route::post('/settings', [WorkflowController::class, 'saveSettings'])->name('settings.save');
    });

    Route::prefix('workflows')->name('workflows.')->group(function (): void {
        // Workflow definition and bootstrap
        Route::get('/definitions', [WorkflowController::class, 'definitions'])->name('definitions');
        Route::post('/definitions', [WorkflowController::class, 'createDefinition'])->name('definitions.store');
        Route::post('/start', [WorkflowController::class, 'start'])->name('start');

        // Workflow timeline and view
        Route::get('/{workflow}/timeline', [WorkflowController::class, 'timeline'])->name('timeline');

        // Workflow actions
        Route::post('/{workflow}/submit', [WorkflowController::class, 'submit'])->name('submit');
        Route::post('/{workflow}/approve', [WorkflowController::class, 'approve'])->name('approve');
        Route::post('/{workflow}/reject', [WorkflowController::class, 'reject'])->name('reject');
        Route::post('/{workflow}/clarification', [WorkflowController::class, 'clarification'])->name('clarification');
        Route::post('/{workflow}/comment', [WorkflowController::class, 'comment'])->name('comment');
        Route::post('/{workflow}/withdraw', [WorkflowController::class, 'withdraw'])->name('withdraw');

        // API endpoints
        Route::get('/pending', [WorkflowController::class, 'pending'])->name('pending');
        Route::get('/{workflow}/timeline-data', [WorkflowController::class, 'getTimeline'])->name('timeline-data');
        Route::get('/{entityType}/{entityId}/workflows', [WorkflowController::class, 'entityWorkflows'])->name('entity');
    });
});
