<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Services\WorkflowService;
use Tests\TestCase;

class WorkflowPendingFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_endpoint_filters_by_mixed_entity_type_query(): void
    {
        $this->seed(RbacSeeder::class);

        $creator = User::factory()->create();
        $creator->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $course = $this->createCourse();
        $programme = $this->createProgramme();

        $courseWorkflow = $this->createAndSubmitWorkflow(
            $course,
            $creator,
            'Course Pending Filter Workflow',
            'course'
        );

        $programmeWorkflow = $this->createAndSubmitWorkflow(
            $programme,
            $creator,
            'Programme Pending Filter Workflow',
            'programme'
        );

        $this->actingAs($reviewer)
            ->getJson('/workflows/pending')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($reviewer)
            ->getJson('/workflows/pending?entity_type=course')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $courseWorkflow->id);

        $this->actingAs($reviewer)
            ->getJson('/workflows/pending?entity_type=programme')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $programmeWorkflow->id);
    }

    private function createAndSubmitWorkflow(
        Course|Programme $entity,
        User $creator,
        string $workflowName,
        string $entityType
    ) {
        $service = app(WorkflowService::class);

        $service->createWorkflow([
            'name' => $workflowName,
            'description' => 'Pending filter test workflow.',
            'entity_type' => $entityType,
            'is_active' => true,
            'steps' => [
                [
                    'title' => 'Reviewer Decision',
                    'roles_required' => ['Reviewer'],
                    'approval_level' => 1,
                    'allow_rejection' => true,
                    'requires_comment' => false,
                ],
            ],
        ]);

        $instance = $service->startWorkflow($entity, $workflowName, $creator);
        $service->submit($instance, $creator, 'Submit for pending filtering test.');

        return $instance->fresh();
    }

    private function createProgramme(): Programme
    {
        return Programme::query()->create([
            'code' => 'PFP1',
            'name' => 'Pending Filter Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);
    }

    private function createCourse(): Course
    {
        $programme = Programme::query()->create([
            'code' => 'PFC1',
            'name' => 'Pending Filter Course Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);

        return Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'PFC101',
            'name' => 'Pending Filter Course',
            'credit_hours' => 3,
            'is_active' => true,
            'status' => 'draft',
        ]);
    }
}
