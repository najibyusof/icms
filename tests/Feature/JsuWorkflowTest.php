<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Database\Seeders\WorkflowDefinitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseClo;
use Modules\Jsu\Models\Jsu;
use Modules\Jsu\Models\JsuBlueprint;
use Modules\Jsu\Services\JsuService;
use Modules\Programme\Models\Programme;
use Modules\Workflow\Models\WorkflowInstance;
use Tests\TestCase;

class JsuWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function seedAll(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowDefinitionSeeder::class);
    }

    private function makeProgramme(): Programme
    {
        return Programme::query()->create([
            'code'               => 'JSU-PROG',
            'name'               => 'JSU Test Programme',
            'level'              => 'Bachelor',
            'duration_semesters' => 8,
            'is_active'          => true,
            'status'             => 'draft',
        ]);
    }

    private function makeCourse(Programme $programme): Course
    {
        return Course::query()->create([
            'programme_id' => $programme->id,
            'code'         => 'JSU101',
            'name'         => 'JSU Test Course',
            'credit_hours' => 3,
            'is_active'    => true,
            'status'       => 'draft',
        ]);
    }

    private function makeClo(Course $course, int $no = 1): CourseClo
    {
        return CourseClo::query()->create([
            'course_id'   => $course->id,
            'clo_no'      => $no,
            'statement'   => "CLO {$no}: Demonstrate understanding.",
            'bloom_level' => 2,
        ]);
    }

    private function makeJsu(Course $course, User $user): Jsu
    {
        return Jsu::query()->create([
            'course_id'        => $course->id,
            'created_by'       => $user->id,
            'academic_session' => '2024/2025-1',
            'exam_type'        => 'midterm',
            'title'            => 'Midterm Examination JSU',
            'total_marks'      => 100,
            'status'           => 'draft',
        ]);
    }

    private function addBlueprint(Jsu $jsu, CourseClo $clo, int $questionNo, int $bloomLevel, float $marks): JsuBlueprint
    {
        return JsuBlueprint::query()->create([
            'jsu_id'      => $jsu->id,
            'clo_id'      => $clo->id,
            'question_no' => $questionNo,
            'bloom_level' => $bloomLevel,
            'marks'       => $marks,
        ]);
    }

    private function makeLecturer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Lecturer');

        return $user;
    }

    private function makeReviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Reviewer');

        return $user;
    }

    private function makeApprover(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Approver');

        return $user;
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_lecturer_can_create_jsu_via_api(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);

        $response = $this->actingAs($lecturer)->postJson('/jsu', [
            'course_id'        => $course->id,
            'academic_session' => '2024/2025-1',
            'exam_type'        => 'midterm',
            'title'            => 'Test JSU',
            'total_marks'      => 100,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('exam_type', 'midterm');
    }

    public function test_lecturer_cannot_create_jsu_with_invalid_exam_type(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);

        $response = $this->actingAs($lecturer)->postJson('/jsu', [
            'course_id'        => $course->id,
            'academic_session' => '2024/2025-1',
            'exam_type'        => 'invalid_type',
            'title'            => 'Bad JSU',
            'total_marks'      => 100,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('exam_type');
    }

    // ── Blueprint management ──────────────────────────────────────────────────

    public function test_lecturer_can_add_blueprint_entry(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);

        $response = $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/blueprints", [
            'question_no' => 1,
            'clo_id'      => $clo->id,
            'bloom_level' => 3,
            'marks'       => 10.0,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('question_no', 1)
            ->assertJsonPath('bloom_level', 3);
    }

    public function test_blueprint_upsert_replaces_existing_question(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/blueprints", [
            'question_no' => 1, 'clo_id' => $clo->id, 'bloom_level' => 2, 'marks' => 5.0,
        ]);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/blueprints", [
            'question_no' => 1, 'clo_id' => $clo->id, 'bloom_level' => 4, 'marks' => 10.0,
        ]);

        $this->assertSame(1, $jsu->blueprints()->count());
        $this->assertSame(4, $jsu->blueprints()->first()->bloom_level);
    }

    // ── Difficulty distribution ───────────────────────────────────────────────

    public function test_distribution_endpoint_returns_per_group_data(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);

        // 30 marks LOTS (bloom 1), 50 marks MOTS (bloom 3), 20 marks HOTS (bloom 5)
        $this->addBlueprint($jsu, $clo, 1, 1, 30.0);
        $this->addBlueprint($jsu, $clo, 2, 3, 50.0);
        $this->addBlueprint($jsu, $clo, 3, 5, 20.0);

        $response = $this->actingAs($lecturer)->getJson("/jsu/{$jsu->id}/distribution");

        $response->assertOk()
            ->assertJsonPath('is_balanced', true)
            ->assertJsonStructure([
                'distribution' => ['lower', 'middle', 'higher'],
                'is_balanced',
                'tolerance_pct',
            ]);
    }

    public function test_distribution_flags_imbalanced_jsu(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);

        // All marks on LOTS — imbalanced
        $this->addBlueprint($jsu, $clo, 1, 1, 100.0);

        $response = $this->actingAs($lecturer)->getJson("/jsu/{$jsu->id}/distribution");

        $response->assertOk()
            ->assertJsonPath('is_balanced', false);
    }

    // ── Submit for approval ───────────────────────────────────────────────────

    public function test_lecturer_can_submit_jsu_for_approval(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);
        $this->addBlueprint($jsu, $clo, 1, 2, 50.0);
        $this->addBlueprint($jsu, $clo, 2, 3, 50.0);

        $response = $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");

        $response->assertOk()->assertJsonStructure(['message', 'workflow']);

        $this->assertSame('submitted', $jsu->fresh()->status);

        $this->assertDatabaseHas('workflow_instances', [
            'entity_type' => Jsu::class,
            'entity_id'   => $jsu->id,
            'status'      => 'in_progress',
        ]);
    }

    public function test_submit_fails_without_blueprint(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $jsu       = $this->makeJsu($course, $lecturer);

        $response = $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");

        $response->assertStatus(422)->assertJsonValidationErrors('blueprints');
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    public function test_reviewer_can_approve_first_step(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $reviewer  = $this->makeReviewer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);
        $this->addBlueprint($jsu, $clo, 1, 2, 100.0);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");

        $response = $this->actingAs($reviewer)->postJson("/jsu/{$jsu->id}/approve");

        $response->assertOk()->assertJsonStructure(['message', 'workflow']);
        // Still in_progress — approver step remains
        $this->assertSame('in_progress', WorkflowInstance::query()
            ->where('entity_type', Jsu::class)->where('entity_id', $jsu->id)->first()->status);
    }

    public function test_full_approval_cycle_sets_jsu_approved(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $reviewer  = $this->makeReviewer();
        $approver  = $this->makeApprover();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);
        $this->addBlueprint($jsu, $clo, 1, 2, 100.0);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");
        $this->actingAs($reviewer)->postJson("/jsu/{$jsu->id}/approve");
        $this->actingAs($approver)->postJson("/jsu/{$jsu->id}/approve");

        $this->assertSame('approved', $jsu->fresh()->status);
        $this->assertNotNull($jsu->fresh()->approved_by);
    }

    // ── Reject ────────────────────────────────────────────────────────────────

    public function test_reviewer_can_reject_jsu(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $reviewer  = $this->makeReviewer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);
        $this->addBlueprint($jsu, $clo, 1, 2, 100.0);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");

        $response = $this->actingAs($reviewer)->postJson("/jsu/{$jsu->id}/reject", [
            'reason' => 'Bloom distribution incomplete.',
        ]);

        $response->assertOk();
        $this->assertSame('rejected', $jsu->fresh()->status);
    }

    // ── Activate ──────────────────────────────────────────────────────────────

    public function test_approver_can_activate_approved_jsu(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $reviewer  = $this->makeReviewer();
        $approver  = $this->makeApprover();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);
        $this->addBlueprint($jsu, $clo, 1, 2, 100.0);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");
        $this->actingAs($reviewer)->postJson("/jsu/{$jsu->id}/approve");
        $this->actingAs($approver)->postJson("/jsu/{$jsu->id}/approve");

        $response = $this->actingAs($approver)->postJson("/jsu/{$jsu->id}/activate");

        $response->assertOk()->assertJsonPath('jsu.status', 'active');
        $this->assertSame('active', $jsu->fresh()->status);
    }

    public function test_activate_fails_when_not_approved(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $approver  = $this->makeApprover();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $jsu       = $this->makeJsu($course, $lecturer);

        $response = $this->actingAs($approver)->postJson("/jsu/{$jsu->id}/activate");

        $response->assertStatus(403);
    }

    // ── Logs ─────────────────────────────────────────────────────────────────

    public function test_jsu_logs_are_recorded(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);
        $jsu       = $this->makeJsu($course, $lecturer);
        $this->addBlueprint($jsu, $clo, 1, 2, 50.0);

        $this->actingAs($lecturer)->postJson("/jsu/{$jsu->id}/submit");

        $response = $this->actingAs($lecturer)->getJson("/jsu/{$jsu->id}/logs");

        $response->assertOk();

        $actions = collect($response->json())->pluck('action');
        $this->assertContains('submitted', $actions->toArray());
    }

    // ── Service unit: custom difficulty config ────────────────────────────────

    public function test_per_jsu_difficulty_config_overrides_global(): void
    {
        $this->seedAll();
        $lecturer  = $this->makeLecturer();
        $programme = $this->makeProgramme();
        $course    = $this->makeCourse($programme);
        $clo       = $this->makeClo($course);

        $jsu = Jsu::query()->create([
            'course_id'        => $course->id,
            'created_by'       => $lecturer->id,
            'academic_session' => '2024/2025-1',
            'exam_type'        => 'final',
            'title'            => 'Custom Distribution JSU',
            'total_marks'      => 100,
            'status'           => 'draft',
            'difficulty_config' => [
                'lower'  => ['bloom_levels' => [1, 2], 'target_pct' => 20],
                'middle' => ['bloom_levels' => [3, 4], 'target_pct' => 40],
                'higher' => ['bloom_levels' => [5, 6], 'target_pct' => 40],
            ],
        ]);

        // 20 LOTS, 40 MOTS, 40 HOTS → balanced for custom config
        $this->addBlueprint($jsu, $clo, 1, 1, 20.0);
        $this->addBlueprint($jsu, $clo, 2, 3, 40.0);
        $this->addBlueprint($jsu, $clo, 3, 5, 40.0);

        $service      = app(JsuService::class);
        $distribution = $service->checkDifficultyDistribution($jsu);

        $this->assertTrue($distribution['lower']['within_tolerance']);
        $this->assertTrue($distribution['middle']['within_tolerance']);
        $this->assertTrue($distribution['higher']['within_tolerance']);
    }
}
