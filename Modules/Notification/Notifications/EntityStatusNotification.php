<?php

namespace Modules\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EntityStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $channels
     */
    public function __construct(
        private readonly array $payload,
        private readonly array $channels,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject((string) ($this->payload['title'] ?? 'Entity Notification'))
            ->line((string) ($this->payload['message'] ?? 'An entity update was recorded.'))
            ->line('Entity: ' . (string) ($this->payload['entity_type'] ?? 'unknown'))
            ->line('Entity ID: ' . (string) ($this->payload['entity_id'] ?? '-'));
    }

    /**
     * @return array<string, string>
     */
    public function toTelegram(object $notifiable): array
    {
        return [
            'text' => sprintf(
                "%s\n%s\nEntity: %s #%s",
                (string) ($this->payload['title'] ?? 'Entity Notification'),
                (string) ($this->payload['message'] ?? ''),
                (string) ($this->payload['entity_type'] ?? 'unknown'),
                (string) ($this->payload['entity_id'] ?? '-')
            ),
        ];
    }
}
