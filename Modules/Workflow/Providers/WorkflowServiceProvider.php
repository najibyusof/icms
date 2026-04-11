<?php

namespace Modules\Workflow\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Workflow\Services\WorkflowService;

class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'workflow');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/workflow.php' => config_path('workflow.php'),
        ], 'workflow-config');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(WorkflowService::class, function ($app) {
            return new WorkflowService();
        });
    }
}
