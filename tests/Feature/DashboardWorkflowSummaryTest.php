<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;
use Tests\TestCase;

class DashboardWorkflowSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_canonical_workflow_role_labels(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();
        $reviewer = User::query()->firstOrCreate(
            ['email' => 'reviewer.test@academic.local'],
            ['name' => 'Reviewer Test', 'password' => 'password']
        );

        $workflow = WorkflowInstance::query()->create([
            'workflowable_type' => 'demo.workflow',
            'workflowable_id' => 1,
            'initiated_by' => $admin->id,
            'status' => 'in_review',
            'current_stage' => 1,
        ]);

        WorkflowApproval::query()->create([
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $reviewer->id,
            'role_name' => 'coordinator',
            'stage' => 1,
            'status' => 'pending',
        ]);

        WorkflowApproval::query()->create([
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $reviewer->id,
            'role_name' => 'programme coordinator',
            'stage' => 2,
            'status' => 'pending',
        ]);

        Cache::flush();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Programme Coordinator')
            ->assertDontSee('coordinator')
            ->assertSee('Pending')
            ->assertSee('2');
    }
}