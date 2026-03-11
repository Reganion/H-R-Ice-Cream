<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DeliveryService;


class ApiDriverShipmentController extends Controller
{
    // please make it a habit to do dependency injection for services 
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }
    // please make it a habit to do dependency injection for services 


    /**
     * Driver shipments list by tab.
     * GET /api/v1/driver/shipments?tab=incoming|accepted|completed&search=...
     */
    public function index(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $tab = strtolower((string) $request->query('tab', 'incoming'));
        if (!in_array($tab, ['incoming', 'accepted', 'completed'], true)) {
            $tab = 'incoming';
        }

        $statuses = $this->statusesForTab($tab);

        $query = Order::query()
            ->where('driver_id', $driver->id)
            ->whereRaw(
                'LOWER(status) IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')',
                $statuses
            )
            ->orderByRaw('COALESCE(delivery_date, created_at) DESC')
            ->orderBy('id', 'desc');

        if ($tab === 'incoming') {
            $query->where(function ($q): void {
                $q->whereNull('status_driver')
                    ->orWhereRaw('TRIM(status_driver) = ? ', ['']);
            });
        } elseif ($tab === 'accepted') {
            $query->whereRaw('LOWER(COALESCE(status_driver, "")) IN (?, ?)', ['accepted', 'on_route']);
        } else {
            $query->whereRaw('LOWER(COALESCE(status_driver, "")) = ?', ['completed']);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('product_name', 'like', '%' . $search . '%')
                    ->orWhere('delivery_address', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%');
            });
        }

        $shipments = $query->get()->map(function (Order $order) use ($tab) {
            $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
            $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
            $schedule = $this->formatSchedule($deliveryDate, $deliveryTime);
            $proofUrl = $order->delivery_proof_image
                ? asset('storage/' . ltrim((string) $order->delivery_proof_image, '/'))
                : null;
            $deliveredTime = $order->delivered_at ? Carbon::parse($order->delivered_at)->format('h:i A') : null;
            $deliveredDate = $order->delivery_date ? Carbon::parse($order->delivery_date)->format('d F Y') : null;
            $deliveredTimeCompact = $order->delivered_at ? strtoupper(Carbon::parse($order->delivered_at)->format('h:ia')) : null;

            return [
                'id' => $order->id,
                'transaction_id' => (string) ($order->transaction_id ?? ''),
                'transaction_label' => '#' . (string) ($order->transaction_id ?? ''),
                'product_name' => (string) ($order->product_name ?? '—'),
                'amount' => (float) ($order->amount ?? 0),
                'amount_text' => 'PHP ' . number_format((float) ($order->amount ?? 0), 2),
                'expected_on' => $schedule,
                'location' => (string) ($order->delivery_address ?? '—'),
                'status' => strtolower((string) ($order->status ?? '')),
                'status_driver' => strtolower((string) ($order->status_driver ?? '')),
                'received_amount' => $order->received_amount !== null ? (float) $order->received_amount : null,
                'delivery_payment_method' => (string) ($order->delivery_payment_method ?? ''),
                'delivery_proof_url' => $proofUrl,
                'delivery_proof_image' => (string) ($order->delivery_proof_image ?? ''),
                'proof_image_url' => $proofUrl,
                'proof_image' => (string) ($order->delivery_proof_image ?? ''),
                'expected_time' => $deliveryTime ? $deliveryTime->format('h:i A') : null,
                'time' => $deliveryTime ? $deliveryTime->format('h:i A') : null,
                'delivery_time_compact' => $this->formatTimeCompact($order->delivery_time),
                'delivered_time' => $deliveredTime,
                'delivered_time_compact' => $deliveredTimeCompact,
                'delivered_date' => $deliveredDate,
                'badge' => $this->badgeForTab($tab),
                'badge_color' => $this->badgeColorForTab($tab),
                'customer_name' => (string) ($order->customer_name ?? ''),
                'customer_phone' => (string) ($order->customer_phone ?? ''),
                'delivery_date' => $deliveryDate ? $deliveryDate->format('Y-m-d') : null,
                'delivery_time' => $deliveryTime ? $deliveryTime->format('H:i') : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'tab' => $tab,
            'count' => $shipments->count(),
            'shipments' => $shipments,
        ]);
    }

    /**
     * Driver shipment details.
     * GET /api/v1/driver/shipments/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = Order::query()
            ->where('driver_id', $driver->id)
            ->whereKey($id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
        $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
        $proofUrl = $order->delivery_proof_image
            ? asset('storage/' . ltrim((string) $order->delivery_proof_image, '/'))
            : null;
        $deliveredTime = $order->delivered_at ? Carbon::parse($order->delivered_at)->format('h:i A') : null;
        $deliveredDate = $order->delivery_date ? Carbon::parse($order->delivery_date)->format('d F Y') : null;
        $deliveredTimeCompact = $order->delivered_at ? strtoupper(Carbon::parse($order->delivered_at)->format('h:ia')) : null;

        return response()->json([
            'success' => true,
            'shipment' => [
                'id' => $order->id,
                'transaction_id' => (string) ($order->transaction_id ?? ''),
                'transaction_label' => '#' . (string) ($order->transaction_id ?? ''),
                'expected_on' => $this->formatSchedule($deliveryDate, $deliveryTime),
                'customer_name' => (string) ($order->customer_name ?? ''),
                'customer_phone' => (string) ($order->customer_phone ?? ''),
                'delivery_address' => (string) ($order->delivery_address ?? '—'),
                'quantity' => (int) ($order->qty ?? 1),
                'size' => (string) ($order->gallon_size ?? ''),
                'order_name' => (string) ($order->product_name ?? ''),
                'order_type' => (string) ($order->product_type ?? ''),
                'cost' => (float) ($order->amount ?? 0),
                'cost_text' => 'PHP ' . number_format((float) ($order->amount ?? 0), 2),
                'status' => strtolower((string) ($order->status ?? '')),
                'status_driver' => strtolower((string) ($order->status_driver ?? '')),
                'received_amount' => $order->received_amount !== null ? (float) $order->received_amount : null,
                'delivery_payment_method' => (string) ($order->delivery_payment_method ?? ''),
                'delivery_proof_url' => $proofUrl,
                'delivery_proof_image' => (string) ($order->delivery_proof_image ?? ''),
                'proof_image_url' => $proofUrl,
                'proof_image' => (string) ($order->delivery_proof_image ?? ''),
                'expected_time' => $deliveryTime ? $deliveryTime->format('h:i A') : null,
                'time' => $deliveryTime ? $deliveryTime->format('h:i A') : null,
                'delivery_time_compact' => $this->formatTimeCompact($order->delivery_time),
                'delivered_time' => $deliveredTime,
                'delivered_time_compact' => $deliveredTimeCompact,
                'delivered_date' => $deliveredDate,
            ],
        ]);
    }

    /**
     * Driver accepts a shipment.
     * Keep order status as assigned; set status_driver=accepted.
     */
    public function accept(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = Order::query()
            ->where('driver_id', $driver->id)
            ->whereKey($id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        if (strtolower((string) ($order->status ?? '')) !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => 'Only assigned shipments can be accepted.',
            ], 422);
        }

        $order->status_driver = 'accepted';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Shipment accepted.',
        ]);
    }

    /**
     * Driver rejects shipment.
     * Set order back to pending and unassign driver.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = Order::query()
            ->where('driver_id', $driver->id)
            ->whereKey($id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $order->update([
            'status' => 'pending',
            'driver_id' => null,
            'status_driver' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipment rejected and moved back to pending.',
        ]);
    }

    /**
     * Driver starts the route for an accepted shipment.
     * Keep order status as assigned and keep status_driver as accepted.
     */
    public function deliver(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = Order::query()
            ->where('driver_id', $driver->id)
            ->whereKey($id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        if (strtolower((string) ($order->status ?? '')) !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => 'Only assigned shipments can be started for delivery.',
            ], 422);
        }

        $currentDriverStatus = strtolower((string) ($order->status_driver ?? ''));
        if ($currentDriverStatus !== 'accepted' && $currentDriverStatus !== 'on_route') {
            return response()->json([
                'success' => false,
                'message' => 'Shipment must be accepted before starting delivery.',
            ], 422);
        }


        $driver->status = Driver::STATUS_ON_ROUTE;
        $driver->save();

        $order->status_driver = "on_route";
        $order->save();

        $coords = $this->deliveryService->geocodeAddress($order->delivery_address);

        return response()->json([
            'success' => true,
            'message' => 'Driver is now on route.',
            'destrination' => $coords
        ]);
    }

    /**
     * Driver completes delivery after collecting amount and proof image.
     * Set order status to completed and status_driver=completed.
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $order = Order::query()
            ->where('driver_id', $driver->id)
            ->whereKey($id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $orderStatus = strtolower((string) ($order->status ?? ''));
        if ($orderStatus !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => 'Only assigned shipments can be submitted.',
            ], 422);
        }

        $driverShipmentStatus = strtolower((string) ($order->status_driver ?? ''));
        if (!in_array($driverShipmentStatus, ['accepted', 'on_route'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment must be accepted before completion.',
            ], 422);
        }

        $validated = $request->validate([
            'received_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'proof_photo' => ['required', 'image', 'max:5120'],
        ]);

        $proofPath = $request->file('proof_photo')->store('delivery-proofs', 'public');

        $order->update([
            'status' => 'completed',
            'status_driver' => 'completed',
            'received_amount' => (float) $validated['received_amount'],
            'delivery_payment_method' => (string) ($validated['payment_method'] ?? ''),
            'delivery_proof_image' => $proofPath,
            'delivered_at' => now(),
        ]);

        $hasActiveRoute = Order::query()
            ->where('driver_id', $driver->id)
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['assigned'])
            ->whereIn('status_driver', ['accepted', 'on_route'])
            ->exists();

        if (!$hasActiveRoute) {
            $driver->status = Driver::STATUS_AVAILABLE;
            $driver->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipment marked completed.',
            'shipment' => [
                'id' => $order->id,
                'status' => strtolower((string) $order->status),
                'status_driver' => strtolower((string) $order->status_driver),
                'received_amount' => (float) $order->received_amount,
                'delivery_payment_method' => (string) ($order->delivery_payment_method ?? ''),
                'delivery_proof_url' => asset('storage/' . ltrim((string) $order->delivery_proof_image, '/')),
                'delivery_proof_image' => (string) ($order->delivery_proof_image ?? ''),
                'proof_image_url' => asset('storage/' . ltrim((string) $order->delivery_proof_image, '/')),
                'proof_image' => (string) ($order->delivery_proof_image ?? ''),
                'delivery_time_compact' => $this->formatTimeCompact($order->delivery_time),
                'delivered_time' => Carbon::parse($order->delivered_at)->format('h:i A'),
                'delivered_time_compact' => strtoupper(Carbon::parse($order->delivered_at)->format('h:ia')),
                'delivered_date' => $order->delivery_date ? Carbon::parse($order->delivery_date)->format('d F Y') : null,
            ],
        ]);
    }

    private function statusesForTab(string $tab): array
    {
        return match ($tab) {
            'accepted' => ['assigned'],
            'completed' => ['completed'],
            default => ['assigned'],
        };
    }

    private function formatSchedule(?Carbon $deliveryDate, ?Carbon $deliveryTime): string
    {
        if (!$deliveryDate && !$deliveryTime) {
            return '—';
        }

        $datePart = $deliveryDate ? $deliveryDate->format('d M') : null;
        $timePart = $deliveryTime ? $deliveryTime->format('h:i A') : null;

        if ($datePart && $timePart) {
            return $datePart . ', ' . $timePart;
        }

        return (string) ($datePart ?? $timePart ?? '—');
    }

    private function badgeForTab(string $tab): string
    {
        return match ($tab) {
            'accepted' => 'Pending',
            'completed' => 'Completed',
            default => 'New',
        };
    }

    private function badgeColorForTab(string $tab): string
    {
        return match ($tab) {
            'accepted' => '#FF6805',
            'completed' => '#00AE2A',
            default => '#007CFF',
        };
    }

    private function formatTimeCompact(?string $time): ?string
    {
        $value = trim((string) ($time ?? ''));
        if ($value === '') {
            return null;
        }

        try {
            return strtoupper(Carbon::parse($value)->format('h:ia'));
        } catch (\Throwable) {
            return $value;
        }
    }
}
