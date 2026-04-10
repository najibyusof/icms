<?php

namespace Modules\Group\Providers;

use Illuminate\Support\ServiceProvider;

class GroupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load views from the module
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'group');
    }
}
