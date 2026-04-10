<?php

namespace Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Notification\Channels\TelegramChannel;
use Modules\Workflow\Models\WorkflowInstance;

class WorkflowStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly WorkflowInstance $workflow, private readonly string $headline)
    {
        $this->onQueue('notifications');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if ((bool) config('services.telegram.enabled')) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'headline' => $this->headline,
            'workflow_id' => $this->workflow->id,
            'status' => $this->workflow->status,
            'current_stage' => $this->workflow->current_stage,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Workflow Update: '.$this->headline)
            ->line('A workflow record has been updated and requires your attention.')
            ->line('Workflow ID: '.$this->workflow->id)
            ->line('Status: '.strtoupper($this->workflow->status));
    }

    /**
     * @return array<string, string>
     */
    public function toTelegram(object $notifiable): array
    {
        return [
            'text' => sprintf(
                '[%s] Workflow #%d is now %s (stage %s).',
                $this->headline,
                $this->workflow->id,
                strtoupper($this->workflow->status),
                $this->workflow->current_stage ?? '-'
            ),
        ];
    }
}
