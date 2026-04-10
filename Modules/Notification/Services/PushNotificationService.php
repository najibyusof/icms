<?php

namespace Modules\Notification\Services;

class PushNotificationService
{
    /**
     * Placeholder for Firebase Cloud Messaging integration.
     *
     * @param  array<int, string>  $deviceTokens
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(array $deviceTokens, array $payload): void
    {
        if (empty(config('services.fcm.project_id')) || empty(config('services.fcm.credentials'))) {
            return;
        }

        // Integrate Firebase Admin SDK here when push notifications are enabled.
    }
}
