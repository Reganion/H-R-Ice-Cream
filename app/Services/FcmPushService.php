<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Driver;
use App\Models\DriverNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class FcmPushService
{
    public function __construct(
        protected Messaging $messaging
    ) {}

    public function sendCustomerNotification(CustomerNotification $notification): void
    {
        $customerId = (int) $notification->customer_id;
        $token = $this->freshCustomerFcmToken($customerId);
        if ($token === '') {
            Log::info('FCM skipped: customer has no fcm_token', ['customer_id' => $customerId]);

            return;
        }

        $title = trim((string) ($notification->title ?? 'Notification'));
        $body = $this->notificationBody(
            (string) ($notification->message ?? ''),
            $notification->data
        );

        $this->sendToToken($token, $title !== '' ? $title : 'Notification', $body, [
            'channel' => 'customer_notifications',
            'notification_id' => (string) $notification->id,
            'type' => (string) ($notification->type ?? ''),
            'related_type' => (string) ($notification->related_type ?? ''),
            'related_id' => (string) ($notification->related_id ?? ''),
        ], customerIdForCleanup: $customerId);
    }

    public function sendDriverNotification(DriverNotification $notification): void
    {
        $driverId = (int) $notification->driver_id;
        $token = $this->freshDriverFcmToken($driverId);
        if ($token === '') {
            Log::info('FCM skipped: driver has no fcm_token', ['driver_id' => $driverId]);

            return;
        }

        $title = trim((string) ($notification->title ?? 'Notification'));
        $body = $this->notificationBody(
            (string) ($notification->message ?? ''),
            $notification->data
        );

        $this->sendToToken($token, $title !== '' ? $title : 'Notification', $body, [
            'channel' => 'driver_notifications',
            'notification_id' => (string) $notification->id,
            'type' => (string) ($notification->type ?? ''),
            'related_type' => (string) ($notification->related_type ?? ''),
            'related_id' => (string) ($notification->related_id ?? ''),
        ], driverIdForCleanup: $driverId);
    }

    public function sendOrderMessageToCustomer(Order $order, string $message): void
    {
        $customerId = (int) ($order->customer_id ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $token = $this->freshCustomerFcmToken($customerId);
        if ($token === '') {
            return;
        }

        $order->loadMissing(['driver']);
        $driverName = trim((string) ($order->driver?->name ?? 'Driver'));
        $title = $driverName !== '' ? $driverName : 'Driver';
        $body = trim($message) !== '' ? trim($message) : 'You have a new message.';

        $this->sendToToken($token, $title, $body, [
            'channel' => 'order_messages',
            'order_id' => (string) $order->id,
            'transaction_id' => (string) ($order->transaction_id ?? ''),
            'sender' => 'driver',
        ], customerIdForCleanup: $customerId);
    }

    public function sendOrderMessageToDriver(Order $order, string $message): void
    {
        $driverId = (int) ($order->driver_id ?? 0);
        if ($driverId <= 0) {
            return;
        }

        $token = $this->freshDriverFcmToken($driverId);
        if ($token === '') {
            return;
        }

        $customerName = trim((string) ($order->customer_name ?? 'Customer'));
        $title = $customerName !== '' ? $customerName : 'Customer';
        $body = trim($message) !== '' ? trim($message) : 'You have a new message.';

        $this->sendToToken($token, $title, $body, [
            'channel' => 'order_messages',
            'order_id' => (string) $order->id,
            'transaction_id' => (string) ($order->transaction_id ?? ''),
            'sender' => 'customer',
        ], driverIdForCleanup: $driverId);
    }

    private function freshCustomerFcmToken(int $customerId): string
    {
        if ($customerId <= 0) {
            return '';
        }

        return trim((string) (Customer::query()->whereKey($customerId)->value('fcm_token') ?? ''));
    }

    private function freshDriverFcmToken(int $driverId): string
    {
        if ($driverId <= 0) {
            return '';
        }

        return trim((string) (Driver::query()->whereKey($driverId)->value('fcm_token') ?? ''));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data,
        ?int $customerIdForCleanup = null,
        ?int $driverIdForCleanup = null,
    ): void {
        $stringData = $this->stringifyData($data);

        try {
            // Prefer structured message (Android HIGH priority). Fallback if Kreait rejects payload shape.
            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'HIGH',
                ],
            ]);

            $this->messaging->send($message);
        } catch (Throwable $e) {
            try {
                $fallback = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($stringData);
                $this->messaging->send($fallback);
            } catch (Throwable $e2) {
                $this->maybeClearStaleToken($token, $e2, $customerIdForCleanup, $driverIdForCleanup);
                report($e2);
            }
        }
    }

    private function maybeClearStaleToken(
        string $token,
        Throwable $e,
        ?int $customerId,
        ?int $driverId,
    ): void {
        $msg = strtolower($e->getMessage());
        $stale = str_contains($msg, 'registration-token-not-registered')
            || str_contains($msg, 'not a valid fcm registration token')
            || str_contains($msg, 'requested entity was not found')
            || str_contains($msg, 'unregistered');

        if (!$stale) {
            return;
        }

        if ($customerId !== null && $customerId > 0) {
            Customer::query()
                ->whereKey($customerId)
                ->where('fcm_token', $token)
                ->update(['fcm_token' => null, 'fcm_platform' => null]);
        }

        if ($driverId !== null && $driverId > 0) {
            Driver::query()
                ->whereKey($driverId)
                ->where('fcm_token', $token)
                ->update(['fcm_token' => null, 'fcm_platform' => null]);
        }
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function notificationBody(string $message, ?array $data): string
    {
        $message = trim($message);
        if ($message !== '') {
            return $message;
        }

        $subtitle = trim((string) ($data['subtitle'] ?? ''));
        $highlight = trim((string) ($data['highlight'] ?? ''));
        $chunks = array_values(array_filter([$subtitle, $highlight], fn ($v) => $v !== ''));

        if (count($chunks) > 0) {
            return implode(' ', $chunks);
        }

        return 'You have a new notification.';
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (! is_scalar($value) && $value !== null) {
                $out[(string) $key] = json_encode($value);

                continue;
            }
            $out[(string) $key] = (string) ($value ?? '');
        }

        return $out;
    }
}
