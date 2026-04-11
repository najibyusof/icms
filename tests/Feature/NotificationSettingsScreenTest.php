<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Notification\Events\EntitySubmitted;
use Modules\Notification\Models\NotificationSetting;
use Tests\TestCase;

class NotificationSettingsScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_notification_settings_screen(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('notifications.settings'));

        $response->assertOk();
        $response->assertSee('Notification Settings');
        $response->assertSee('Save Settings');
    }

    public function test_non_admin_cannot_view_notification_settings_screen(): void
    {
        $this->seed(RbacSeeder::class);

        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');

        $response = $this->actingAs($lecturer)->get(route('notifications.settings'));

        $response->assertForbidden();
    }

    public function test_admin_can_save_notification_channel_settings(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $payload = [
            'channels' => [
                'submitted' => ['database' => '1', 'mail' => '0', 'telegram' => '0', 'push' => '1'],
                'approved' => ['database' => '1', 'mail' => '1', 'telegram' => '1', 'push' => '0'],
                'rejected' => ['database' => '1', 'mail' => '1', 'telegram' => '0', 'push' => '0'],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('notifications.settings.save'), $payload);

        $response->assertRedirect(route('notifications.settings'));

        $this->assertSame('1', NotificationSetting::get('notification.channels.submitted.database'));
        $this->assertSame('0', NotificationSetting::get('notification.channels.submitted.mail'));
        $this->assertSame('1', NotificationSetting::get('notification.channels.approved.telegram'));
        $this->assertSame('0', NotificationSetting::get('notification.channels.rejected.push'));
    }

    public function test_disabling_submitted_database_channel_stops_in_app_notification(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $recipient = User::factory()->create();
        $actor = User::factory()->create();

        $this->actingAs($admin)->post(route('notifications.settings.save'), [
            'channels' => [
                'submitted' => ['database' => '0', 'mail' => '1', 'telegram' => '1', 'push' => '1'],
                'approved' => ['database' => '1', 'mail' => '1', 'telegram' => '1', 'push' => '1'],
                'rejected' => ['database' => '1', 'mail' => '1', 'telegram' => '1', 'push' => '1'],
            ],
        ]);

        event(new EntitySubmitted(
            entityType: Course::class,
            entityId: 7,
            actorId: $actor->id,
            recipientIds: [$recipient->id],
            meta: ['source' => 'settings-test'],
        ));

        $this->assertNull($recipient->notifications()->first());
    }
}
