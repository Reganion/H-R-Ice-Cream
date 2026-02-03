<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiFavoriteController extends Controller
{
    /**
     * List favorites for the authenticated customer.
     * GET /api/v1/favorites
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $favorites = $customer->favorites()->orderBy('favorites.created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }

    /**
     * Add a flavor to favorites (toggle: if already in favorites, remove it).
     * POST /api/v1/favorites
     * Body: { "flavor_id": 1 }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'flavor_id' => 'required|integer|exists:flavors,id',
        ]);
        $customer = $request->user();
        $flavorId = (int) $request->flavor_id;

        $exists = $customer->favorites()->where('flavor_id', $flavorId)->exists();
        if ($exists) {
            $customer->favorites()->detach($flavorId);
            return response()->json([
                'success' => true,
                'message' => 'Removed from favorites.',
                'is_favorite' => false,
            ]);
        }

        $customer->favorites()->attach($flavorId);
        return response()->json([
            'success' => true,
            'message' => 'Added to favorites.',
            'is_favorite' => true,
        ]);
    }

    /**
     * Remove a flavor from favorites.
     * DELETE /api/v1/favorites/{flavor_id}
     */
    public function destroy(Request $request, int $flavorId): JsonResponse
    {
        $flavor = Flavor::find($flavorId);
        if (!$flavor) {
            return response()->json(['success' => false, 'message' => 'Flavor not found.'], 404);
        }
        $request->user()->favorites()->detach($flavorId);
        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites.',
        ]);
    }

    /**
     * Check if a flavor is in favorites (for UI state).
     * GET /api/v1/favorites/check?flavor_id=1
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'flavor_id' => 'required|integer|exists:flavors,id',
        ]);
        $flavorId = (int) $request->query('flavor_id');
        $isFavorite = $request->user()->favorites()->where('flavor_id', $flavorId)->exists();
        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
        ]);
    }
}
