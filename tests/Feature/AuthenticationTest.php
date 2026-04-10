<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login page
    // -------------------------------------------------------------------------

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign In');
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->seed(RbacSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // Login via email
    // -------------------------------------------------------------------------

    public function test_user_can_login_with_email(): void
    {
        User::factory()->create([
            'email' => 'lecturer@test.local',
            'password' => Hash::make('Secret99'),
        ]);

        $this->post(route('login.store'), [
            'login' => 'lecturer@test.local',
            'password' => 'Secret99',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_wrong_email_password(): void
    {
        User::factory()->create([
            'email' => 'lecturer@test.local',
            'password' => Hash::make('Secret99'),
        ]);

        $this->post(route('login.store'), [
            'login' => 'lecturer@test.local',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Login via staff_id
    // -------------------------------------------------------------------------

    public function test_user_can_login_with_staff_id(): void
    {
        User::factory()->create([
            'staff_id' => 'STF1001',
            'password' => Hash::make('Secret55'),
        ]);

        $this->post(route('login.store'), [
            'login' => 'STF1001',
            'password' => 'Secret55',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_wrong_staff_id(): void
    {
        User::factory()->create([
            'staff_id' => 'STF1001',
            'password' => Hash::make('Secret55'),
        ]);

        $this->post(route('login.store'), [
            'login' => 'STF9999',
            'password' => 'Secret55',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_correct_staff_id_and_wrong_password(): void
    {
        User::factory()->create([
            'staff_id' => 'STF1001',
            'password' => Hash::make('Secret55'),
        ]);

        $this->post(route('login.store'), [
            'login' => 'STF1001',
            'password' => 'BadPass1',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Login validation — password complexity rules
    // -------------------------------------------------------------------------

    public function test_login_requires_login_field(): void
    {
        $this->post(route('login.store'), ['password' => 'Secret99'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_requires_password_field(): void
    {
        $this->post(route('login.store'), ['login' => 'test@test.local'])
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_cannot_access_logout(): void
    {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Forgot password page
    // -------------------------------------------------------------------------

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot Password');
    }

    public function test_forgot_password_requires_email_field(): void
    {
        $this->post(route('password.email'), [])
            ->assertSessionHasErrors('email');
    }

    public function test_forgot_password_rejects_invalid_email_format(): void
    {
        $this->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_forgot_password_sends_reset_link_for_existing_email(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        User::factory()->create(['email' => 'staff@test.local']);

        $this->post(route('password.email'), ['email' => 'staff@test.local'])
            ->assertSessionHas('status');
    }

    // -------------------------------------------------------------------------
    // Reset password page
    // -------------------------------------------------------------------------

    public function test_reset_password_page_renders_with_token(): void
    {
        $this->get(route('password.reset', ['token' => 'fake-token', 'email' => 'staff@test.local']))
            ->assertOk()
            ->assertSee('Reset Password');
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $this->post(route('password.store'), [])
            ->assertSessionHasErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_enforces_min_8_chars_with_number(): void
    {
        $this->post(route('password.store'), [
            'token' => 'any',
            'email' => 'staff@test.local',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }

    public function test_reset_password_rejects_password_without_number(): void
    {
        $this->post(route('password.store'), [
            'token' => 'any',
            'email' => 'staff@test.local',
            'password' => 'onlyletters',
            'password_confirmation' => 'onlyletters',
        ])->assertSessionHasErrors('password');
    }

    public function test_reset_password_rejects_mismatched_confirmation(): void
    {
        $this->post(route('password.store'), [
            'token' => 'any',
            'email' => 'staff@test.local',
            'password' => 'Valid8password',
            'password_confirmation' => 'Different8password',
        ])->assertSessionHasErrors('password');
    }

    // -------------------------------------------------------------------------
    // Change password page
    // -------------------------------------------------------------------------

    public function test_change_password_page_is_accessible_when_authenticated(): void
    {
        $this->seed(RbacSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('password.change'))
            ->assertOk()
            ->assertSee('Change Password');
    }

    public function test_guest_cannot_access_change_password_page(): void
    {
        $this->get(route('password.change'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_change_own_password(): void
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create([
            'password' => Hash::make('OldPass1'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->put(route('password.change.update'), [
                'current_password' => 'OldPass1',
                'password' => 'NewPass2',
                'password_confirmation' => 'NewPass2',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPass2', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create([
            'password' => Hash::make('OldPass1'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->put(route('password.change.update'), [
                'current_password' => 'WrongPass9',
                'password' => 'NewPass2',
                'password_confirmation' => 'NewPass2',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_change_password_enforces_min_8_chars_with_number(): void
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create([
            'password' => Hash::make('OldPass1'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->put(route('password.change.update'), [
                'current_password' => 'OldPass1',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_change_password_rejects_password_without_number(): void
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create([
            'password' => Hash::make('OldPass1'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->put(route('password.change.update'), [
                'current_password' => 'OldPass1',
                'password' => 'onlyletters',
                'password_confirmation' => 'onlyletters',
            ])
            ->assertSessionHasErrors('password');
    }

    // -------------------------------------------------------------------------
    // RBAC — role-based access to change-password route
    // -------------------------------------------------------------------------

    public function test_all_valid_roles_can_access_change_password(): void
    {
        $this->seed(RbacSeeder::class);

        foreach (['admin', 'lecturer', 'coordinator', 'reviewer', 'approver'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)
                ->get(route('password.change'))
                ->assertOk();
        }
    }
}
