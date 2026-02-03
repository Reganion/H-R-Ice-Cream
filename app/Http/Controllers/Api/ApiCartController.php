<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCartController extends Controller
{
    private const MAX_QUANTITY = 5;

    /**
     * List cart items for the authenticated customer.
     * GET /api/v1/cart
     * Returns each item with flavor, gallon, quantity, and line_total (flavor price * qty + gallon addon * qty).
     */
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->cartItems()
            ->with(['flavor', 'gallon'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (CartItem $item) => $this->cartItemToArray($item));

        $subtotal = $items->sum('line_total');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'subtotal' => round($subtotal, 2),
                'count' => $items->count(),
            ],
        ]);
    }

    /**
     * Add to cart (or update quantity if same flavor + gallon already in cart).
     * POST /api/v1/cart
     * Body: { "flavor_id": 1, "gallon_id": 1, "quantity": 1 }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'flavor_id' => 'required|integer|exists:flavors,id',
            'gallon_id' => 'required|integer|exists:gallons,id',
            'quantity' => 'required|integer|min:1|max:' . self::MAX_QUANTITY,
        ]);

        $customer = $request->user();
        $flavorId = (int) $request->flavor_id;
        $gallonId = (int) $request->gallon_id;
        $quantity = (int) $request->quantity;

        $existing = $customer->cartItems()
            ->where('flavor_id', $flavorId)
            ->where('gallon_id', $gallonId)
            ->first();

        if ($existing) {
            $newQty = min(self::MAX_QUANTITY, $existing->quantity + $quantity);
            $existing->update(['quantity' => $newQty]);
            $cartItem = $existing->fresh(['flavor', 'gallon']);
            $message = 'Cart updated.';
        } else {
            $cartItem = $customer->cartItems()->create([
                'flavor_id' => $flavorId,
                'gallon_id' => $gallonId,
                'quantity' => $quantity,
            ]);
            $cartItem->load(['flavor', 'gallon']);
            $message = 'Added to cart.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->cartItemToArray($cartItem),
        ], 201);
    }

    /**
     * Update cart item quantity.
     * PUT/PATCH /api/v1/cart/{id}
     * Body: { "quantity": 2 }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . self::MAX_QUANTITY,
        ]);

        $item = $request->user()->cartItems()->find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Cart item not found.'], 404);
        }

        $item->update(['quantity' => (int) $request->quantity]);
        $item->load(['flavor', 'gallon']);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'data' => $this->cartItemToArray($item),
        ]);
    }

    /**
     * Remove item from cart.
     * DELETE /api/v1/cart/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = $request->user()->cartItems()->find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Cart item not found.'], 404);
        }
        $item->delete();
        return response()->json([
            'success' => true,
            'message' => 'Removed from cart.',
        ]);
    }

    private function cartItemToArray(CartItem $item): array
    {
        $flavor = $item->flavor;
        $gallon = $item->gallon;
        $flavorPrice = $flavor ? (float) $flavor->price : 0;
        $addonPrice = $gallon ? (float) $gallon->addon_price : 0;
        $qty = $item->quantity;
        $lineTotal = ($flavorPrice + $addonPrice) * $qty;

        return [
            'id' => $item->id,
            'flavor_id' => $item->flavor_id,
            'gallon_id' => $item->gallon_id,
            'quantity' => $qty,
            'flavor' => $flavor ? [
                'id' => $flavor->id,
                'name' => $flavor->name,
                'category' => $flavor->category,
                'price' => $flavorPrice,
                'image' => $flavor->image,
                'mobile_image' => $flavor->mobile_image,
            ] : null,
            'gallon' => $gallon ? [
                'id' => $gallon->id,
                'size' => $gallon->size,
                'addon_price' => $addonPrice,
                'image' => $gallon->image,
            ] : null,
            'line_total' => round($lineTotal, 2),
            'created_at' => $item->created_at?->toDateTimeString(),
        ];
    }
}
