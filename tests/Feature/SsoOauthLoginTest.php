<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SsoOauthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_sso_redirect_route_redirects_to_oauth_provider(): void
    {
        config([
            'services.sso.enabled' => true,
            'services.sso.driver' => 'google',
            'services.sso.stateless' => true,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://provider.example/auth'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('integration.sso.redirect'))
            ->assertRedirect('https://provider.example/auth');
    }

    public function test_sso_callback_auto_creates_user_and_maps_roles(): void
    {
        $this->seed(RbacSeeder::class);

        config([
            'services.sso.enabled' => true,
            'services.sso.driver' => 'google',
            'services.sso.stateless' => true,
        ]);

        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = 'external-1001';
        $socialiteUser->name = 'External Lecturer';
        $socialiteUser->email = 'external.lecturer@example.edu';
        $socialiteUser->user = [
            'roles' => ['reviewer', 'approver'],
            'staff_id' => 'EXT1001',
            'faculty' => 'School of Computing',
        ];

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('integration.sso.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'external.lecturer@example.edu')->firstOrFail();

        $this->assertSame('google', $user->sso_provider);
        $this->assertSame('external-1001', $user->sso_subject);
        $this->assertSame('EXT1001', $user->staff_id);
        $this->assertSame('School of Computing', $user->faculty);
        $this->assertTrue($user->hasRole('Reviewer'));
        $this->assertTrue($user->hasRole('Approver'));
        $this->assertNotNull($user->last_sso_login_at);
    }

    public function test_sso_callback_updates_existing_user_matched_by_email(): void
    {
        $this->seed(RbacSeeder::class);

        config([
            'services.sso.enabled' => true,
            'services.sso.driver' => 'google',
            'services.sso.stateless' => true,
        ]);

        $existingUser = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.edu',
        ]);
        $existingUser->assignRole('Lecturer');

        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = 'external-2002';
        $socialiteUser->name = 'Updated Existing User';
        $socialiteUser->email = 'existing@example.edu';
        $socialiteUser->user = [
            'roles' => ['admin'],
            'staff_id' => 'EX2002',
            'faculty' => 'Academic Affairs',
        ];

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('integration.sso.callback'))
            ->assertRedirect(route('dashboard'));

        $existingUser->refresh();

        $this->assertSame('Updated Existing User', $existingUser->name);
        $this->assertSame('EX2002', $existingUser->staff_id);
        $this->assertSame('Academic Affairs', $existingUser->faculty);
        $this->assertSame('external-2002', $existingUser->sso_subject);
        $this->assertTrue($existingUser->hasRole('Admin'));
    }

    public function test_sso_auth_middleware_redirects_guest_to_sso_redirect(): void
    {
        config([
            'services.sso.enabled' => true,
        ]);

        $this->get(route('integration.sso.me'))
            ->assertRedirect(route('integration.sso.redirect'));
    }

    public function test_sso_me_endpoint_returns_authenticated_user_context(): void
    {
        $this->seed(RbacSeeder::class);

        config([
            'services.sso.enabled' => true,
        ]);

        $user = User::factory()->create([
            'staff_id' => 'ME1001',
            'faculty' => 'Engineering',
        ]);
        $user->assignRole('Lecturer');

        $this->actingAs($user)
            ->getJson(route('integration.sso.me'))
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.staff_id', 'ME1001');
    }
}
