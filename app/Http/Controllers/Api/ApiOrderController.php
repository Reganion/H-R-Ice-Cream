<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Feedback;
use App\Models\Flavor;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiOrderController extends Controller
{
    /** Status filter values for order history tabs: all, completed, processing, cancelled */
    private const STATUS_FILTER_ALL = 'all';
    private const STATUS_COMPLETED = ['delivered', 'walk_in'];
    private const STATUS_PROCESSING = ['pending', 'preparing', 'assigned'];
    private const STATUS_CANCELLED = ['cancelled'];

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

        $query = Order::where(function ($q) use ($user) {
            $q->where('customer_name', $user->firstname . ' ' . $user->lastname)
                ->orWhere('customer_phone', $user->contact_no);
        });

        $statusFilter = $request->query('status', self::STATUS_FILTER_ALL);
        $statusFilter = strtolower((string) $statusFilter);
        if ($statusFilter === 'completed') {
            $query->whereIn('status', self::STATUS_COMPLETED);
        } elseif ($statusFilter === 'processing') {
            $query->whereIn('status', self::STATUS_PROCESSING);
        } elseif ($statusFilter === 'cancelled') {
            $query->whereIn('status', self::STATUS_CANCELLED);
        }
        // 'all' or any other value: no status filter

        $orders = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Order $order) => $this->formatOrderForApi($order));

        return response()->json(['success' => true, 'data' => $orders]);
    }

    /**
     * Return only fields needed for order display (list/detail) and Flutter order history cards.
     */
    private function formatOrderForApi(Order $order): array
    {
        $imagePath = $order->product_image ?? 'img/default-product.png';
        $imageUrl = str_starts_with($imagePath, 'http') ? $imagePath : url($imagePath);
        $amount = (float) $order->amount;
        $amountFormatted = '₱' . number_format($amount, 0);

        return [
            'id'                 => $order->id,
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
            'quantity'           => (int) ($order->qty ?? 1),
            'payment_method'     => $order->payment_method,
            'status'             => $order->status,
            'reason'             => $order->reason,
            'created_at'          => $order->created_at?->toIso8601String(),
            'created_at_formatted' => $order->created_at?->format('M d, Y h:i A'),
        ];
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
        ]);

        $user = $request->user();
        $customerName = $request->customer_name ?? ($user ? $user->firstname . ' ' . $user->lastname : 'Guest');
        $customerPhone = $request->customer_phone ?? ($user?->contact_no ?? '');
        $customerImage = $request->customer_image ?? 'img/default-user.png';
        $addQty = (int) $request->input('quantity', 1);
        $addAmount = (float) $request->amount;

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? 'img/default-product.png';

        // Find existing pending/assigned order with same customer, flavor, size, and delivery slot
        $existing = Order::whereIn('status', self::STATUS_PROCESSING)
            ->where('product_name', $request->product_name)
            ->where('gallon_size', $request->gallon_size)
            ->where('delivery_date', $request->delivery_date)
            ->where('delivery_time', $request->delivery_time)
            ->where('delivery_address', $request->delivery_address)
            ->where(function ($q) use ($customerName, $customerPhone) {
                $q->where('customer_name', $customerName)
                    ->orWhere('customer_phone', $customerPhone);
            })
            ->first();

        if ($existing) {
            $existing->increment('qty', $addQty);
            $existing->update(['amount' => $existing->amount + $addAmount]);
            $order = $existing->fresh();

            return response()->json(['success' => true, 'data' => $this->formatOrderForApi($order), 'merged' => true], 200);
        }

        $order = Order::create([
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
        $order = Order::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('customer_name', $user->firstname . ' ' . $user->lastname)
                    ->orWhere('customer_phone', $user->contact_no);
            })
            ->first();
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

        $order = Order::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('customer_name', $user->firstname . ' ' . $user->lastname)
                    ->orWhere('customer_phone', $user->contact_no);
            })
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!in_array($order->status, self::STATUS_PROCESSING, true)) {
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

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data'    => $this->formatOrderForApi($order->fresh()),
        ]);
    }

    /**
     * Submit rating/feedback for a completed order (Flutter "Rate your order" → Continue).
     * POST /api/v1/orders/{id}/feedback
     * Body: { "rating": 1-5, "message": "optional testimonial" }
     * Only delivered/walk_in orders; one feedback per order (re-submit updates existing).
     */
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

        $order = Order::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('customer_name', $user->firstname . ' ' . $user->lastname)
                    ->orWhere('customer_phone', $user->contact_no);
            })
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!in_array($order->status, self::STATUS_COMPLETED, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only rate delivered or completed orders.',
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
}
