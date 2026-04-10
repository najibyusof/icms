<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/integration/sso/validate-token';

    public function test_guest_is_redirected_from_sso_endpoint(): void
    {
        $this->post(self::ENDPOINT, ['token' => 'any'])
            ->assertRedirect(route('login'));
    }

    public function test_lecturer_cannot_call_sso_validate(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('lecturer');

        $this->actingAs($lecturer)
            ->postJson(self::ENDPOINT, ['token' => 'any-token'])
            ->assertForbidden();
    }

    public function test_sso_endpoint_requires_token_field(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(self::ENDPOINT, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);
    }

    public function test_admin_receives_valid_response_for_non_empty_token(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(self::ENDPOINT, ['token' => 'valid-external-token'])
            ->assertOk()
            ->assertJsonStructure(['valid', 'subject', 'email', 'roles'])
            ->assertJsonFragment([
                'valid' => true,
                'email' => 'user@example.edu',
            ]);
    }

    public function test_sso_service_returns_invalid_for_empty_string_token(): void
    {
        // The validator enforces `required`, so an empty string is caught there.
        // This test confirms that the `required` rule fires before SsoService::validateToken
        // ever sees an empty string.
        $this->seed(RbacSeeder::class);

        $admin = User::query()->where('email', 'admin@academic.local')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(self::ENDPOINT, ['token' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);
    }

    public function test_reviewer_role_cannot_call_sso_validate(): void
    {
        $this->seed(RbacSeeder::class);

        $reviewer = User::factory()->create();
        $reviewer->assignRole('reviewer');

        $this->actingAs($reviewer)
            ->postJson(self::ENDPOINT, ['token' => 'some-token'])
            ->assertForbidden();
    }
}
