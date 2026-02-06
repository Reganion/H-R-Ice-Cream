<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Driver;
use App\Models\Flavor;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminOrderController extends Controller
{
    /**
     * Return orders as JSON for real-time polling on the admin orders page.
     */
    public function listJson(Request $request)
    {
        $orders = Order::orderBy('created_at', 'desc')->get();

        $data = $orders->map(function (Order $order) {
            $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
            $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
            $createdAt = $order->created_at ? Carbon::parse($order->created_at) : null;

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
                'delivery_address' => $order->delivery_address ?? '',
                'amount' => (float) $order->amount,
                'payment_method' => $order->payment_method ?? '',
                'status' => $order->status ?? '',
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
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
        $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
        $createdAt = $order->created_at ? Carbon::parse($order->created_at) : null;
        $driver = $order->driver;

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
            'delivery_address' => $order->delivery_address ?? '',
            'amount' => (float) $order->amount,
            'payment_method' => $order->payment_method ?? '',
            'status' => $order->status ?? '',
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
            'status'           => 'walk_in',
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

        $oldStatus = $order->status;
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
            'payment_method'   => $request->payment_method,
        ];
        if ($request->has('status')) {
            $updates['status'] = $request->input('status');
        }
        $order->update($updates);

        // Notify all admins when order is marked as delivered
        if ($order->wasChanged('status') && $order->status === 'delivered') {
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

        return back()->with('success', 'Walk-in order updated successfully.');
    }

    /**
     * Return available (non-deactivated) drivers as JSON for assign modal.
     */
    public function availableDriversJson()
    {
        $drivers = Driver::where('status', '!=', Driver::STATUS_DEACTIVATE)
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

        if (in_array($order->status, ['delivered', 'walk_in'], true)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This order cannot be assigned a driver.'], 422);
            }
            return back()->with('error', 'This order cannot be assigned a driver.');
        }

        $order->update([
            'driver_id' => $request->driver_id,
            'status' => 'assigned',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Driver assigned successfully.']);
        }
        return back()->with('success', 'Driver assigned successfully.');
    }
}
