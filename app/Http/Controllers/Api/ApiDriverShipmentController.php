<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDriverShipmentController extends Controller
{
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
            $query->whereRaw('LOWER(COALESCE(status_driver, "")) = ?', ['accepted']);
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
     * Driver marks shipment completed.
     * Keep order status assigned; set status_driver=completed.
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
                'message' => 'Only assigned shipments can be completed.',
            ], 422);
        }

        $order->status_driver = 'completed';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Shipment marked completed.',
        ]);
    }

    private function statusesForTab(string $tab): array
    {
        return match ($tab) {
            'accepted' => ['assigned'],
            'completed' => ['assigned'],
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
}
