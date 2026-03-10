<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiDriverAuthController extends Controller
{
    private const CACHE_PREFIX = 'api_driver_session:';
    private const TTL_MINUTES = 60 * 24 * 7; // 7 days

    /**
     * Driver login (for Flutter rider app).
     * POST /api/v1/driver/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $driver = Driver::where('email', $request->email)->first();

        if (!$driver || !$driver->password || !Hash::check($request->password, $driver->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        if ($driver->status === Driver::STATUS_DEACTIVATE) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is deactivated. Please contact admin.',
            ], 403);
        }

        $token = Str::random(64);
        Cache::put(self::CACHE_PREFIX . $token, $driver->id, now()->addMinutes(self::TTL_MINUTES));

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully.',
            'driver' => $this->driverProfileArray($driver),
            'token' => $token,
        ]);
    }

    /**
     * Get currently authenticated driver profile.
     * GET /api/v1/driver/me
     */
    public function me(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        return response()->json([
            'success' => true,
            'driver' => $this->driverProfileArray($driver),
        ]);
    }

    /**
     * Driver logout and invalidate token.
     * POST /api/v1/driver/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $this->getTokenFromRequest($request);
        if ($token) {
            Cache::forget(self::CACHE_PREFIX . $token);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out.',
        ]);
    }

    private function driverProfileArray(Driver $driver): array
    {
        $imagePath = $driver->image;

        return [
            'id' => $driver->id,
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'status' => $driver->status,
            'driver_code' => $driver->driver_code,
            'license_no' => $driver->license_no,
            'license_type' => $driver->license_type,
            'image' => $imagePath,
            'image_url' => $imagePath ? url($imagePath) : null,
        ];
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return trim($m[1]);
        }
        return $request->header('X-Session-Token') ?: null;
    }
}
