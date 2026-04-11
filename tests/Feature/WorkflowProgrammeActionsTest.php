<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Services\WorkflowService;
use Tests\TestCase;

class WorkflowProgrammeActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_programme_workflow_can_be_submitted(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $programme = $this->createProgramme('PGS1', 'Programme Submit');
        $workflow = $this->createWorkflowAndStart($programme, 'Programme Test Workflow Submit', $coordinator);

        $this->actingAs($coordinator)
            ->postJson("/workflows/{$workflow->id}/submit", [
                'comment' => 'Submitting programme for review.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $workflow->refresh();

        $this->assertSame('in_progress', $workflow->status);
        $this->assertNotNull($workflow->current_step_id);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'submitted',
            'comment' => 'Submitting programme for review.',
        ]);
    }

    public function test_programme_workflow_can_be_approved(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $programme = $this->createProgramme('PGA1', 'Programme Approve');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/approve", [
                'comment' => 'Programme approved.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $workflow->refresh();
        $programme->refresh();

        $this->assertSame('approved', $workflow->status);
        $this->assertSame('approved', $programme->status);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'approved',
            'comment' => 'Programme approved.',
        ]);
    }

    public function test_programme_workflow_can_be_rejected(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $programme = $this->createProgramme('PGR1', 'Programme Reject');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/reject", [
                'reason' => 'Programme objectives need revision.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $workflow->refresh();
        $programme->refresh();

        $this->assertSame('rejected', $workflow->status);
        $this->assertSame('Programme objectives need revision.', $workflow->rejection_reason);
        $this->assertSame('rejected', $programme->status);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'rejected',
            'comment' => 'Programme objectives need revision.',
        ]);
    }

    public function test_programme_workflow_supports_commenting(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $programme = $this->createProgramme('PGC1', 'Programme Comment');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($coordinator)
            ->postJson("/workflows/{$workflow->id}/comment", [
                'comment' => 'Added programme rationale note.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'commented',
            'comment' => 'Added programme rationale note.',
        ]);
    }

    public function test_non_reviewer_cannot_approve_programme_workflow(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $programme = $this->createProgramme('PGN1', 'Programme Non Reviewer');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($coordinator)
            ->postJson("/workflows/{$workflow->id}/approve", [
                'comment' => 'Trying to approve without reviewer role.',
            ])
            ->assertForbidden();
    }

    public function test_programme_workflow_cannot_be_approved_after_rejection(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $programme = $this->createProgramme('PGR2', 'Programme Reject Then Approve');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/reject", [
                'reason' => 'Reject first to close workflow.',
            ])
            ->assertOk();

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/approve", [
                'comment' => 'Should not be allowed after rejection.',
            ])
            ->assertForbidden();
    }

    public function test_programme_workflow_comment_requires_comment_text(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $programme = $this->createProgramme('PGV1', 'Programme Validation');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($coordinator)
            ->postJson("/workflows/{$workflow->id}/comment", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_programme_workflow_timeline_is_accessible_to_creator(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $programme = $this->createProgramme('PGTL1', 'Programme Timeline Allowed');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($coordinator)
            ->get("/workflows/{$workflow->id}/timeline")
            ->assertOk()
            ->assertSee('Workflow Timeline');
    }

    public function test_programme_workflow_timeline_is_forbidden_for_unrelated_user_without_permission(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $outsider = User::factory()->create();

        $programme = $this->createProgramme('PGTL2', 'Programme Timeline Forbidden');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($outsider)
            ->get("/workflows/{$workflow->id}/timeline")
            ->assertForbidden();
    }

    public function test_programme_pending_endpoint_returns_only_actionable_workflows_for_reviewer(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $approver = User::factory()->create();
        $approver->assignRole('Approver');

        $programme = $this->createProgramme('PGPD1', 'Programme Pending');
        $workflow = $this->createSubmittedProgrammeWorkflow($programme, $coordinator);

        $this->actingAs($reviewer)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $workflow->id);

        $this->actingAs($approver)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_programme_workflow_approval_requires_comment_when_step_requires_comment(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('Programme Coordinator');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $programme = $this->createProgramme('PGRC1', 'Programme Requires Comment');
        $workflow = $this->createSubmittedProgrammeWorkflow(
            $programme,
            $coordinator,
            'Programme Test Workflow Requires Comment',
            true
        );

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/approve", [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Comment is required for this approval step');
    }

    private function createSubmittedProgrammeWorkflow(
        Programme $programme,
        User $creator,
        string $workflowName = 'Programme Test Workflow Flow',
        bool $requiresComment = false
    ): WorkflowInstance
    {
        $workflow = $this->createWorkflowAndStart($programme, $workflowName, $creator, $requiresComment);

        $this->actingAs($creator)
            ->postJson("/workflows/{$workflow->id}/submit", [
                'comment' => 'Initial submit.',
            ])
            ->assertOk();

        return $workflow->fresh();
    }

    private function createWorkflowAndStart(
        Programme $programme,
        string $name,
        User $creator,
        bool $requiresComment = false
    ): WorkflowInstance
    {
        $service = app(WorkflowService::class);

        $service->createWorkflow([
            'name' => $name,
            'description' => 'Test workflow for programme.',
            'entity_type' => 'programme',
            'is_active' => true,
            'steps' => [
                [
                    'title' => 'Reviewer Decision',
                    'roles_required' => ['Reviewer'],
                    'approval_level' => 1,
                    'allow_rejection' => true,
                    'requires_comment' => $requiresComment,
                ],
            ],
        ]);

        return $service->startWorkflow($programme, $name, $creator);
    }

    private function createProgramme(string $code, string $name): Programme
    {
        return Programme::query()->create([
            'code' => $code,
            'name' => $name,
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);
    }
}
