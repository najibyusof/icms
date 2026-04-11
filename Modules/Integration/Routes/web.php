<?php

use Illuminate\Support\Facades\Route;
use Modules\Integration\Http\Controllers\SsoController;

Route::prefix('integration/sso')->name('integration.sso.')->group(function (): void {
    Route::get('/redirect', [SsoController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [SsoController::class, 'callback'])->name('callback');
    Route::get('/me', [SsoController::class, 'me'])->middleware('sso.auth')->name('me');

    Route::middleware('auth')->group(function (): void {
        Route::get('/manage/settings', [SsoController::class, 'settings'])->name('settings');
        Route::post('/manage/settings', [SsoController::class, 'saveSettings'])->name('settings.save');
        Route::post('/validate-token', [SsoController::class, 'validateToken'])->name('validate-token');
    });
});
