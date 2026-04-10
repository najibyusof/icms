<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Programme\Models\Programme;
use Tests\TestCase;

class CrudRegressionTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Programme CRUD
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_programme(): void
    {
        $this->postJson('/programmes', [
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
        ])->assertUnauthorized();
    }

    public function test_lecturer_cannot_create_programme(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $this->actingAs($lecturer)
            ->postJson('/programmes', [
                'code' => 'CS',
                'name' => 'Computer Science',
                'level' => 'Bachelor',
                'duration_semesters' => 8,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_programme(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/programmes', [
                'code' => 'CS',
                'name' => 'Computer Science',
                'level' => 'Bachelor',
                'duration_semesters' => 8,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonFragment(['code' => 'CS', 'name' => 'Computer Science']);
    }

    public function test_create_programme_validates_required_fields(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/programmes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'level', 'duration_semesters']);
    }

    public function test_create_programme_rejects_duplicate_code(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        Programme::query()->create([
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->postJson('/programmes', [
                'code' => 'CS',
                'name' => 'Computer Science Duplicate',
                'level' => 'Bachelor',
                'duration_semesters' => 8,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_admin_can_list_programmes(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        Programme::query()->create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'level' => 'Diploma',
            'duration_semesters' => 6,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->getJson('/programmes')
            ->assertOk()
            ->assertJsonCount(1);
    }

    // -------------------------------------------------------------------------
    // Course CRUD
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_course(): void
    {
        $this->postJson('/courses', [
            'programme_id' => 1,
            'code' => 'CS101',
            'name' => 'Introduction to Computing',
            'credit_hours' => 3,
        ])->assertUnauthorized();
    }

    public function test_lecturer_cannot_create_course(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $programme = Programme::query()->create([
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($lecturer)
            ->postJson('/courses', [
                'programme_id' => $programme->id,
                'code' => 'CS101',
                'name' => 'Introduction to Computing',
                'credit_hours' => 3,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_course(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $programme = Programme::query()->create([
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->postJson('/courses', [
                'programme_id' => $programme->id,
                'code' => 'CS101',
                'name' => 'Introduction to Computing',
                'credit_hours' => 3,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonFragment(['code' => 'CS101']);
    }

    public function test_create_course_validates_required_fields(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/courses', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['programme_id', 'code', 'name', 'credit_hours']);
    }

    public function test_create_course_rejects_nonexistent_programme(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/courses', [
                'programme_id' => 9999,
                'code' => 'CS101',
                'name' => 'Introduction to Computing',
                'credit_hours' => 3,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['programme_id']);
    }

    // -------------------------------------------------------------------------
    // Academic Group CRUD
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_group(): void
    {
        $this->postJson('/groups', [
            'programme_id' => 1,
            'name' => 'CS-2026-A',
            'intake_year' => 2026,
            'semester' => 1,
        ])->assertUnauthorized();
    }

    public function test_lecturer_cannot_create_group(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $programme = Programme::query()->create([
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($lecturer)
            ->postJson('/groups', [
                'programme_id' => $programme->id,
                'name' => 'CS-2026-A',
                'intake_year' => 2026,
                'semester' => 1,
            ])
            ->assertForbidden();
    }

    public function test_coordinator_can_create_group(): void
    {
        $this->seed(RbacSeeder::class);

        $coordinator = User::factory()->create();
        $coordinator->assignRole('coordinator');

        $programme = Programme::query()->create([
            'code' => 'CS',
            'name' => 'Computer Science',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($coordinator)
            ->postJson('/groups', [
                'programme_id' => $programme->id,
                'name' => 'CS-2026-A',
                'intake_year' => 2026,
                'semester' => 1,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'CS-2026-A']);
    }

    public function test_admin_can_create_group(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $programme = Programme::query()->create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'level' => 'Diploma',
            'duration_semesters' => 6,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->postJson('/groups', [
                'programme_id' => $programme->id,
                'name' => 'IT-2026-A',
                'intake_year' => 2026,
                'semester' => 1,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'IT-2026-A']);
    }

    public function test_create_group_validates_required_fields(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/groups', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['programme_id', 'name', 'intake_year', 'semester']);
    }
}
