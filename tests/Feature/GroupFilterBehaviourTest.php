<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Group\Models\AcademicGroup;
use Modules\Programme\Models\Programme;
use Tests\TestCase;

class GroupFilterBehaviourTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_show_page_loads_with_assigned_users(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();
        $member = User::query()->create([
            'name' => 'Group Member One',
            'email' => 'group.member1@example.com',
            'password' => bcrypt('password'),
            'staff_id' => 'GM1001',
            'is_active' => true,
        ]);

        $programme = Programme::query()->create([
            'code' => 'GRPSHOW',
            'name' => 'Group Show Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $group = AcademicGroup::query()->create([
            'programme_id' => $programme->id,
            'name' => 'SHOW GROUP 1',
            'intake_year' => 2026,
            'semester' => 1,
            'is_active' => true,
        ]);

        $group->users()->attach($member->id, ['role' => 'member']);

        $this->actingAs($admin)
            ->get(route('groups.show', $group))
            ->assertOk()
            ->assertSee('SHOW GROUP 1')
            ->assertSee('Members');
    }

    public function test_group_index_filters_return_expected_subsets(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $csProgramme = Programme::query()->create([
            'code' => 'CSFILT',
            'name' => 'Computer Science Filter Programme',
            'level' => 'Bachelor',
            'duration_semesters' => 8,
            'is_active' => true,
        ]);

        $itProgramme = Programme::query()->create([
            'code' => 'ITFILT',
            'name' => 'Information Technology Filter Programme',
            'level' => 'Diploma',
            'duration_semesters' => 6,
            'is_active' => true,
        ]);

        AcademicGroup::query()->create([
            'programme_id' => $csProgramme->id,
            'name' => 'CS FILTER ALPHA',
            'intake_year' => 2026,
            'semester' => 1,
            'is_active' => true,
        ]);

        AcademicGroup::query()->create([
            'programme_id' => $csProgramme->id,
            'name' => 'CS FILTER BETA',
            'intake_year' => 2025,
            'semester' => 2,
            'is_active' => false,
        ]);

        AcademicGroup::query()->create([
            'programme_id' => $itProgramme->id,
            'name' => 'IT FILTER GAMMA',
            'intake_year' => 2026,
            'semester' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('groups.index', ['search' => 'ALPHA']))
            ->assertOk()
            ->assertSee('CS FILTER ALPHA')
            ->assertDontSee('CS FILTER BETA')
            ->assertDontSee('IT FILTER GAMMA');

        $this->actingAs($admin)
            ->get(route('groups.index', ['programme_id' => $itProgramme->id]))
            ->assertOk()
            ->assertSee('IT FILTER GAMMA')
            ->assertDontSee('CS FILTER ALPHA')
            ->assertDontSee('CS FILTER BETA');

        $this->actingAs($admin)
            ->get(route('groups.index', ['intake_year' => 2025]))
            ->assertOk()
            ->assertSee('CS FILTER BETA')
            ->assertDontSee('CS FILTER ALPHA')
            ->assertDontSee('IT FILTER GAMMA');

        $this->actingAs($admin)
            ->get(route('groups.index', ['active' => '0']))
            ->assertOk()
            ->assertSee('CS FILTER BETA')
            ->assertDontSee('CS FILTER ALPHA')
            ->assertDontSee('IT FILTER GAMMA');

        $jsonResponse = $this->actingAs($admin)
            ->getJson('/groups?active=0')
            ->assertOk()
            ->assertJsonCount(1)
            ->json();

        $this->assertSame('CS FILTER BETA', $jsonResponse[0]['name']);
    }
}
