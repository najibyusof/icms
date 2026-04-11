<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Course\Models\Course;
use Modules\Notification\Events\EntityApproved;
use Modules\Notification\Events\EntityRejected;
use Modules\Notification\Events\EntitySubmitted;
use Modules\Notification\Listeners\SendDatabaseNotification;
use Modules\Notification\Listeners\SendMailNotification;
use Modules\Notification\Listeners\SendPushNotification;
use Modules\Notification\Listeners\SendTelegramNotification;
use Modules\Workflow\Events\WorkflowSubmitted;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowInstance;
use Tests\TestCase;

class NotificationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_entity_submitted_event_creates_database_notification(): void
    {
        $this->seed(RbacSeeder::class);

        $actor = User::factory()->create();
        $recipient = User::factory()->create();

        event(new EntitySubmitted(
            entityType: Course::class,
            entityId: 99,
            actorId: $actor->id,
            recipientIds: [$recipient->id],
            meta: ['source' => 'test'],
        ));

        $notification = $recipient->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('Entity Submitted', data_get($notification->data, 'title'));
        $this->assertSame(Course::class, data_get($notification->data, 'entity_type'));
        $this->assertSame(99, data_get($notification->data, 'entity_id'));
    }

    public function test_entity_approved_and_rejected_events_create_notifications(): void
    {
        $this->seed(RbacSeeder::class);

        $actor = User::factory()->create();
        $recipient = User::factory()->create();

        event(new EntityApproved(Course::class, 5, $actor->id, [$recipient->id], ['source' => 'approved']));
        event(new EntityRejected(Course::class, 5, $actor->id, [$recipient->id], ['source' => 'rejected']));

        $titles = $recipient->notifications()->get()->pluck('data.title')->all();

        $this->assertContains('Entity Approved', $titles);
        $this->assertContains('Entity Rejected', $titles);
    }

    public function test_workflow_submitted_event_bridges_to_entity_submitted_notifications(): void
    {
        $this->seed(RbacSeeder::class);

        $submitter = User::factory()->create();
        $submitter->assignRole('Lecturer');

        $reviewer = User::factory()->create();
        $reviewer->assignRole('Reviewer');

        $workflow = Workflow::query()->create([
            'name' => 'Notification Bridge Test Workflow',
            'description' => 'Test workflow',
            'entity_type' => 'course',
            'is_active' => true,
            'config' => ['version' => 1],
        ]);

        $instance = WorkflowInstance::query()->create([
            'workflow_id' => $workflow->id,
            'entity_type' => Course::class,
            'entity_id' => 1,
            'workflowable_type' => Course::class,
            'workflowable_id' => 1,
            'status' => 'in_progress',
            'created_by' => $submitter->id,
            'submitted_by' => $submitter->id,
            'initiated_by' => $submitter->id,
            'submitted_at' => now(),
        ]);

        event(new WorkflowSubmitted($instance));

        $notification = $reviewer->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('Entity Submitted', data_get($notification->data, 'title'));
        $this->assertSame(Course::class, data_get($notification->data, 'entity_type'));
        $this->assertSame(1, data_get($notification->data, 'entity_id'));
    }

    public function test_notification_channel_listeners_are_queued(): void
    {
        $listeners = [
            SendDatabaseNotification::class,
            SendMailNotification::class,
            SendTelegramNotification::class,
            SendPushNotification::class,
        ];

        foreach ($listeners as $listener) {
            $this->assertContains(ShouldQueue::class, class_implements($listener));
        }
    }
}
