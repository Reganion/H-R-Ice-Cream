<?php

namespace App\Http\Middleware;

use App\Models\Driver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiDriver
{
    public const CACHE_PREFIX = 'api_driver_session:';

    /**
     * Handle an incoming request. Expects Bearer token or X-Session-Token header.
     * Token is stored in cache as api_driver_session:{token} => driver_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated. Send Authorization: Bearer {token} or X-Session-Token header.',
            ], 401);
        }

        $driverId = Cache::get(self::CACHE_PREFIX . $token);
        if (!$driverId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please log in again.',
            ], 401);
        }

        $driver = Driver::find($driverId);
        if (!$driver) {
            Cache::forget(self::CACHE_PREFIX . $token);
            return response()->json([
                'success' => false,
                'message' => 'Session invalid. Please log in again.',
            ], 401);
        }

        if ($driver->status === Driver::STATUS_DEACTIVATE) {
            Cache::forget(self::CACHE_PREFIX . $token);
            return response()->json([
                'success' => false,
                'message' => 'Your account is deactivated. Please contact admin.',
            ], 403);
        }

        $request->setUserResolver(fn () => $driver);

        return $next($request);
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
