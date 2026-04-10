<?php

namespace Modules\Notification\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class TelegramChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! config('services.telegram.enabled')) {
            return;
        }

        $payload = method_exists($notification, 'toTelegram')
            ? $notification->toTelegram($notifiable)
            : null;

        if (! is_array($payload) || empty($payload['text'])) {
            return;
        }

        Http::timeout(4)
            ->post(sprintf('https://api.telegram.org/bot%s/sendMessage', config('services.telegram.bot_token')), [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $payload['text'],
            ]);
    }
}
