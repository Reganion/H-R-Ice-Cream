<?php

namespace App\Observers;

use App\Models\DriverNotification;
use App\Services\FcmPushService;
use App\Services\FirebaseRealtimeService;

class DriverNotificationObserver
{
    public function __construct(
        protected FirebaseRealtimeService $firebase,
        protected FcmPushService $fcmPush
    ) {}

    public function created(DriverNotification $notification): void
    {
        $this->syncToFirebase($notification);
        $this->fcmPush->sendDriverNotification($notification);
    }

    public function updated(DriverNotification $notification): void
    {
        if ($notification->wasChanged('read_at')) {
            $this->firebase->updateDriverNotificationReadAt(
                (int) $notification->driver_id,
                (int) $notification->id,
                $notification->read_at?->toIso8601String()
            );
        }
    }

    public function deleted(DriverNotification $notification): void
    {
        try {
            $this->firebase->deleteDriverNotificationItem(
                (int) $notification->driver_id,
                (int) $notification->id
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function syncToFirebase(DriverNotification $notification): void
    {
        $this->firebase->syncDriverNotification(
            (int) $notification->driver_id,
            (int) $notification->id,
            [
                'id' => $notification->id,
                'driver_id' => $notification->driver_id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'image_url' => $notification->image_url,
                'related_type' => $notification->related_type,
                'related_id' => $notification->related_id,
                'read_at' => $notification->read_at?->toIso8601String(),
                'data' => $notification->data,
                'created_at' => $notification->created_at->toIso8601String(),
            ]
        );
    }
}
