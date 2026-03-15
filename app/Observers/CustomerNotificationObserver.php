<?php

namespace App\Observers;

use App\Models\CustomerNotification;
use App\Services\FirebaseRealtimeService;

class CustomerNotificationObserver
{
    public function __construct(
        protected FirebaseRealtimeService $firebase
    ) {}

    /**
     * Sync new notification to Firebase for real-time clients.
     */
    public function created(CustomerNotification $notification): void
    {
        $this->syncToFirebase($notification);
    }

    /**
     * When read_at is set, update Firebase so clients see read status in real time.
     */
    public function updated(CustomerNotification $notification): void
    {
        if ($notification->wasChanged('read_at')) {
            $this->firebase->updateNotificationReadAt(
                (int) $notification->customer_id,
                (int) $notification->id,
                $notification->read_at?->toIso8601String()
            );
        }
    }

    protected function syncToFirebase(CustomerNotification $notification): void
    {
        $this->firebase->syncNotification(
            (int) $notification->customer_id,
            (int) $notification->id,
            [
                'id' => $notification->id,
                'customer_id' => $notification->customer_id,
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
