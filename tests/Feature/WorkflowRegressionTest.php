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

class WorkflowRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_examination_workflow_progresses_reviewer_then_approver_without_leaking_stage_access(): void
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

        $this->actingAs($lecturer)
            ->postJson('/examinations', [
                'course_id' => $course->id,
                'group_id' => $group->id,
                'title' => 'Final Examination',
                'exam_date' => '2026-05-10',
                'metadata' => ['venue' => 'Hall A'],
            ])
            ->assertCreated();

        $examination = Examination::query()->with('workflow.approvals')->firstOrFail();
        $workflow = $examination->workflow;

        $this->assertSame('submitted', $examination->status);
        $this->assertSame(1, $workflow->current_stage);
        $this->assertSame('in_review', $workflow->status);

        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $reviewer->id,
            'stage' => 1,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $approver->id,
            'stage' => 2,
            'status' => 'queued',
        ]);

        $this->actingAs($reviewer)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(1);

        $this->actingAs($approver)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(0);

        $this->actingAs($reviewer)
            ->postJson('/workflows/decide', [
                'workflow_id' => $workflow->id,
                'decision' => 'approved',
                'comments' => 'Reviewed and accepted.',
            ])
            ->assertOk();

        $workflow->refresh();
        $examination->refresh();

        $this->assertSame('in_review', $workflow->status);
        $this->assertSame(2, $workflow->current_stage);
        $this->assertSame('in_review', $examination->status);

        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'reviewer_id' => $approver->id,
            'stage' => 2,
            'status' => 'pending',
        ]);

        $this->actingAs($approver)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(1);

        $this->actingAs($approver)
            ->postJson('/workflows/decide', [
                'workflow_id' => $workflow->id,
                'decision' => 'approved',
                'comments' => 'Approved for release.',
            ])
            ->assertOk();

        $workflow->refresh();
        $examination->refresh();

        $this->assertSame('approved', $workflow->status);
        $this->assertNull($workflow->current_stage);
        $this->assertSame('approved', $examination->status);

        Notification::assertSentTo($reviewer, WorkflowStatusNotification::class);
        Notification::assertSentTo($approver, WorkflowStatusNotification::class);
        Notification::assertSentTo($lecturer, WorkflowStatusNotification::class);
    }
}
