<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Notification\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('notification.view'), 403);

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        abort_unless($request->user()?->can('notification.view'), 403);

        $notification = $request->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function settings(Request $request): View
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        return view('notifications.settings', [
            'matrix' => $this->notificationService->channelMatrix(),
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Admin', 'admin']), 403);

        $states = ['submitted', 'approved', 'rejected'];
        $channels = ['database', 'mail', 'telegram', 'push'];

        $rules = [];

        foreach ($states as $state) {
            foreach ($channels as $channel) {
                $rules["channels.{$state}.{$channel}"] = ['nullable', 'boolean'];
            }
        }

        $validated = $request->validate($rules);

        $this->notificationService->saveChannelMatrix($validated['channels'] ?? []);

        return redirect()
            ->route('notifications.settings')
            ->with('success', 'Notification settings updated successfully.');
    }
}
