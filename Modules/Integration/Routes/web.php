<?php

use Illuminate\Support\Facades\Route;
use Modules\Integration\Http\Controllers\SsoController;

Route::middleware('auth')->prefix('integration/sso')->name('integration.sso.')->group(function (): void {
    Route::post('/validate-token', [SsoController::class, 'validateToken'])->name('validate-token');
});
