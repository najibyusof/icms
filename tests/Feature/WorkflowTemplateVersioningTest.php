<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Database\Seeders\WorkflowDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Course\Services\CourseService;
use Modules\Programme\Models\Programme;
use Modules\Programme\Services\ProgrammeService;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Services\WorkflowService;
use Tests\TestCase;

class WorkflowTemplateVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_starts_course_workflow_by_requested_template_version(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Lecturer');

        $programme = Programme::query()->create([
            'code' => 'WTC1',
            'name' => 'Workflow Template Course Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);

        $course = Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'WTC101',
            'name' => 'Workflow Template Course',
            'credit_hours' => 3,
            'is_active' => true,
            'status' => 'draft',
        ]);

        $service = app(WorkflowService::class);
        $instance = $service->startWorkflowForEntityTypeAndVersion($course, $user, 3);

        $instance->load('workflow');

        $this->assertSame(3, (int) data_get($instance->workflow->config, 'version'));
        $this->assertSame('Course Approval Workflow v3', $instance->workflow->name);
    }

    public function test_service_starts_programme_workflow_by_requested_template_version(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Programme Coordinator');

        $programme = Programme::query()->create([
            'code' => 'WTP1',
            'name' => 'Workflow Template Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);

        $service = app(WorkflowService::class);
        $instance = $service->startWorkflowForEntityTypeAndVersion($programme, $user, 2);

        $instance->load('workflow');

        $this->assertSame(2, (int) data_get($instance->workflow->config, 'version'));
        $this->assertSame('Programme Approval Workflow v2', $instance->workflow->name);
    }

    public function test_course_service_uses_configured_default_template_version(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);
        config(['workflow.templates.default_versions.course' => 3]);

        $user = User::factory()->create();
        $user->assignRole('Lecturer');

        $programme = Programme::query()->create([
            'code' => 'WTC2',
            'name' => 'Workflow Template Course Programme 2',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);

        $course = Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'WTC201',
            'name' => 'Workflow Template Course 2',
            'credit_hours' => 3,
            'is_active' => true,
            'status' => 'draft',
        ]);

        app(CourseService::class)->submitForApproval($course, $user->id);

        $instance = WorkflowInstance::query()
            ->where('entity_type', Course::class)
            ->where('entity_id', $course->id)
            ->latest('id')
            ->firstOrFail();

        $instance->load('workflow');

        $this->assertSame(3, (int) data_get($instance->workflow->config, 'version'));
        $this->assertSame('Course Approval Workflow v3', $instance->workflow->name);
    }

    public function test_programme_service_uses_configured_default_template_version(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);
        config(['workflow.templates.default_versions.programme' => 2]);

        $user = User::factory()->create();
        $user->assignRole('Programme Coordinator');

        $programme = Programme::query()->create([
            'code' => 'WTP2',
            'name' => 'Workflow Template Programme 2',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);

        app(ProgrammeService::class)->submitForApproval($programme, $user);

        $instance = WorkflowInstance::query()
            ->where('entity_type', Programme::class)
            ->where('entity_id', $programme->id)
            ->latest('id')
            ->firstOrFail();

        $instance->load('workflow');

        $this->assertSame(2, (int) data_get($instance->workflow->config, 'version'));
        $this->assertSame('Programme Approval Workflow v2', $instance->workflow->name);
    }
}
