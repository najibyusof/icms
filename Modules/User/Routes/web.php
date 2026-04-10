<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth', 'role:Admin|admin'])->prefix('users')->name('users.')->group(function (): void {
    Route::get('/', [UserController::class, 'index'])->name('index');
});
