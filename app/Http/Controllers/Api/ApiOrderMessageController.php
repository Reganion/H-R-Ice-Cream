<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiOrderMessageController extends Controller
{
    /**
     * Driver: list messages for a shipment/order.
     * GET /api/v1/driver/shipments/{id}/messages
     */
    public function driverMessages(Request $request, int $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = $this->resolveDriverOrder($driver, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if (!$order->customer_id) {
            return response()->json(['success' => false, 'message' => 'Shipment has no linked customer account.'], 422);
        }

        $perPage = min(max((int) $request->query('per_page', 50), 1), 100);
        $messages = OrderMessage::query()
            ->where('order_id', $order->id)
            ->orderBy('created_at')
            ->paginate($perPage);

        $items = $messages->getCollection()
            ->map(fn (OrderMessage $m) => $this->formatMessage($m, OrderMessage::SENDER_DRIVER))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'customer_id' => (int) $order->customer_id,
            'driver_id' => (int) $driver->id,
            'data' => $items,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Driver: send message to customer for a shipment/order.
     * POST /api/v1/driver/shipments/{id}/messages
     */
    public function driverSend(Request $request, int $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $order = $this->resolveDriverOrder($driver, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if (!$order->customer_id) {
            return response()->json(['success' => false, 'message' => 'Shipment has no linked customer account.'], 422);
        }

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'driver_id' => (int) $driver->id,
            'customer_id' => (int) $order->customer_id,
            'sender_type' => OrderMessage::SENDER_DRIVER,
            'message' => trim((string) $request->input('message')),
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatMessage($message, OrderMessage::SENDER_DRIVER),
        ], 201);
    }

    /**
     * Driver: mark customer messages as read in this shipment thread.
     * POST /api/v1/driver/shipments/{id}/messages/read
     */
    public function driverMarkRead(Request $request, int $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = $this->resolveDriverOrder($driver, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        OrderMessage::query()
            ->where('order_id', $order->id)
            ->where('sender_type', OrderMessage::SENDER_CUSTOMER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Marked as read.',
        ]);
    }

    /**
     * Customer: list messages for an order.
     * GET /api/v1/orders/{id}/messages
     */
    public function customerMessages(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $order = $this->resolveCustomerOrder($customer, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!$order->driver_id) {
            return response()->json(['success' => false, 'message' => 'No driver assigned yet for this order.'], 422);
        }
        $driver = $order->driver;
        $driverName = trim((string) ($driver?->name ?? ''));
        $driverPhone = trim((string) ($driver?->phone ?? ''));
        $driverCode = trim((string) ($driver?->driver_code ?? ''));

        $perPage = min(max((int) $request->query('per_page', 50), 1), 100);
        $messages = OrderMessage::query()
            ->where('order_id', $order->id)
            ->where('customer_status', OrderMessage::CUSTOMER_STATUS_ACTIVE)
            ->orderBy('created_at')
            ->paginate($perPage);

        $items = $messages->getCollection()
            ->map(fn (OrderMessage $m) => $this->formatMessage($m, OrderMessage::SENDER_CUSTOMER))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'customer_id' => (int) $customer->id,
            'driver_id' => (int) $order->driver_id,
            'driver_name' => $driverName !== '' ? $driverName : 'Driver',
            'driver_phone' => $driverPhone,
            'driver_code' => $driverCode,
            'driver' => $driver ? [
                'id' => (int) $driver->id,
                'name' => $driverName !== '' ? $driverName : 'Driver',
                'phone' => $driverPhone,
                'driver_code' => $driverCode,
            ] : null,
            'data' => $items,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Customer: send message to driver for an order.
     * POST /api/v1/orders/{id}/messages
     */
    public function customerSend(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $order = $this->resolveCustomerOrder($customer, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!$order->driver_id) {
            return response()->json(['success' => false, 'message' => 'No driver assigned yet for this order.'], 422);
        }

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'driver_id' => (int) $order->driver_id,
            'customer_id' => (int) $customer->id,
            'sender_type' => OrderMessage::SENDER_CUSTOMER,
            'message' => trim((string) $request->input('message')),
            'customer_status' => OrderMessage::CUSTOMER_STATUS_ACTIVE,
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatMessage($message, OrderMessage::SENDER_CUSTOMER),
        ], 201);
    }

    /**
     * Customer: mark driver messages as read in this order thread.
     * POST /api/v1/orders/{id}/messages/read
     */
    public function customerMarkRead(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $order = $this->resolveCustomerOrder($customer, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        OrderMessage::query()
            ->where('order_id', $order->id)
            ->where('sender_type', OrderMessage::SENDER_DRIVER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Marked as read.',
        ]);
    }

    /**
     * Customer: archive all messages in this order thread (soft-delete from customer view).
     * POST /api/v1/orders/{id}/messages/archive
     */
    public function customerArchive(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $order = $this->resolveCustomerOrder($customer, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Update all messages in this thread (order already verified as customer's)
        $updated = OrderMessage::query()
            ->where('order_id', $order->id)
            ->update(['customer_status' => OrderMessage::CUSTOMER_STATUS_ARCHIVE]);

        return response()->json([
            'success' => true,
            'message' => 'Messages archived.',
            'archived_count' => $updated,
        ]);
    }

    /**
     * Customer: archive selected messages (soft-delete from customer view).
     * Messages may belong to the given order or any other order owned by the customer (e.g. merged thread).
     * POST /api/v1/orders/{id}/messages/archive-selected
     */
    public function customerArchiveSelected(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        // Ensure the order in the URL belongs to the customer (validates access)
        $order = $this->resolveCustomerOrder($customer, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $request->validate([
            'message_ids' => ['required', 'array'],
            'message_ids.*' => ['nullable'],
        ]);

        $rawIds = $request->input('message_ids', []);
        $messageIds = array_values(array_unique(array_filter(array_map(function ($v) {
            $id = is_numeric($v) ? (int) $v : null;
            return $id > 0 ? $id : null;
        }, $rawIds))));

        if ($messageIds === []) {
            return response()->json(['success' => false, 'message' => 'No valid message IDs provided.'], 422);
        }

        // Only update messages that belong to an order owned by this customer (supports merged threads)
        $customerOrderIds = Order::query()
            ->where('customer_id', $customer->id)
            ->pluck('id')
            ->all();

        if ($customerOrderIds === []) {
            return response()->json([
                'success' => true,
                'message' => 'Selected messages archived.',
                'archived_count' => 0,
            ]);
        }

        $updated = OrderMessage::query()
            ->whereIn('order_id', $customerOrderIds)
            ->whereIn('id', $messageIds)
            ->update(['customer_status' => OrderMessage::CUSTOMER_STATUS_ARCHIVE]);

        return response()->json([
            'success' => true,
            'message' => 'Selected messages archived.',
            'archived_count' => $updated,
        ]);
    }

    /**
     * Customer: restore archived messages in this order thread.
     * POST /api/v1/orders/{id}/messages/unarchive
     */
    public function customerUnarchive(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $order = $this->resolveCustomerOrder($customer, $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Update all messages in this thread (order already verified as customer's)
        $updated = OrderMessage::query()
            ->where('order_id', $order->id)
            ->update(['customer_status' => OrderMessage::CUSTOMER_STATUS_ACTIVE]);

        return response()->json([
            'success' => true,
            'message' => 'Messages restored.',
            'restored_count' => $updated,
        ]);
    }

    private function resolveDriverOrder(Driver $driver, int $orderId): ?Order
    {
        return Order::query()
            ->with(['driver:id,name,phone,driver_code'])
            ->where('driver_id', $driver->id)
            ->whereKey($orderId)
            ->first();
    }

    private function resolveCustomerOrder(Customer $customer, int $orderId): ?Order
    {
        return Order::query()
            ->with(['driver:id,name,phone,driver_code'])
            ->where('customer_id', $customer->id)
            ->whereKey($orderId)
            ->first();
    }

    private function formatMessage(OrderMessage $message, string $currentSenderType): array
    {
        $payload = [
            'id' => $message->id,
            'order_id' => (int) $message->order_id,
            'driver_id' => (int) $message->driver_id,
            'customer_id' => (int) $message->customer_id,
            'sender_type' => $message->sender_type,
            'message' => $message->message,
            'is_mine' => $message->sender_type === $currentSenderType,
            'created_at' => $message->created_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
        ];
        if (\in_array('customer_status', $message->getFillable(), true)) {
            $payload['customer_status'] = $message->customer_status ?? OrderMessage::CUSTOMER_STATUS_ACTIVE;
        }
        return $payload;
    }
}
