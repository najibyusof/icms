<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NavigationMenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_main_navigation_modules(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee(route('courses.index'), false)
            ->assertSee(route('programmes.index'), false)
            ->assertSee(route('groups.index'), false)
            ->assertSee(route('users.index'), false);
    }

    public function test_lecturer_sees_academic_navigation_without_user_management(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::query()->create([
            'name' => 'Navigation Lecturer',
            'email' => 'navigation.lecturer@academic.local',
            'staff_id' => 'LEC9001',
            'faculty' => 'School of Computing',
            'password' => Hash::make('password'),
        ]);
        $lecturer->syncRoles(['Lecturer', 'lecturer']);

        $response = $this->actingAs($lecturer)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee(route('courses.index'), false)
            ->assertSee(route('programmes.index'), false)
            ->assertSee(route('groups.index'), false)
            ->assertDontSee(route('users.index'), false);
    }

    public function test_reviewer_does_not_see_main_management_menu_links(): void
    {
        $this->seed(RbacSeeder::class);

        $reviewer = User::query()->create([
            'name' => 'Navigation Reviewer',
            'email' => 'navigation.reviewer@academic.local',
            'staff_id' => 'REV9001',
            'faculty' => 'Quality Assurance',
            'password' => Hash::make('password'),
        ]);
        $reviewer->syncRoles(['Reviewer', 'reviewer']);

        $response = $this->actingAs($reviewer)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertDontSee(route('courses.index'), false)
            ->assertDontSee(route('programmes.index'), false)
            ->assertDontSee(route('groups.index'), false)
            ->assertDontSee(route('users.index'), false);
    }

    public function test_active_state_is_applied_for_current_module(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('courses.index'));

        $response
            ->assertOk()
            ->assertSee('ams-sidebar-link-active', false);
    }
}
