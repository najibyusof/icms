<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Modules\Course\Models\Course;
use Modules\Course\Policies\CoursePolicy;
use Modules\Examination\Models\Examination;
use Modules\Examination\Policies\ExaminationPolicy;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Policies\WorkflowInstancePolicy;
use Modules\Group\Models\AcademicGroup;
use Modules\Group\Policies\GroupPolicy;

class ModuleServiceProvider extends AuthServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        Examination::class => ExaminationPolicy::class,
        WorkflowInstance::class => WorkflowInstancePolicy::class,
        AcademicGroup::class => GroupPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
