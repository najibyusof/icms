<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function (): void {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
});
