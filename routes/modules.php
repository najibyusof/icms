<?php

use Illuminate\Support\Facades\Route;

$moduleRoutes = glob(base_path('Modules/*/Routes/web.php')) ?: [];

foreach ($moduleRoutes as $moduleRoute) {
    Route::middleware('web')->group($moduleRoute);
}
