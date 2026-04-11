<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Services\WorkflowService;
use Tests\TestCase;

class WorkflowCourseActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_workflow_can_be_submitted(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $course = $this->createCourse();
        $workflow = $this->createWorkflowAndStart($course, 'Course Test Workflow Submit', $lecturer);

        $this->actingAs($lecturer)
            ->postJson("/workflows/{$workflow->id}/submit", [
                'comment' => 'Submitting course for review.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $workflow->refresh();

        $this->assertSame('in_progress', $workflow->status);
        $this->assertNotNull($workflow->current_step_id);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'submitted',
            'comment' => 'Submitting course for review.',
        ]);
    }

    public function test_course_workflow_can_be_approved(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/approve", [
                'comment' => 'Approved for delivery.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $workflow->refresh();
        $course->refresh();

        $this->assertSame('approved', $workflow->status);
        $this->assertNull($workflow->current_step_id);
        $this->assertSame('approved', $course->status);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'approved',
            'comment' => 'Approved for delivery.',
        ]);
    }

    public function test_course_workflow_can_be_rejected(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/reject", [
                'reason' => 'Learning outcomes are incomplete.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $workflow->refresh();
        $course->refresh();

        $this->assertSame('rejected', $workflow->status);
        $this->assertSame('Learning outcomes are incomplete.', $workflow->rejection_reason);
        $this->assertSame('rejected', $course->status);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'rejected',
            'comment' => 'Learning outcomes are incomplete.',
        ]);
    }

    public function test_course_workflow_supports_commenting(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($lecturer)
            ->postJson("/workflows/{$workflow->id}/comment", [
                'comment' => 'Added course mapping clarification.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $workflow->id,
            'action' => 'commented',
            'comment' => 'Added course mapping clarification.',
        ]);
    }

    public function test_non_reviewer_cannot_approve_course_workflow(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($lecturer)
            ->postJson("/workflows/{$workflow->id}/approve", [
                'comment' => 'Trying to approve without reviewer role.',
            ])
            ->assertForbidden();
    }

    public function test_course_workflow_cannot_be_approved_after_rejection(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

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

    public function test_course_workflow_comment_requires_comment_text(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($lecturer)
            ->postJson("/workflows/{$workflow->id}/comment", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_course_workflow_timeline_is_accessible_to_creator(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($lecturer)
            ->get("/workflows/{$workflow->id}/timeline")
            ->assertOk()
            ->assertSee('Workflow Timeline');
    }

    public function test_course_workflow_timeline_is_forbidden_for_unrelated_user_without_permission(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $outsider = User::factory()->create();

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

        $this->actingAs($outsider)
            ->get("/workflows/{$workflow->id}/timeline")
            ->assertForbidden();
    }

    public function test_course_pending_endpoint_returns_only_actionable_workflows_for_reviewer(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $approver = User::factory()->create();
        $approver->assignRole('Approver');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow($course, $lecturer);

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

    public function test_course_workflow_approval_requires_comment_when_step_requires_comment(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $course = $this->createCourse();
        $workflow = $this->createSubmittedCourseWorkflow(
            $course,
            $lecturer,
            'Course Test Workflow Requires Comment',
            true
        );

        $this->actingAs($reviewer)
            ->postJson("/workflows/{$workflow->id}/approve", [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Comment is required for this approval step');
    }

    private function createSubmittedCourseWorkflow(
        Course $course,
        User $creator,
        string $workflowName = 'Course Test Workflow Flow',
        bool $requiresComment = false
    ): WorkflowInstance
    {
        $workflow = $this->createWorkflowAndStart($course, $workflowName, $creator, $requiresComment);

        $this->actingAs($creator)
            ->postJson("/workflows/{$workflow->id}/submit", [
                'comment' => 'Initial submit.',
            ])
            ->assertOk();

        return $workflow->fresh();
    }

    private function createWorkflowAndStart(
        Course $course,
        string $name,
        User $creator,
        bool $requiresComment = false
    ): WorkflowInstance
    {
        $service = app(WorkflowService::class);

        $service->createWorkflow([
            'name' => $name,
            'description' => 'Test workflow for course.',
            'entity_type' => 'course',
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

        return $service->startWorkflow($course, $name, $creator);
    }

    private function createCourse(): Course
    {
        $programme = Programme::query()->create([
            'code' => 'CST',
            'name' => 'Course Workflow Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        return Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'CST101',
            'name' => 'Course Workflow Testing',
            'credit_hours' => 3,
            'is_active' => true,
            'status' => 'draft',
        ]);
    }
}
