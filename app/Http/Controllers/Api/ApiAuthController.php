<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    /**
     * Customer login (for Flutter). Returns token if Sanctum installed, else success + user.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        if (!$customer->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email with the 4-digit OTP first.',
                'email' => $customer->email,
            ], 403);
        }

        $token = null;
        if (method_exists($customer, 'createToken')) {
            $token = $customer->createToken('flutter')->plainTextToken;
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully.',
            'customer' => [
                'id' => $customer->id,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'contact_no' => $customer->contact_no,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Customer register (for Flutter). Sends OTP to email; verify via verify-otp before login.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:customers,email',
            'contact_no' => 'nullable|string|max:20',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $otpExpiresAt = now()->addMinutes(10);

        $customer = Customer::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'password' => Hash::make($request->password),
            'otp' => $otp,
            'otp_expires_at' => $otpExpiresAt,
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Account created but we could not send the verification email.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account created. A 4-digit code was sent to your email. Verify with POST /api/v1/verify-otp.',
            'email' => $customer->email,
            'customer' => [
                'id' => $customer->id,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'contact_no' => $customer->contact_no,
            ],
        ], 201);
    }

    /**
     * Verify OTP (for Flutter). Send email + otp; returns success so client can then call login.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit code.',
            'otp.size' => 'The code must be 4 digits.',
            'otp.regex' => 'The code must be 4 digits only.',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found.',
            ], 404);
        }

        if ($customer->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code. Please try again.',
            ], 422);
        }

        if ($customer->otp_expires_at && $customer->otp_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This code has expired. Request a new one with POST /api/v1/resend-otp.',
            ], 422);
        }

        $customer->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified. You can now log in.',
            'email' => $customer->email,
        ]);
    }

    /**
     * Resend OTP (for Flutter). Send email; generates new 4-digit code and emails it.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found.',
            ], 404);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not send the new code. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A new 4-digit code has been sent to your email.',
            'email' => $customer->email,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user() && method_exists($request->user(), 'currentAccessToken')) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'contact_no' => $user->contact_no,
            ],
        ]);
    }

    /**
     * Forgot password: send OTP to email (for Flutter).
     * POST /api/v1/forgot-password { "email": "user@example.com" }
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not send the verification code. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A 4-digit code has been sent to your email. Use POST /api/v1/forgot-password/verify-otp with email and otp.',
            'email' => $customer->email,
        ]);
    }

    /**
     * Forgot password: resend OTP (for Flutter).
     * POST /api/v1/forgot-password/resend-otp { "email": "user@example.com" }
     */
    public function resendForgotPasswordOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Could not send the new code. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A new 4-digit code has been sent to your email.',
            'email' => $customer->email,
        ]);
    }

    /**
     * Forgot password: verify OTP and get a short-lived reset token (for Flutter).
     * POST /api/v1/forgot-password/verify-otp { "email": "user@example.com", "otp": "1234" }
     * Returns reset_token; use it in POST /api/v1/forgot-password/reset-password.
     */
    public function verifyForgotPasswordOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit code.',
            'otp.size' => 'The code must be 4 digits.',
            'otp.regex' => 'The code must be 4 digits only.',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found.',
            ], 404);
        }

        if ($customer->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code. Please try again.',
            ], 422);
        }

        if ($customer->otp_expires_at && $customer->otp_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This code has expired. Request a new one with POST /api/v1/forgot-password/resend-otp.',
            ], 422);
        }

        $customer->update(['otp' => null, 'otp_expires_at' => null]);

        $resetToken = Str::random(64);
        Cache::put('password_reset:' . $resetToken, $customer->email, now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'message' => 'Code verified. Use the reset_token in POST /api/v1/forgot-password/reset-password to set your new password.',
            'reset_token' => $resetToken,
            'expires_in_minutes' => 15,
        ]);
    }

    /**
     * Forgot password: set new password using reset_token (for Flutter).
     * POST /api/v1/forgot-password/reset-password { "reset_token": "...", "password": "newpass", "password_confirmation": "newpass" }
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ], [
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $email = Cache::get('password_reset:' . $request->reset_token);
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token. Please start the forgot-password flow again.',
            ], 422);
        }

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            Cache::forget('password_reset:' . $request->reset_token);
            return response()->json([
                'success' => false,
                'message' => 'Account not found.',
            ], 404);
        }

        $customer->update(['password' => $request->password]);
        Cache::forget('password_reset:' . $request->reset_token);

        return response()->json([
            'success' => true,
            'message' => 'Your password has been updated. You can now log in.',
        ]);
    }
}
