<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    Laravel\Socialite\SocialiteServiceProvider::class,
    Modules\Group\Providers\GroupServiceProvider::class,
    Modules\Workflow\Providers\WorkflowServiceProvider::class,
];
