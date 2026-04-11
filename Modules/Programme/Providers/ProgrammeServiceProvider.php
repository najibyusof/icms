<?php

namespace Modules\Programme\Providers;

use Illuminate\Support\ServiceProvider;

class ProgrammeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'programme');
    }
}
