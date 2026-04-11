<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Integration\Models\IntegrationSetting;
use Tests\TestCase;

class SsoSettingsScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_sso_settings_screen_and_nav_link(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $dashboard = $this->actingAs($admin)->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('SSO');
        $dashboard->assertSee('Settings');

        $response = $this->actingAs($admin)->get(route('integration.sso.settings'));

        $response->assertOk();
        $response->assertSee('SSO Settings');
        $response->assertSee('Role Mapping');
        $response->assertSee('Default Role');
    }

    public function test_non_admin_cannot_view_sso_settings_screen(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $response = $this->actingAs($lecturer)->get(route('integration.sso.settings'));

        $response->assertForbidden();
    }

    public function test_admin_can_save_sso_role_mapping_settings(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('integration.sso.settings.save'), [
            'default_role' => 'Reviewer',
            'external_roles' => ['faculty_admin', 'external_reviewer'],
            'local_roles' => ['Admin', 'Reviewer'],
        ]);

        $response->assertRedirect(route('integration.sso.settings'));

        $this->assertSame('Reviewer', IntegrationSetting::get('sso.default_role'));

        $roleMap = json_decode((string) IntegrationSetting::get('sso.role_map'), true);

        $this->assertSame('Admin', $roleMap['faculty_admin']);
        $this->assertSame('Reviewer', $roleMap['external_reviewer']);
    }

    public function test_login_page_shows_sso_button_when_enabled(): void
    {
        config(['services.sso.enabled' => true]);

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Login with SSO');
        $response->assertSee(route('integration.sso.redirect'), false);
    }

    public function test_login_page_hides_sso_button_when_disabled(): void
    {
        config(['services.sso.enabled' => false]);

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('Login with SSO');
    }
}
