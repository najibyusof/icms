<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowDefinitionManagementScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_workflow_definition_management_screen(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get('/workflows/manage/definitions')
            ->assertOk()
            ->assertSee('Workflow Definitions');
    }

    public function test_non_admin_cannot_view_workflow_definition_management_screen(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $this->actingAs($lecturer)
            ->get('/workflows/manage/definitions')
            ->assertForbidden();
    }

    public function test_admin_can_create_workflow_definition_from_management_screen(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->post('/workflows/manage/definitions', [
                'name' => 'Admin Managed Workflow',
                'description' => 'Created from Blade management screen.',
                'entity_type' => 'course',
                'steps' => [
                    [
                        'title' => 'Reviewer Step',
                        'description' => 'Review stage',
                        'roles_required' => ['Reviewer'],
                        'approval_level' => 1,
                        'action_type' => 'review',
                        'allow_rejection' => 1,
                        'requires_comment' => 0,
                    ],
                ],
            ])
            ->assertRedirect('/workflows/manage/definitions');

        $this->assertDatabaseHas('workflows', [
            'name' => 'Admin Managed Workflow',
            'entity_type' => 'course',
        ]);

        $this->assertDatabaseHas('workflow_steps', [
            'title' => 'Reviewer Step',
            'approval_level' => 1,
        ]);
    }

    public function test_admin_sees_workflow_setup_nav_link_on_dashboard(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Workflow')
            ->assertSee('Setup')
            ->assertSee('/workflows/manage/definitions');
    }

    public function test_non_admin_does_not_see_workflow_setup_nav_link_on_dashboard(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $this->actingAs($lecturer)
            ->get('/')
            ->assertOk()
            ->assertDontSee('workflows/manage/definitions')
            ->assertDontSee('/workflows/manage/definitions');
    }
}
