<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Feedback;
use App\Models\Flavor;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Services\FirebaseRealtimeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiOrderController extends Controller
{
    public function __construct(
        protected FirebaseRealtimeService $firebase
    ) {}

    /** Status filter values for order history tabs: all, completed, processing, cancelled */
    private const STATUS_FILTER_ALL = 'all';
    private const STATUS_COMPLETED = ['completed', 'delivered', 'walk-in', 'walk_in', 'walk in', 'walkin'];
    /** Processing: pending, preparing, assigned, ready, out of delivery (and variants). */
    private const STATUS_PROCESSING = [
        'pending',
        'preparing',
        'assigned',
        'ready',
        'out of delivery',
        'out for delivery',
        'out_of_delivery',
    ];
    private const STATUS_CANCELLED = ['cancelled', 'canceled'];
    /** Only orders with this status can be rated (feedback). */
    private const STATUS_RATEABLE = ['completed'];
    /** Only these processing statuses allow cancellation (not ready / out of delivery). */
    private const STATUS_CANCELLABLE = ['pending', 'preparing', 'assigned'];

    /**
     * List orders for the authenticated customer (order history for Flutter).
     * Query: ?status=all|completed|processing|cancelled (default: all).
     * Returns real data with image URL, formatted price, quantity for list/detail.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $query = Order::query()
            ->with(['driver:id,name,phone,driver_code', 'customer:id,contact_no']);
        $this->applyCustomerOwnershipFilter($query, $user);

        $statusFilter = $request->query('status', self::STATUS_FILTER_ALL);
        $statusFilter = strtolower((string) $statusFilter);
        if ($statusFilter === 'completed') {
            $this->applyStatusInFilter($query, self::STATUS_COMPLETED);
        } elseif ($statusFilter === 'processing') {
            $this->applyStatusInFilter($query, self::STATUS_PROCESSING);
        } elseif ($statusFilter === 'cancelled') {
            $this->applyStatusInFilter($query, self::STATUS_CANCELLED);
        }
        // 'all' or any other value: no status filter

        // For Messages > Driver Chats: only orders with driver that have at least one active message (or no messages yet).
        // Archived threads (all messages customer_status = archive) are hidden.
        $forDriverChats = (bool) $request->query('for_driver_chats');
        if ($forDriverChats) {
            $query->whereNotNull('driver_id')->where(function (Builder $q) use ($user) {
                $q->whereDoesntHave('messages')
                    ->orWhereHas('messages', function (Builder $mq) use ($user) {
                        $mq->where('customer_id', $user->id)
                            ->where('customer_status', OrderMessage::CUSTOMER_STATUS_ACTIVE);
                    });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        // When for_driver_chats: attach newest message per order (customer_status = active only) for list preview.
        $latestMessageByOrder = [];
        if ($forDriverChats && $orders->isNotEmpty()) {
            $orderIds = $orders->pluck('id')->all();
            $latestMessages = OrderMessage::query()
                ->whereIn('order_id', $orderIds)
                ->where('customer_id', $user->id)
                ->where('customer_status', OrderMessage::CUSTOMER_STATUS_ACTIVE)
                ->orderByDesc('created_at')
                ->get();
            foreach ($latestMessages as $msg) {
                if (!isset($latestMessageByOrder[$msg->order_id])) {
                    $latestMessageByOrder[$msg->order_id] = $msg;
                }
            }
        }

        $data = $orders->map(fn (Order $order) => $this->formatOrderForApi(
            $order,
            $latestMessageByOrder[$order->id] ?? null
        ));

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Return only fields needed for order display (list/detail) and Flutter order history cards.
     * When $latestOrderMessage is set (e.g. for driver chats), includes latest_message and last_message_at (active only).
     */
    private function formatOrderForApi(Order $order, ?OrderMessage $latestOrderMessage = null): array
    {
        $downpayment = (float) ($order->downpayment ?? 0.0);
        $imagePath = $order->product_image ?? 'img/default-product.png';
        $imageUrl = str_starts_with($imagePath, 'http') ? $imagePath : url($imagePath);
        $amount = (float) $order->amount;
        $balance = (float) ($order->balance ?? max(0, $amount - $downpayment));
        $amountFormatted = '₱' . number_format($amount, 0);
        $driver = $order->driver;
        $driverName = $this->firstNonEmptyString([$driver?->name, 'Driver']);
        $driverPhone = $this->firstNonEmptyString([$driver?->phone, '']);
        $driverCode = $this->firstNonEmptyString([$driver?->driver_code, '']);
        $customerPhone = $this->firstNonEmptyString([
            $order->customer_phone,
            $order->customer?->contact_no,
        ]);

        $payload = [
            'id'                 => $order->id,
            'customer_id'        => $order->customer_id,
            'customer_phone'     => $customerPhone !== '' ? $customerPhone : null,
            'driver_id'          => $order->driver_id ? (int) $order->driver_id : null,
            'driver_name'        => $driverName,
            'assigned_driver_name' => $driverName,
            'driver_phone'       => $driverPhone,
            'driver_code'        => $driverCode,
            'driver'             => $driver ? [
                'id' => (int) $driver->id,
                'name' => $driverName,
                'phone' => $driverPhone,
                'driver_code' => $driverCode,
            ] : null,
            'transaction_id'     => $order->transaction_id,
            'product_name'       => $order->product_name,
            'product_type'       => $order->product_type,
            'gallon_size'        => $order->gallon_size,
            'product_image'      => $order->product_image,
            'product_image_url'  => $imageUrl,
            'delivery_date'      => $order->delivery_date?->format('Y-m-d'),
            'delivery_time'      => $order->delivery_time,
            'delivery_address'   => $order->delivery_address,
            'amount'              => $amount,
            'amount_formatted'    => $amountFormatted,
            'downpayment'        => $downpayment,
            'balance'            => $balance,
            'quantity'           => (int) ($order->qty ?? 1),
            'payment_method'     => $order->payment_method,
            'status'             => $order->status,
            'reason'             => $order->reason,
            'created_at'          => $order->created_at?->toIso8601String(),
            'created_at_formatted' => $order->created_at?->format('M d, Y h:i A'),
        ];

        if ($latestOrderMessage !== null) {
            $payload['latest_message'] = [
                'id' => $latestOrderMessage->id,
                'order_id' => (int) $latestOrderMessage->order_id,
                'sender_type' => $latestOrderMessage->sender_type,
                'message' => $latestOrderMessage->message,
                'created_at' => $latestOrderMessage->created_at?->toIso8601String(),
            ];
            $payload['last_message_at'] = $latestOrderMessage->created_at?->toIso8601String();
            $payload['last_message'] = $latestOrderMessage->message;
        }

        return $payload;
    }

    /**
     * Create order (for Flutter). If same customer + same flavor + same size + same delivery date/time
     * and order is still pending/assigned, quantity is added to existing order instead of creating a duplicate.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'gallon_size' => 'required|string|max:50',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required|string',
            'delivery_address' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'quantity' => 'nullable|integer|min:1',
            'qty' => 'nullable|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'address_first_name' => 'nullable|string|max:100',
            'address_last_name' => 'nullable|string|max:100',
            'address_contact' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $addressFirstName = trim((string) $request->input('address_first_name', $request->input('first_name', '')));
        $addressLastName = trim((string) $request->input('address_last_name', $request->input('last_name', '')));
        $addressContact = trim((string) $request->input('address_contact', $request->input('contact_no', $request->input('phone', ''))));
        $addressName = trim($addressFirstName . ' ' . $addressLastName);
        $customerName = $this->firstNonEmptyString([
            $request->input('customer_name'),
            $addressName,
            $user instanceof Customer ? trim($user->firstname . ' ' . $user->lastname) : null,
            'Guest',
        ]);
        $customerPhone = $this->firstNonEmptyString([
            $request->input('customer_phone'),
            $addressContact,
            $user?->contact_no,
            '',
        ]);
        $customerImage = $request->customer_image ?? 'img/default-user.png';
        $customerId = $user instanceof Customer ? $user->id : null;
        $addQty = max(
            1,
            (int) $request->input('quantity', $request->input('qty', 1))
        );
        $addAmount = (float) $request->amount;
        $downpayment = 0.0;
        $balance = $addAmount;

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? 'img/default-product.png';

        // Find existing pending/assigned order with same customer (by id to avoid duplication), flavor, size, and delivery slot
        $existingQuery = Order::query()
            ->where('product_name', $request->product_name)
            ->where('gallon_size', $request->gallon_size)
            ->where('delivery_date', $request->delivery_date)
            ->where('delivery_time', $request->delivery_time)
            ->where('delivery_address', $request->delivery_address)
            ->where(function ($q) use ($customerId, $customerName, $customerPhone) {
                if ($customerId !== null) {
                    $q->where('customer_id', $customerId);
                    return;
                }
                $q->where('customer_name', $customerName)
                    ->orWhere('customer_phone', $customerPhone);
            });
        $this->applyStatusInFilter($existingQuery, self::STATUS_CANCELLABLE);
        $existing = $existingQuery->first();

        if ($existing) {
            $existing->increment('qty', $addQty);
            $newAmount = $existing->amount + $addAmount;
            $existingDownpayment = (float) ($existing->downpayment ?? 0.0);
            $existing->update([
                'amount' => $newAmount,
                'balance' => max(0, $newAmount - $existingDownpayment),
            ]);
            $order = $existing->fresh();
            $this->firebase->touchOrdersUpdated();

            return response()->json(['success' => true, 'data' => $this->formatOrderForApi($order), 'merged' => true], 200);
        }

        $order = Order::create([
            'customer_id' => $customerId,
            'transaction_id' => strtoupper(Str::random(10)),
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'gallon_size' => $request->gallon_size,
            'product_image' => $productImage,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_image' => $customerImage,
            'delivery_date' => $request->delivery_date,
            'delivery_time' => $request->delivery_time,
            'delivery_address' => $request->delivery_address,
            'amount' => $request->amount,
            'downpayment' => $downpayment,
            'balance' => $balance,
            'qty' => $addQty,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);

        // Notify customer when order is placed (if authenticated)
        if ($user instanceof Customer) {
            CustomerNotification::create([
                'customer_id'   => $user->id,
                'type'          => CustomerNotification::TYPE_ORDER_PLACED,
                'title'         => $order->product_name,
                'message'       => 'Your order has been placed successfully.',
                'image_url'     => $productImage,
                'related_type'  => 'Order',
                'related_id'    => $order->id,
                'data'          => ['transaction_id' => $order->transaction_id],
            ]);
        }

        // Notify all admins: "CustomerName Order #TransactionNo ProductName"
        AdminNotification::createForAllAdmins(
            AdminNotification::TYPE_ORDER_NEW,
            $customerName,
            null,
            $productImage,
            'Order',
            $order->id,
            ['subtitle' => 'Order #' . $order->transaction_id, 'highlight' => $order->product_name]
        );

        $this->firebase->touchOrdersUpdated();

        return response()->json(['success' => true, 'data' => $this->formatOrderForApi($order)], 201);
    }

    /**
     * Single order for authenticated customer (only their orders, slim response).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }
        $orderQuery = Order::with(['driver:id,name,phone,driver_code', 'customer:id,contact_no'])
            ->where('id', $id);
        $this->applyCustomerOwnershipFilter($orderQuery, $user);
        $order = $orderQuery->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $this->formatOrderForApi($order)]);
    }

    /**
     * Cancel an order (only pending or assigned). For Flutter "Cancel" button in Processing tab.
     * PATCH /api/v1/orders/{id}/cancel
     * Body (optional): { "reason": "Changed my mind" } or { "reason": "Others (Please specify)", "reason_detail": "..." }
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
            'reason_detail' => 'nullable|string|max:1000',
        ]);

        $orderQuery = Order::where('id', $id);
        $this->applyCustomerOwnershipFilter($orderQuery, $user);
        $order = $orderQuery->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!in_array($this->normalizeStatus((string) $order->status), self::STATUS_CANCELLABLE, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or assigned orders can be cancelled.',
            ], 422);
        }

        $reason = $request->input('reason');
        $detail = $request->input('reason_detail');
        $reasonText = $reason;
        if ($detail !== null && $detail !== '') {
            $reasonText = ($reason ? $reason . ': ' : '') . $detail;
        }

        $order->update([
            'status' => 'cancelled',
            'reason' => $reasonText,
        ]);

        $this->firebase->touchOrdersUpdated();

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data'    => $this->formatOrderForApi($order->fresh()),
        ]);
    }


    public function feedback(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'message' => 'nullable|string|max:2000',
        ]);

        $orderQuery = Order::where('id', $id);
        $this->applyCustomerOwnershipFilter($orderQuery, $user);
        $order = $orderQuery->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!in_array($this->normalizeStatus((string) $order->status), self::STATUS_RATEABLE, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only rate orders with status Completed.',
            ], 422);
        }

        $flavor = Flavor::where('name', $order->product_name)->first();
        $customerName = $user->firstname . ' ' . $user->lastname;
        $photo = $user->image ?? null;
        $testimonial = $request->input('message', '');
        $rating = (int) $request->input('rating');

        $feedback = Feedback::where('order_id', $id)->first();
        if ($feedback) {
            $feedback->update([
                'flavor_id'     => $flavor?->id,
                'rating'        => $rating,
                'testimonial'   => $testimonial,
                'customer_name' => $customerName,
                'photo'         => $photo,
                'feedback_date' => now(),
            ]);
        } else {
            $feedback = Feedback::create([
                'flavor_id'     => $flavor?->id,
                'order_id'      => $order->id,
                'customer_name' => $customerName,
                'photo'         => $photo,
                'rating'        => $rating,
                'testimonial'   => $testimonial,
                'feedback_date' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'data'    => [
                'id'           => $feedback->id,
                'order_id'     => $order->id,
                'rating'       => $feedback->rating,
                'message'      => $feedback->testimonial,
                'feedback_date' => $feedback->feedback_date?->toIso8601String(),
            ],
        ], $feedback->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Restrict orders to those owned by the customer. Uses customer_id only to avoid
     * duplication of messages/orders when matching by name (same name, different accounts).
     */
    private function applyCustomerOwnershipFilter(Builder $query, Customer $customer): void
    {
        $query->where('customer_id', $customer->id);
    }

    private function applyStatusInFilter(Builder $query, array $statuses): void
    {
        if (count($statuses) === 0) {
            return;
        }

        $normalized = array_values(array_unique(array_map(
            fn (string $status) => $this->normalizeStatus($status),
            $statuses
        )));

        $placeholders = implode(',', array_fill(0, count($normalized), '?'));
        $query->whereRaw('LOWER(TRIM(COALESCE(status, ""))) IN (' . $placeholders . ')', $normalized);
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower(trim($status));
    }

    /**
     * Return the first non-empty trimmed string from a list.
     *
     * @param array<int, mixed> $values
     */
    private function firstNonEmptyString(array $values): string
    {
        foreach ($values as $value) {
            $text = trim((string) $value);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }
}
