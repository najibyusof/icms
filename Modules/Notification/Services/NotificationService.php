<?php

namespace Modules\Notification\Services;

use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Modules\Notification\Channels\TelegramChannel;
use Modules\Notification\Models\NotificationSetting;
use Modules\Notification\Notifications\EntityStatusNotification;

class NotificationService
{
    /**
     * @var array<int, string>
     */
    private array $states = ['submitted', 'approved', 'rejected'];

    /**
     * @var array<int, string>
     */
    private array $channels = ['database', 'mail', 'telegram', 'push'];

    public function __construct(private readonly PushNotificationService $pushNotificationService)
    {
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function channelMatrix(): array
    {
        $matrix = [];

        foreach ($this->states as $state) {
            foreach ($this->channels as $channel) {
                $matrix[$state][$channel] = $this->isChannelEnabled($channel, $state);
            }
        }

        return $matrix;
    }

    /**
     * @param array<string, array<string, bool|int|string>> $matrix
     */
    public function saveChannelMatrix(array $matrix): void
    {
        foreach ($this->states as $state) {
            foreach ($this->channels as $channel) {
                $value = (bool) data_get($matrix, "{$state}.{$channel}", false);
                NotificationSetting::set($this->key($state, $channel), $value);
            }
        }
    }

    public function isChannelEnabled(string $channel, string $state): bool
    {
        $raw = NotificationSetting::get($this->key($state, $channel));

        if ($raw === null) {
            return true;
        }

        return in_array((string) $raw, ['1', 'true', 'yes'], true);
    }

    private function key(string $state, string $channel): string
    {
        return "notification.channels.{$state}.{$channel}";
    }

    /**
     * @param array<int, int>|null $recipientIds
     * @return Collection<int, User>
     */
    public function resolveRecipients(?array $recipientIds, int $actorId): Collection
    {
        if (! empty($recipientIds)) {
            return User::query()
                ->whereIn('id', $recipientIds)
                ->where('id', '!=', $actorId)
                ->get();
        }

        return User::role(['Reviewer', 'Approver', 'reviewer', 'approver'])
            ->where('id', '!=', $actorId)
            ->get();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendDatabase(User $recipient, array $payload): void
    {
        if (! $this->isChannelEnabled('database', (string) ($payload['state'] ?? 'submitted'))) {
            return;
        }

        $recipient->notify(new EntityStatusNotification($payload, ['database']));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendMail(User $recipient, array $payload): void
    {
        if (! $this->isChannelEnabled('mail', (string) ($payload['state'] ?? 'submitted'))) {
            return;
        }

        if (empty($recipient->email)) {
            return;
        }

        $recipient->notify(new EntityStatusNotification($payload, ['mail']));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendTelegram(User $recipient, array $payload): void
    {
        if (! $this->isChannelEnabled('telegram', (string) ($payload['state'] ?? 'submitted'))) {
            return;
        }

        if (! (bool) config('services.telegram.enabled')) {
            return;
        }

        $chatId = $recipient->telegram_chat_id ?? config('services.telegram.chat_id');

        if (empty($chatId)) {
            return;
        }

        $notifiable = new AnonymousNotifiable();
        $notifiable->route(TelegramChannel::class, $chatId);
        $notifiable->notify(new EntityStatusNotification($payload, [TelegramChannel::class]));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendPush(User $recipient, array $payload): void
    {
        if (! $this->isChannelEnabled('push', (string) ($payload['state'] ?? 'submitted'))) {
            return;
        }

        $token = $recipient->fcm_token ?? null;

        if (empty($token)) {
            return;
        }

        $this->pushNotificationService->dispatch([$token], $payload);
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    public function payload(
        string $state,
        string $entityType,
        int $entityId,
        int $actorId,
        array $meta = [],
    ): array {
        $title = match ($state) {
            'submitted' => 'Entity Submitted',
            'approved' => 'Entity Approved',
            'rejected' => 'Entity Rejected',
            default => 'Entity Notification',
        };

        return [
            'title' => $title,
            'message' => sprintf('%s #%d has been %s.', class_basename($entityType), $entityId, $state),
            'state' => $state,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'actor_id' => $actorId,
            'meta' => $meta,
        ];
    }
}
