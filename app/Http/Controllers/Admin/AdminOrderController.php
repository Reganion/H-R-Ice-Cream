<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Driver;
use App\Models\Flavor;
use App\Models\Gallon;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminOrderController extends Controller
{
    private function toDatabaseOrderStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) ($status ?? '')));
        $normalized = str_replace('_', '-', preg_replace('/\s+/', '-', $normalized));

        return match ($normalized) {
            '', 'new', 'new-order', 'new_order', 'pending' => 'pending',
            'walk-in', 'walkin' => 'Walk-in',
            'preparing' => 'Preparing',
            'assigned' => 'Assigned',
            'completed', 'delivered' => 'Completed',
            'cancelled', 'canceled' => 'Cancelled',
            'ready' => 'Ready',
            'out-for-delivery', 'out_for_delivery', 'out for delivery' => 'Out for Delivery',
            default => 'pending',
        };
    }

    private function normalizeOrderStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) ($status ?? '')));
        $normalized = str_replace('_', '-', preg_replace('/\s+/', '-', $normalized));

        if ($normalized === '' || $normalized === 'new-order' || $normalized === 'new') {
            return 'pending';
        }

        if ($normalized === 'walk-in' || $normalized === 'walkin') {
            return 'walk_in';
        }

        if ($normalized === 'preparing') {
            return 'preparing';
        }

        if ($normalized === 'delivered' || $normalized === 'completed') {
            return 'completed';
        }

        if ($normalized === 'assigned') {
            return 'assigned';
        }

        if ($normalized === 'cancelled' || $normalized === 'canceled') {
            return 'cancelled';
        }

        if ($normalized === 'ready') {
            return 'ready';
        }

        if ($normalized === 'out-for-delivery' || $normalized === 'out_for_delivery') {
            return 'out_for_delivery';
        }

        return $normalized;
    }

    /**
     * Return orders as JSON for real-time polling on the admin orders page.
     * When scope=this_month, only returns orders from the start of the current month.
     */
    public function listJson(Request $request)
    {
        $query = Order::query()
            ->with(['driver', 'customer']);

        if ($request->get('scope') === 'this_month') {
            $query->where('created_at', '>=', Carbon::now()->startOfMonth());
        }

        if ($request->get('scope') === 'records') {
            $query->whereRaw("LOWER(TRIM(status)) IN ('completed', 'delivered', 'cancelled')");
        }

        $orders = $query
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) IN ('pending', 'new_order') THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) IN ('walk_in', 'walk-in', 'walk in', 'walkin') THEN 3
                    WHEN LOWER(TRIM(status)) = 'assigned' THEN 4
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 5
                    WHEN LOWER(TRIM(status)) IN ('out for delivery', 'out_for_delivery') THEN 6
                    WHEN LOWER(TRIM(status)) IN ('completed', 'delivered') THEN 7
                    WHEN LOWER(TRIM(status)) = 'cancelled' THEN 8
                    ELSE 9
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $orders->map(function (Order $order) {
            $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
            $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
            $createdAt = $order->created_at ? Carbon::parse($order->created_at) : null;
            $status = $this->normalizeOrderStatus($order->status);
            $downpayment = (float) ($order->downpayment ?? 0.0);
            $amount = (float) $order->amount;
            $balance = (float) ($order->balance ?? max(0, $amount - $downpayment));

            $driver = $order->driver;
            return [
                'id' => $order->id,
                'transaction_id' => $order->transaction_id,
                'product_name' => $order->product_name ?? '',
                'product_type' => $order->product_type ?? '',
                'gallon_size' => $order->gallon_size ?? '',
                'product_image_url' => asset($order->product_image ?? 'img/default-product.png'),
                'customer_name' => $order->customer_name ?? '',
                'customer_phone' => $order->customer_phone ?? '',
                'customer_image_url' => asset($order->customer_image ?? 'img/default-user.png'),
                'customer_email' => $order->customer?->email,
                'delivery_address' => $order->delivery_address ?? '',
                'amount' => $amount,
                'downpayment' => $downpayment,
                'balance' => $balance,
                'quantity' => (int) ($order->qty ?? 1),
                'payment_method' => $order->payment_method ?? '',
                'status' => $status,
                'driver_id' => $order->driver_id,
                'driver_name' => $driver ? $driver->name : null,
                'driver_phone' => $driver ? $driver->phone : null,
                'driver_image_url' => $driver ? asset($driver->image ?? 'img/default-user.png') : null,
                'created_at_formatted' => $createdAt ? $createdAt->format('d M Y') : '—',
                'delivery_date' => $deliveryDate ? $deliveryDate->format('Y-m-d') : '',
                'delivery_time' => $deliveryTime ? $deliveryTime->format('H:i') : '',
                'delivery_date_formatted' => $deliveryDate ? $deliveryDate->format('d M') : '',
                'delivery_time_formatted' => $deliveryTime ? $deliveryTime->format('h:i A') : '',
            ];
        });

        return response()->json(['orders' => $data]);
    }

    /**
     * Return a single order as JSON (for notification order-details modal).
     */
    public function showJson(string $id)
    {
        $order = Order::with(['driver', 'customer'])->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
        $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
        $createdAt = $order->created_at ? Carbon::parse($order->created_at) : null;
        $driver = $order->driver;
        $status = $this->normalizeOrderStatus($order->status);
        $downpayment = (float) ($order->downpayment ?? 0.0);
        $amount = (float) $order->amount;
        $balance = (float) ($order->balance ?? max(0, $amount - $downpayment));

        $data = [
            'id' => $order->id,
            'transaction_id' => $order->transaction_id,
            'product_name' => $order->product_name ?? '',
            'product_type' => $order->product_type ?? '',
            'gallon_size' => $order->gallon_size ?? '',
            'product_image_url' => asset($order->product_image ?? 'img/default-product.png'),
            'customer_name' => $order->customer_name ?? '',
            'customer_phone' => $order->customer_phone ?? '',
            'customer_image_url' => asset($order->customer_image ?? 'img/default-user.png'),
            'customer_email' => $order->customer?->email,
            'delivery_address' => $order->delivery_address ?? '',
            'amount' => $amount,
            'downpayment' => $downpayment,
            'balance' => $balance,
            'quantity' => (int) ($order->qty ?? 1),
            'payment_method' => $order->payment_method ?? '',
            'status' => $status,
            'driver_id' => $order->driver_id,
            'driver_name' => $driver ? $driver->name : null,
            'driver_phone' => $driver ? $driver->phone : null,
            'driver_image_url' => $driver ? asset($driver->image ?? 'img/default-user.png') : null,
            'created_at_formatted' => $createdAt ? $createdAt->format('d M Y') : '—',
            'delivery_date' => $deliveryDate ? $deliveryDate->format('Y-m-d') : '',
            'delivery_time' => $deliveryTime ? $deliveryTime->format('H:i') : '',
            'delivery_date_formatted' => $deliveryDate ? $deliveryDate->format('d M Y') : '',
            'delivery_time_formatted' => $deliveryTime ? $deliveryTime->format('h:i A') : '',
        ];

        return response()->json(['order' => $data]);
    }

    public function storeWalkIn(Request $request)
    {
        $request->validate([
            'product_name'     => 'required|string|max:255',
            'product_type'     => 'required|string|max:255',
            'gallon_size'      => 'required|string|max:50',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'required',
            'delivery_address' => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
            'qty'              => 'nullable|integer|min:1',
        ]);

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? 'img/default-product.png';

        $order = Order::create([
            'transaction_id'   => strtoupper(Str::random(10)),
            'product_name'     => $request->product_name,
            'product_type'     => $request->product_type,
            'gallon_size'      => $request->gallon_size,
            'product_image'    => $productImage,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'customer_image'   => 'img/default-user.png',
            'delivery_date'    => $request->delivery_date,
            'delivery_time'    => $request->delivery_time,
            'delivery_address' => $request->delivery_address,
            'amount'           => $request->amount,
            'qty'              => (int) $request->input('quantity', 1),
            'payment_method'   => $request->payment_method,
            'status'           => $this->toDatabaseOrderStatus('walk_in'),
        ]);

        // Notify all admins: "CustomerName Order #TransactionNo ProductName"
        AdminNotification::createForAllAdmins(
            AdminNotification::TYPE_ORDER_NEW,
            $order->customer_name,
            null,
            $productImage,
            'Order',
            $order->id,
            ['subtitle' => 'Order #' . $order->transaction_id, 'highlight' => $order->product_name]
        );

        return back()->with('success', 'Walk-in order added successfully.');
    }

    public function updateWalkIn(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $currentStatus = $this->normalizeOrderStatus($order->status);

        // For pending/assigned/preparing orders, only allow flavor, quantity, and status edits.
        if (in_array($currentStatus, ['pending', 'assigned', 'preparing'], true)) {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'gallon_size' => 'required|string|max:50',
                'qty' => 'required|integer|min:1',
                'status' => 'required|string',
            ]);

            $requestedStatus = $this->normalizeOrderStatus($request->input('status'));
            $allowedNext = match ($currentStatus) {
                'pending', 'assigned' => ['preparing', $currentStatus],
                'preparing' => ['preparing', 'ready'],
                default => [$currentStatus],
            };
            if (!in_array($requestedStatus, $allowedNext, true)) {
                return back()->withErrors([
                    'status' => $currentStatus === 'preparing'
                        ? 'For this order, status can only be Preparing or Ready.'
                        : 'For this order, status can only be the previous status or Preparing.',
                ])->withInput();
            }

            $flavor = Flavor::where('name', $request->product_name)->first();
            $gallon = Gallon::where('size', $request->gallon_size)->first();
            $newType = $request->input('product_type');
            if ($newType === null || trim((string) $newType) === '') {
                $newType = $flavor?->category ?? $order->product_type;
            }
            $qty = (int) $request->input('qty', 1);
            $unitPrice = (float) (($flavor?->price ?? 0) + ($gallon?->addon_price ?? 0));
            $totalAmount = round($unitPrice * max($qty, 1), 2);

            $order->update([
                'product_name' => $request->product_name,
                'product_type' => $newType,
                'gallon_size' => $request->gallon_size,
                'product_image' => $flavor?->image ?? $order->product_image ?? 'img/default-product.png',
                'qty' => $qty,
                'amount' => $totalAmount,
                'status' => $this->toDatabaseOrderStatus($requestedStatus),
            ]);

            if ($order->wasChanged('status') && $this->normalizeOrderStatus($order->status) === 'completed') {
                AdminNotification::createForAllAdmins(
                    AdminNotification::TYPE_DELIVERY_SUCCESS,
                    $order->product_name,
                    null,
                    null,
                    'Order',
                    $order->id,
                    ['subtitle' => 'delivered', 'highlight' => 'Successfully']
                );
            }

            return back()->with('success', 'Order updated successfully.');
        }

        $request->validate([
            'product_name'     => 'required|string|max:255',
            'product_type'     => 'required|string|max:255',
            'gallon_size'      => 'required|string|max:50',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'required',
            'delivery_address' => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
        ]);

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? $order->product_image ?? 'img/default-product.png';

        $updates = [
            'product_name'     => $request->product_name,
            'product_type'     => $request->product_type,
            'gallon_size'      => $request->gallon_size,
            'product_image'    => $productImage,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'delivery_date'    => $request->delivery_date,
            'delivery_time'    => $request->delivery_time,
            'delivery_address' => $request->delivery_address,
            'amount'           => $request->amount,
            'qty'              => (int) $request->input('qty', $order->qty ?? 1),
            'payment_method'   => $request->payment_method,
        ];
        if ($request->has('status')) {
            $updates['status'] = $this->toDatabaseOrderStatus($request->input('status'));
        }
        $order->update($updates);

        // Notify all admins when order is marked as completed/delivered.
        if ($order->wasChanged('status') && $this->normalizeOrderStatus($order->status) === 'completed') {
            AdminNotification::createForAllAdmins(
                AdminNotification::TYPE_DELIVERY_SUCCESS,
                $order->product_name,
                null,
                null,
                'Order',
                $order->id,
                ['subtitle' => 'delivered', 'highlight' => 'Successfully']
            );
        }

        return back()->with('success', 'Order updated successfully.');
    }

    /**
     * Return only available drivers as JSON for assign modal.
     */
    public function availableDriversJson()
    {
        $drivers = Driver::where('status', Driver::STATUS_AVAILABLE)
            ->orderBy('name')
            ->get()
            ->map(function (Driver $d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'phone' => $d->phone ?? '',
                    'image_url' => asset($d->image ?? 'img/default-user.png'),
                ];
            });

        return response()->json(['drivers' => $drivers]);
    }

    /**
     * Assign or reassign a driver to an order. Expects JSON: { "driver_id": 1 }.
     */
    public function assignDriver(Request $request, string $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ]);

        $order = Order::findOrFail($id);
        $status = $this->normalizeOrderStatus($order->status);

        if ($status === 'completed') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This order cannot be assigned a driver.'], 422);
            }
            return back()->with('error', 'This order cannot be assigned a driver.');
        }

        $order->update([
            'driver_id' => $request->driver_id,
            'status' => $this->toDatabaseOrderStatus('assigned'),
            'status_driver' => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Driver assigned successfully.']);
        }
        return back()->with('success', 'Driver assigned successfully.');
    }
}
