<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use App\Models\Gallon;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApiFlavorController extends Controller
{
    /**
     * Best Sellers: top 5 flavors by order count (most ordered by customers).
     * GET /api/v1/best-sellers
     */
    public function bestSellers(): JsonResponse
    {
        $orderCounts = Order::selectRaw('product_name, count(*) as order_count')
            ->groupBy('product_name')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();
        $bestSellerNames = $orderCounts->pluck('product_name');
        $flavorsByName = Flavor::whereIn('name', $bestSellerNames)->get()->keyBy('name');
        $bestSellers = $orderCounts->map(fn ($row) => $flavorsByName->get($row->product_name))->filter()->values();
        return response()->json([
            'success' => true,
            'data' => $bestSellers,
        ]);
    }

    /**
     * Popular: top 5 flavors by average rating (from feedback with flavor_id).
     * GET /api/v1/popular
     */
    public function popular(): JsonResponse
    {
        $popularFlavorIds = DB::table('feedback')
            ->whereNotNull('flavor_id')
            ->selectRaw('flavor_id, avg(rating) as avg_rating')
            ->groupBy('flavor_id')
            ->orderByDesc('avg_rating')
            ->limit(5)
            ->pluck('flavor_id');
        $popularFlavors = $popularFlavorIds->map(fn ($id) => Flavor::find($id))->filter()->values();
        if ($popularFlavors->isEmpty()) {
            $orderCounts = Order::selectRaw('product_name, count(*) as order_count')
                ->groupBy('product_name')
                ->orderByDesc('order_count')
                ->limit(6)
                ->get();
            $flavorsByName = Flavor::whereIn('name', $orderCounts->pluck('product_name'))->get()->keyBy('name');
            $popularFlavors = $orderCounts->skip(1)->take(5)->map(fn ($row) => $flavorsByName->get($row->product_name))->filter()->values();
        }
        if ($popularFlavors->isEmpty()) {
            $popularFlavors = Flavor::orderBy('created_at', 'desc')->limit(5)->get();
        }
        return response()->json([
            'success' => true,
            'data' => $popularFlavors,
        ]);
    }

    /**
     * List all flavors (for Flutter).
     * GET /api/v1/flavors
     */
    public function index(): JsonResponse
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $flavors,
        ]);
    }

    /**
     * Single flavor by id.
     */
    public function show(int $id): JsonResponse
    {
        $flavor = Flavor::find($id);
        if (!$flavor) {
            return response()->json(['success' => false, 'message' => 'Flavor not found.'], 404);
        }
        return response()->json(['success' => true, 'data' => $flavor]);
    }

    /**
     * List gallon sizes (for Flutter).
     */
    public function gallons(): JsonResponse
    {
        $gallons = Gallon::orderBy('size', 'asc')->get();
        return response()->json(['success' => true, 'data' => $gallons]);
    }
}
