<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use App\Models\Gallon;
use Illuminate\Http\JsonResponse;

class ApiFlavorController extends Controller
{
    /**
     * List all flavors (for Flutter).
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
