<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementCanonicalRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_displays_alias_roles_with_canonical_label(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $user = User::query()->create([
            'name' => 'Alias Role User',
            'email' => 'alias.role@academic.local',
            'staff_id' => 'ALS1001',
            'faculty' => 'School of Computing',
            'password' => 'password',
        ]);
        $user->syncRoles(['coordinator']);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response
            ->assertOk()
            ->assertSee('Programme Coordinator')
            ->assertDontSee('>coordinator<', false);
    }
}