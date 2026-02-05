<?php

namespace Database\Seeders;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) {
            return;
        }

        $now = now();

        // Sample delivery success notifications (from orders if any)
        $orders = Order::orderBy('created_at', 'desc')->take(5)->get();
        foreach ($orders as $index => $order) {
            AdminNotification::create([
                'user_id'      => $admin->id,
                'type'         => AdminNotification::TYPE_DELIVERY_SUCCESS,
                'title'        => $order->product_name,
                'message'      => null,
                'image_url'    => null,
                'related_type' => 'Order',
                'related_id'   => $order->id,
                'read_at'     => $index >= 2 ? $now : null, // first 2 unread
                'data'        => ['subtitle' => 'delivered', 'highlight' => 'Successfully'],
            ]);
        }

        // If no orders, add a few generic delivery samples
        if ($orders->isEmpty()) {
            $samples = [
                ['Strawberry', true],
                ['Mango', true],
                ['Ube Macapuno', false],
                ['Vanilla', false],
            ];
            foreach ($samples as $index => [$name, $unread]) {
                AdminNotification::create([
                    'user_id'   => $admin->id,
                    'type'      => AdminNotification::TYPE_DELIVERY_SUCCESS,
                    'title'     => $name,
                    'data'      => ['subtitle' => 'delivered', 'highlight' => 'Successfully'],
                    'read_at'   => $unread ? null : $now,
                ]);
            }
        }

        // Sample profile update notifications (optional)
        $customers = \App\Models\Customer::take(2)->get();
        foreach ($customers as $customer) {
            $name = trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? ''));
            if ($name === '') {
                $name = $customer->email ?? 'A customer';
            }
            AdminNotification::create([
                'user_id'   => $admin->id,
                'type'      => AdminNotification::TYPE_PROFILE_UPDATE,
                'title'     => $name,
                'image_url' => $customer->image ?: null,
                'related_type' => 'Customer',
                'related_id'   => $customer->id,
                'read_at'   => null,
                'data'      => ['subtitle' => 'changed his', 'highlight' => 'Phone Number'],
            ]);
        }
    }
}
