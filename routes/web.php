<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Module management routes are loaded from routes/modules.php via bootstrap/app.php.
Route::get('/', DashboardController::class)->name('dashboard');
