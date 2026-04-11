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
use Modules\Workflow\Models\WorkflowSetting;use Tests\TestCase;

class WorkflowSettingsTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ helpers

    private function makeAdmin(): User
    {
        $this->seed(RbacSeeder::class);
        $admin = User::factory()->create(['email' => 'admin.settings@test.com']);
        $admin->assignRole('Admin');

        return $admin;
    }

    private function makeNonAdmin(): User
    {
        $user = User::factory()->create(['email' => 'lecturer.settings@test.com']);
        $user->assignRole('Lecturer');

        return $user;
    }

    // ------------------------------------------------------------------ screen tests

    public function test_admin_can_view_settings_panel_on_management_screen(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('workflows.manage.definitions'));

        $response->assertOk();
        $response->assertSee('Default Template Versions');
        $response->assertSee('course_default_version');
        $response->assertSee('programme_default_version');
        $response->assertSee('Save Settings');
    }

    // ------------------------------------------------------------------ save / read

    public function test_admin_can_save_default_version_settings(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('workflows.manage.settings.save'), [
            'course_default_version'    => 2,
            'programme_default_version' => 3,
        ]);

        $response->assertRedirect(route('workflows.manage.definitions'));
        $response->assertSessionHas('success');

        $this->assertSame('2', WorkflowSetting::get('default_version.course'));
        $this->assertSame('3', WorkflowSetting::get('default_version.programme'));
    }

    public function test_non_admin_cannot_save_settings(): void
    {
        $this->seed(RbacSeeder::class);
        $user = $this->makeNonAdmin();

        $response = $this->actingAs($user)->post(route('workflows.manage.settings.save'), [
            'course_default_version'    => 2,
            'programme_default_version' => 3,
        ]);

        $response->assertForbidden();
        $this->assertNull(WorkflowSetting::get('default_version.course'));
    }

    public function test_save_settings_validates_numeric_bounds(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('workflows.manage.settings.save'), [
            'course_default_version'    => 0,   // below min:1
            'programme_default_version' => 99,  // above max:10
        ]);

        $response->assertSessionHasErrors(['course_default_version', 'programme_default_version']);
    }

    // ------------------------------------------------------------------ service integration

    public function test_course_service_uses_db_setting_over_config_default(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);

        // Persist DB setting that overrides config
        WorkflowSetting::set('default_version.course', '3');

        // Config stays at 1
        config(['workflow.templates.default_versions.course' => 1]);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $programme = Programme::query()->create([
            'code'               => 'WS1',
            'name'               => 'WS Programme',
            'level'              => 'Bachelor',
            'duration_semesters' => 8,
            'is_active'          => true,
            'status'             => 'draft',
        ]);

        $course = Course::query()->create([
            'programme_id' => $programme->id,
            'code'         => 'WS101',
            'name'         => 'WS Course',
            'credit_hours' => 3,
            'is_active'    => true,
            'status'       => 'draft',
        ]);

        $service = app(CourseService::class);
        $service->submitForApproval($course, $lecturer->id);

        $instance = WorkflowInstance::query()
            ->where('entity_type', Course::class)
            ->where('entity_id', $course->id)
            ->latest('id')
            ->firstOrFail();

        $instance->load('workflow');

        // Expect the v3 template (4 steps) was selected via DB setting
        $this->assertSame(3, (int) data_get($instance->workflow->config, 'version'));
    }

    public function test_programme_service_uses_db_setting_over_config_default(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);

        WorkflowSetting::set('default_version.programme', '2');

        config(['workflow.templates.default_versions.programme' => 1]);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $programme = Programme::query()->create([
            'code'               => 'WS2',
            'name'               => 'WS Programme 2',
            'level'              => 'Bachelor',
            'duration_semesters' => 8,
            'is_active'          => true,
            'status'             => 'draft',
        ]);

        $service = app(ProgrammeService::class);
        $service->submitForApproval($programme, $coordinator);

        $instance = WorkflowInstance::query()
            ->where('entity_type', Programme::class)
            ->where('entity_id', $programme->id)
            ->latest('id')
            ->firstOrFail();

        $instance->load('workflow');

        $this->assertSame(2, (int) data_get($instance->workflow->config, 'version'));
    }

    // ------------------------------------------------------------------ upsert behaviour

    public function test_saving_settings_twice_updates_existing_row(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('workflows.manage.settings.save'), [
            'course_default_version'    => 2,
            'programme_default_version' => 2,
        ]);

        $this->actingAs($admin)->post(route('workflows.manage.settings.save'), [
            'course_default_version'    => 3,
            'programme_default_version' => 3,
        ]);

        // DB must have exactly one row per key (upsert, not append)
        $this->assertSame(1, WorkflowSetting::query()->where('key', 'default_version.course')->count());
        $this->assertSame('3', WorkflowSetting::get('default_version.course'));
        $this->assertSame('3', WorkflowSetting::get('default_version.programme'));
    }
}
