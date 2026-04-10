<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Course\Models\Course;
use Modules\Examination\Models\Examination;
use Modules\Group\Models\AcademicGroup;
use Modules\Notification\Notifications\WorkflowStatusNotification;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;
use Tests\TestCase;

class WorkflowRejectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression: when the stage-1 reviewer rejects, the workflow status must become
     * "rejected", the examination must reflect that, stage-2 must remain "queued"
     * (the approver was never activated), and the approver must NOT be able to record
     * any further decision.
     */
    public function test_reviewer_rejection_halts_workflow_and_blocks_approver(): void
    {
        Notification::fake();
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('reviewer');

        $approver = User::factory()->create();
        $approver->assignRole('approver');

        $programme = Programme::query()->create([
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'CS101',
            'name' => 'Software Engineering',
            'credit_hours' => 3,
            'is_active' => true,
        ]);

        $group = AcademicGroup::query()->create([
            'programme_id' => $programme->id,
            'coordinator_id' => null,
            'name' => 'SE-2026-A',
            'intake_year' => 2026,
            'semester' => 2,
            'is_active' => true,
        ]);

        // Lecturer submits an examination — workflow created in_review at stage 1
        $this->actingAs($lecturer)
            ->postJson('/examinations', [
                'course_id' => $course->id,
                'group_id' => $group->id,
                'title' => 'Supplementary Examination',
                'exam_date' => '2026-06-15',
                'metadata' => ['venue' => 'Hall B'],
            ])
            ->assertCreated();

        $examination = Examination::query()->with('workflow.approvals')->firstOrFail();
        $workflow = $examination->workflow;

        // Sanity: stage 2 is queued before any action
        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $approver->id,
            'stage' => 2,
            'status' => 'queued',
        ]);

        // Reviewer rejects at stage 1
        $this->actingAs($reviewer)
            ->postJson('/workflows/decide', [
                'workflow_id' => $workflow->id,
                'decision' => 'rejected',
                'comments' => 'Insufficient details.',
            ])
            ->assertOk();

        $workflow->refresh();
        $examination->refresh();

        // Workflow and examination must both be rejected
        $this->assertSame('rejected', $workflow->status);
        $this->assertNull($workflow->current_stage);
        $this->assertSame('rejected', $examination->status);

        // Stage 1 approval must be marked rejected
        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $reviewer->id,
            'stage' => 1,
            'status' => 'rejected',
        ]);

        // Stage 2 must remain queued — approver was never activated
        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $approver->id,
            'stage' => 2,
            'status' => 'queued',
        ]);

        // Approver must see zero pending workflows
        $this->actingAs($approver)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(0);

        // Approver attempting to decide on a rejected workflow should fail (no pending approval to find)
        $this->actingAs($approver)
            ->postJson('/workflows/decide', [
                'workflow_id' => $workflow->id,
                'decision' => 'approved',
                'comments' => 'Should not reach this point.',
            ])
            ->assertStatus(404);

        // Submission event notified reviewer + approver (1 each); rejection decision notified only reviewer + lecturer.
        // Approver must have received exactly 1 notification total (the submission), not the rejection decision.
        Notification::assertSentTo($reviewer, WorkflowStatusNotification::class);
        Notification::assertSentTo($lecturer, WorkflowStatusNotification::class);
        Notification::assertSentToTimes($approver, WorkflowStatusNotification::class, 1);
    }

    /**
     * Regression: a reviewer may not decide on a workflow that belongs to another actor.
     * Attempting to piggyback on a different actor's pending stage must return 404.
     */
    public function test_reviewer_cannot_decide_on_another_actors_pending_stage(): void
    {
        Notification::fake();
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('reviewer');

        $approver = User::factory()->create();
        $approver->assignRole('approver');

        // A second reviewer — not the assigned stage-1 actor
        $otherReviewer = User::factory()->create();
        $otherReviewer->assignRole('reviewer');

        $programme = Programme::query()->create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'level' => 'Bachelor',
            'duration_semesters' => 6,
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'IT101',
            'name' => 'Database Systems',
            'credit_hours' => 3,
            'is_active' => true,
        ]);

        $group = AcademicGroup::query()->create([
            'programme_id' => $programme->id,
            'coordinator_id' => null,
            'name' => 'IT-2026-A',
            'intake_year' => 2026,
            'semester' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($lecturer)
            ->postJson('/examinations', [
                'course_id' => $course->id,
                'group_id' => $group->id,
                'title' => 'Mid-Term Examination',
                'exam_date' => '2026-04-20',
                'metadata' => [],
            ])
            ->assertCreated();

        $workflow = WorkflowInstance::query()->firstOrFail();

        // The *other* reviewer was not assigned — their attempt must be rejected
        $this->actingAs($otherReviewer)
            ->postJson('/workflows/decide', [
                'workflow_id' => $workflow->id,
                'decision' => 'approved',
                'comments' => 'Unauthorised attempt.',
            ])
            ->assertStatus(404);

        // Actual reviewer still sees the workflow as pending
        $workflow->refresh();
        $this->assertSame('in_review', $workflow->status);
        $this->assertSame(1, $workflow->current_stage);
    }
}
