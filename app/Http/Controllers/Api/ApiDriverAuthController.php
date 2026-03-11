<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ApiDriverAuthController extends Controller
{
    private const CACHE_PREFIX = 'api_driver_session:';
    private const TTL_MINUTES = 60 * 24 * 7; // 7 days
    private const OTP_TTL_MINUTES = 10;
    private const CHANGE_EMAIL_KEY_PREFIX = 'driver_change_email:';
    private const CHANGE_PASSWORD_KEY_PREFIX = 'driver_change_password:';

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

    /**
     * Change email step 1: verify current password, generate OTP, send it to new email.
     * POST /api/v1/driver/change-email/send-otp
     */
    public function changeEmailSendOtp(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_email' => 'required|email|max:100|unique:drivers,email',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_email.required' => 'Please enter your new email address.',
            'new_email.email' => 'Please enter a valid email address.',
            'new_email.unique' => 'This email is already used by another driver.',
        ]);

        if (!$driver->password || !Hash::check($request->current_password, $driver->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $newEmail = strtolower(trim($request->new_email));
        if (strcasecmp($driver->email ?? '', $newEmail) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Your new email must be different from your current email.',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Cache::put(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id, [
            'otp' => $otp,
            'new_email' => $newEmail,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($newEmail)->send(new OtpVerificationMail($otp, $newEmail));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not send OTP to the new email. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP has been sent to your new email.',
            'expires_in_minutes' => self::OTP_TTL_MINUTES,
        ]);
    }

    /**
     * Change email step 2: verify OTP and update email.
     * POST /api/v1/driver/change-email/verify-otp
     */
    public function changeEmailVerifyOtp(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit OTP.',
            'otp.size' => 'The OTP must be 4 digits.',
            'otp.regex' => 'The OTP must be 4 digits only.',
        ]);

        $payload = Cache::get(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id);
        if (!$payload || !is_array($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new OTP.',
            ], 422);
        }

        $storedOtp = (string) ($payload['otp'] ?? '');
        $newEmail = (string) ($payload['new_email'] ?? '');

        if ($storedOtp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }

        if ($newEmail === '') {
            Cache::forget(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id);
            return response()->json([
                'success' => false,
                'message' => 'Email change session is invalid. Please try again.',
            ], 422);
        }

        $exists = Driver::where('email', $newEmail)->where('id', '!=', $driver->id)->exists();
        if ($exists) {
            Cache::forget(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id);
            return response()->json([
                'success' => false,
                'message' => 'This email is already used by another driver.',
            ], 422);
        }

        $driver->update(['email' => $newEmail]);
        Cache::forget(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id);

        return response()->json([
            'success' => true,
            'message' => 'Email updated successfully.',
            'driver' => $this->driverProfileArray($driver->fresh()),
        ]);
    }

    /**
     * Change email: resend OTP to pending new email.
     * POST /api/v1/driver/change-email/resend-otp
     */
    public function changeEmailResendOtp(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $payload = Cache::get(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id);
        $newEmail = is_array($payload) ? (string) ($payload['new_email'] ?? '') : '';
        if ($newEmail === '') {
            return response()->json([
                'success' => false,
                'message' => 'No pending email change found. Start again by sending OTP.',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Cache::put(self::CHANGE_EMAIL_KEY_PREFIX . $driver->id, [
            'otp' => $otp,
            'new_email' => $newEmail,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($newEmail)->send(new OtpVerificationMail($otp, $newEmail));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not resend OTP. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A new OTP has been sent to your new email.',
            'expires_in_minutes' => self::OTP_TTL_MINUTES,
        ]);
    }

    /**
     * Update driver's phone number.
     * POST /api/v1/driver/change-phone
     */
    public function changePhone(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'phone' => 'required|string|max:30|unique:drivers,phone,' . $driver->id,
        ], [
            'phone.required' => 'Please enter your phone number.',
            'phone.unique' => 'This phone number is already used by another driver.',
        ]);

        $newPhone = trim((string) $request->phone);
        if ($newPhone === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please enter your phone number.',
            ], 422);
        }

        if (($driver->phone ?? '') === $newPhone) {
            return response()->json([
                'success' => true,
                'message' => 'Phone number is already up to date.',
                'driver' => $this->driverProfileArray($driver),
            ]);
        }

        $driver->update(['phone' => $newPhone]);

        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully.',
            'driver' => $this->driverProfileArray($driver->fresh()),
        ]);
    }

    /**
     * Change password step 1: validate current/new password, then send OTP to driver's current email.
     * POST /api/v1/driver/change-password/send-otp
     */
    public function changePasswordSendOtp(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        if (!$driver->email) {
            return response()->json([
                'success' => false,
                'message' => 'Driver email is not set. Please contact admin.',
            ], 422);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter your new password.',
            'new_password.confirmed' => 'New password and retype password do not match.',
            'new_password.min' => 'New password must be at least 6 characters.',
        ]);

        if (!$driver->password || !Hash::check($request->current_password, $driver->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        if (Hash::check($request->new_password, $driver->password)) {
            return response()->json([
                'success' => false,
                'message' => 'New password must be different from current password.',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Cache::put(self::CHANGE_PASSWORD_KEY_PREFIX . $driver->id, [
            'otp' => $otp,
            'new_password_hash' => Hash::make($request->new_password),
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($driver->email)->send(new OtpVerificationMail($otp, $driver->email));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not send OTP email. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP has been sent to your email.',
            'expires_in_minutes' => self::OTP_TTL_MINUTES,
        ]);
    }

    /**
     * Change password step 2: verify OTP and update password.
     * POST /api/v1/driver/change-password/verify-otp
     */
    public function changePasswordVerifyOtp(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit OTP.',
            'otp.size' => 'The OTP must be 4 digits.',
            'otp.regex' => 'The OTP must be 4 digits only.',
        ]);

        $payload = Cache::get(self::CHANGE_PASSWORD_KEY_PREFIX . $driver->id);
        if (!$payload || !is_array($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new OTP.',
            ], 422);
        }

        $storedOtp = (string) ($payload['otp'] ?? '');
        $newPasswordHash = (string) ($payload['new_password_hash'] ?? '');

        if ($storedOtp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }

        if ($newPasswordHash === '') {
            Cache::forget(self::CHANGE_PASSWORD_KEY_PREFIX . $driver->id);
            return response()->json([
                'success' => false,
                'message' => 'Password change session is invalid. Please try again.',
            ], 422);
        }

        $driver->update(['password' => $newPasswordHash]);
        Cache::forget(self::CHANGE_PASSWORD_KEY_PREFIX . $driver->id);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * Change password: resend OTP to current driver email.
     * POST /api/v1/driver/change-password/resend-otp
     */
    public function changePasswordResendOtp(Request $request): JsonResponse
    {
        $driver = $request->user();
        if (!$driver instanceof Driver) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        if (!$driver->email) {
            return response()->json([
                'success' => false,
                'message' => 'Driver email is not set. Please contact admin.',
            ], 422);
        }

        $payload = Cache::get(self::CHANGE_PASSWORD_KEY_PREFIX . $driver->id);
        $newPasswordHash = is_array($payload) ? (string) ($payload['new_password_hash'] ?? '') : '';
        if ($newPasswordHash === '') {
            return response()->json([
                'success' => false,
                'message' => 'No pending password change found. Start again by sending OTP.',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Cache::put(self::CHANGE_PASSWORD_KEY_PREFIX . $driver->id, [
            'otp' => $otp,
            'new_password_hash' => $newPasswordHash,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($driver->email)->send(new OtpVerificationMail($otp, $driver->email));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not resend OTP. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A new OTP has been sent to your email.',
            'expires_in_minutes' => self::OTP_TTL_MINUTES,
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
