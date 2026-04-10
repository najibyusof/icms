<?php

namespace Tests\Feature;

use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_entry_points_are_available(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Academic Management System');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign In');
    }

    public function test_guest_is_redirected_from_protected_modules(): void
    {
        $this->get('/users')
            ->assertRedirect(route('login'));

        $this->get('/programmes')
            ->assertRedirect(route('login'));
    }

    public function test_seeded_admin_can_access_protected_module_endpoints(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = \App\Models\User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->getJson('/users')
            ->assertOk();

        $this->actingAs($admin)
            ->getJson('/programmes')
            ->assertOk();
    }
}
