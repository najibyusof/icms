<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth', 'role:Admin|admin'])->prefix('users')->name('users.')->group(function (): void {
    Route::get('/',              [UserController::class, 'index'])->name('index');
    Route::post('/',             [UserController::class, 'store'])->name('store');
    Route::get('/{user}/edit',   [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}',        [UserController::class, 'update'])->name('update');
    Route::delete('/{user}',     [UserController::class, 'destroy'])->name('destroy');
});
