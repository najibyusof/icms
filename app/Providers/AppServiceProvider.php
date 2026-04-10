<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Course\Models\Course;
use Modules\Course\Policies\CoursePolicy;
use Modules\User\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
    }
}
