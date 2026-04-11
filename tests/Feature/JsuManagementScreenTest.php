<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseClo;
use Modules\Jsu\Models\Jsu;
use Modules\Programme\Models\Programme;
use Tests\TestCase;

class JsuManagementScreenTest extends TestCase
{
    use RefreshDatabase;

    private function seedRbac(): void
    {
        $this->seed(RbacSeeder::class);
    }

    private function makeProgramme(): Programme
    {
        return Programme::query()->create([
            'code' => 'JMS-P',
            'name' => 'JSU Management Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
            'status' => 'draft',
        ]);
    }

    private function makeCourse(Programme $programme): Course
    {
        return Course::query()->create([
            'programme_id' => $programme->id,
            'code' => 'JMS101',
            'name' => 'JSU Management Course',
            'credit_hours' => 3,
            'is_active' => true,
            'status' => 'draft',
        ]);
    }

    public function test_lecturer_can_view_jsu_manage_index_and_nav_link(): void
    {
        $this->seedRbac();

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $dashboard = $this->actingAs($lecturer)->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('JSU');

        $response = $this->actingAs($lecturer)->get(route('jsu.manage.index'));

        $response->assertOk();
        $response->assertSee('JSU Management');
    }

    public function test_lecturer_can_create_jsu_from_manage_form(): void
    {
        $this->seedRbac();

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $programme = $this->makeProgramme();
        $course = $this->makeCourse($programme);

        $response = $this->actingAs($lecturer)->post(route('jsu.manage.store'), [
            'course_id' => $course->id,
            'academic_session' => '2025/2026-1',
            'exam_type' => 'midterm',
            'title' => 'JSU Form Create',
            'total_marks' => 100,
        ]);

        $jsu = Jsu::query()->first();

        $response->assertRedirect(route('jsu.manage.show', $jsu));

        $this->assertDatabaseHas('jsu', [
            'title' => 'JSU Form Create',
            'status' => 'draft',
        ]);
    }

    public function test_lecturer_can_add_blueprint_from_manage_show_page(): void
    {
        $this->seedRbac();

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $programme = $this->makeProgramme();
        $course = $this->makeCourse($programme);

        $clo = CourseClo::query()->create([
            'course_id' => $course->id,
            'clo_no' => 1,
            'statement' => 'Demonstrate concept understanding',
            'bloom_level' => 2,
        ]);

        $jsu = Jsu::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'academic_session' => '2025/2026-1',
            'exam_type' => 'final',
            'title' => 'JSU for Blueprint',
            'total_marks' => 100,
            'status' => 'draft',
        ]);

        $show = $this->actingAs($lecturer)->get(route('jsu.manage.show', $jsu));
        $show->assertOk();
        $show->assertSee('Blueprint Entries');

        $response = $this->actingAs($lecturer)->post(route('jsu.manage.blueprints.store', $jsu), [
            'question_no' => 1,
            'clo_id' => $clo->id,
            'bloom_level' => 3,
            'marks' => 20,
        ]);

        $response->assertRedirect(route('jsu.manage.show', $jsu));

        $this->assertDatabaseHas('jsu_blueprints', [
            'jsu_id' => $jsu->id,
            'question_no' => 1,
            'bloom_level' => 3,
        ]);
    }

    public function test_user_without_jsu_view_permission_cannot_open_manage_index(): void
    {
        $this->seedRbac();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('jsu.manage.index'));

        $response->assertForbidden();
    }
}
