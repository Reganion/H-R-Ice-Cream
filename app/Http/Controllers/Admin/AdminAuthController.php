<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'This field is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'This field is required.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => 'Invalid email or password.']);
        }

        $request->session()->put('admin_id', $user->id);
        return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
    }

    public function register(Request $request)
    {
        $existing = User::where('email', $request->email)->first();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'image'      => 'nullable|image|max:2048',
            'password'   => 'required|string|confirmed|min:6',
        ], [
            'first_name.required'  => 'First name is required.',
            'last_name.required'   => 'Last name is required.',
            'email.required'       => 'Email is required.',
            'password.required'    => 'Password is required.',
            'password.confirmed'   => 'Passwords do not match.',
            'password.min'         => 'Password must be at least 6 characters.',
        ]);

        if ($existing !== null) {
            return redirect()->back()
                ->withInput($request->only('first_name', 'last_name', 'email'))
                ->withErrors(['email' => 'This email is already registered.']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $dir = public_path('img/admins');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $image = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image->getClientOriginalName());
            $image->move($dir, $filename);
            $imagePath = 'img/admins/' . $filename;
        }

        User::create([
            'name'       => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'image'      => $imagePath,
            'password'   => Hash::make($request->password),
        ]);

        return redirect()->route('admin.login')->with('success', 'Account created successfully! Please log in.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function updateAccount(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        if ($request->session()->has('admin_password_change_pending')) {
            return redirect()->route('admin.account')
                ->with('error', 'Please finish or cancel the pending password change OTP first.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required'  => 'Last name is required.',
            'email.required'      => 'Email is required.',
            'email.email'         => 'Please enter a valid email address.',
            'email.unique'        => 'This email is already in use.',
        ]);

        $newEmail = strtolower(trim((string) $validated['email']));
        $currentEmail = strtolower((string) $admin->email);
        $emailChanged = $newEmail !== $currentEmail;

        if ($emailChanged) {
            $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $admin->update([
                'otp'            => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            $request->session()->put('admin_account_update_pending', [
                'first_name' => trim((string) $validated['first_name']),
                'last_name'  => trim((string) $validated['last_name']),
                'email'      => $newEmail,
            ]);

            try {
                Mail::to($admin->email)->send(new OtpVerificationMail($otp, $admin->email));
            } catch (\Throwable $e) {
                report($e);
                return redirect()->route('admin.account')
                    ->withInput()
                    ->with('error', 'Could not send OTP to your current email. Please try again.');
            }

            return redirect()->route('admin.account')
                ->withInput()
                ->with('success', 'A 4-digit OTP was sent to your current email. Enter it below to confirm the email change.');
        }

        $firstName = trim((string) $validated['first_name']);
        $lastName = trim((string) $validated['last_name']);

        $admin->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'name'       => trim($firstName . ' ' . $lastName),
            'email'      => $newEmail,
            'otp'        => null,
            'otp_expires_at' => null,
        ]);

        $request->session()->forget('admin_account_update_pending');

        return redirect()->route('admin.account')->with('success', 'Account updated successfully.');
    }

    public function verifyAccountEmailOtp(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit OTP.',
            'otp.size'     => 'The OTP must be 4 digits.',
            'otp.regex'    => 'The OTP must contain digits only.',
        ]);

        $pending = $request->session()->get('admin_account_update_pending');
        if (!$pending || !is_array($pending)) {
            return redirect()->route('admin.account')->with('error', 'No pending email change found.');
        }

        if ((string) $admin->otp !== (string) $request->otp) {
            return redirect()->route('admin.account')
                ->withErrors(['otp' => 'Invalid OTP. Please try again.'])
                ->withInput();
        }

        if ($admin->otp_expires_at && $admin->otp_expires_at->isPast()) {
            return redirect()->route('admin.account')
                ->withErrors(['otp' => 'This OTP has expired. Please submit the account update again.'])
                ->withInput();
        }

        $newEmail = strtolower(trim((string) ($pending['email'] ?? '')));
        $firstName = trim((string) ($pending['first_name'] ?? ''));
        $lastName = trim((string) ($pending['last_name'] ?? ''));

        if ($newEmail === '' || $firstName === '' || $lastName === '') {
            $request->session()->forget('admin_account_update_pending');
            return redirect()->route('admin.account')->with('error', 'Pending update data is invalid. Please try again.');
        }

        $emailAlreadyUsed = User::where('email', $newEmail)->where('id', '!=', $admin->id)->exists();
        if ($emailAlreadyUsed) {
            $request->session()->forget('admin_account_update_pending');
            return redirect()->route('admin.account')->with('error', 'That email is already in use by another account.');
        }

        $admin->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'name'       => trim($firstName . ' ' . $lastName),
            'email'      => $newEmail,
            'otp'        => null,
            'otp_expires_at' => null,
        ]);

        $request->session()->forget('admin_account_update_pending');

        return redirect()->route('admin.account')->with('success', 'Email address verified and account updated successfully.');
    }

    public function resendAccountEmailOtp(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        $pending = $request->session()->get('admin_account_update_pending');
        if (!$pending || !is_array($pending)) {
            return redirect()->route('admin.account')->with('error', 'No pending email change found.');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $admin->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($admin->email)->send(new OtpVerificationMail($otp, $admin->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.account')
                ->with('error', 'Could not resend OTP. Please try again.');
        }

        return redirect()->route('admin.account')
            ->with('success', 'A new OTP was sent to your current email.');
    }

    public function cancelAccountEmailOtp(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        $admin->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        $request->session()->forget('admin_account_update_pending');
        $request->session()->forget('admin_password_change_pending');
        $request->session()->forget('admin_password_change_form');

        return redirect()->route('admin.account')->with('success', 'Verification request was cancelled.');
    }

    public function sendAccountPasswordOtp(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        $request->session()->put('admin_password_change_form', true);

        if ($request->session()->has('admin_account_update_pending')) {
            return redirect()->route('admin.account')
                ->with('error', 'Please finish or cancel the pending email change OTP first.');
        }

        $validated = $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string|min:6',
        ], [
            'current_password.required'          => 'Current password is required.',
            'new_password.required'              => 'New password is required.',
            'new_password.min'                   => 'New password must be at least 6 characters.',
            'new_password.confirmed'             => 'New password and retype password do not match.',
            'new_password_confirmation.required' => 'Retype password is required.',
        ]);

        if (!Hash::check((string) $validated['current_password'], (string) $admin->password)) {
            return redirect()->route('admin.account')
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        if (Hash::check((string) $validated['new_password'], (string) $admin->password)) {
            return redirect()->route('admin.account')
                ->withErrors(['new_password' => 'New password must be different from current password.'])
                ->withInput();
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $admin->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $request->session()->put('admin_password_change_pending', [
            'password_hash' => Hash::make((string) $validated['new_password']),
        ]);
        $request->session()->forget('admin_password_change_form');

        try {
            Mail::to($admin->email)->send(new OtpVerificationMail($otp, $admin->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.account')
                ->withInput()
                ->with('error', 'Could not send OTP to your current email. Please try again.');
        }

        return redirect()->route('admin.account')
            ->with('success', 'A 4-digit OTP was sent to your current email to verify password change.');
    }

    public function verifyAccountPasswordOtp(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit OTP.',
            'otp.size'     => 'The OTP must be 4 digits.',
            'otp.regex'    => 'The OTP must contain digits only.',
        ]);

        $pending = $request->session()->get('admin_password_change_pending');
        if (!$pending || !is_array($pending) || empty($pending['password_hash'])) {
            return redirect()->route('admin.account')->with('error', 'No pending password change found.');
        }

        if ((string) $admin->otp !== (string) $request->otp) {
            return redirect()->route('admin.account')
                ->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        if ($admin->otp_expires_at && $admin->otp_expires_at->isPast()) {
            return redirect()->route('admin.account')
                ->withErrors(['otp' => 'This OTP has expired. Please submit the password change again.']);
        }

        $admin->update([
            'password'       => (string) $pending['password_hash'],
            'otp'            => null,
            'otp_expires_at' => null,
        ]);

        $request->session()->forget('admin_password_change_pending');
        $request->session()->forget('admin_password_change_form');

        return redirect()->route('admin.account')->with('success', 'Password updated successfully.');
    }

    public function resendAccountPasswordOtp(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in again.');
        }

        $pending = $request->session()->get('admin_password_change_pending');
        if (!$pending || !is_array($pending) || empty($pending['password_hash'])) {
            return redirect()->route('admin.account')->with('error', 'No pending password change found.');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $admin->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($admin->email)->send(new OtpVerificationMail($otp, $admin->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.account')
                ->with('error', 'Could not resend OTP. Please try again.');
        }

        return redirect()->route('admin.account')
            ->with('success', 'A new OTP was sent to your current email.');
    }

    public function startAccountPasswordChange(Request $request)
    {
        if ($request->session()->has('admin_account_update_pending')) {
            return redirect()->route('admin.account')
                ->with('error', 'Please finish or cancel the pending email change OTP first.');
        }

        $request->session()->put('admin_password_change_form', true);

        return redirect()->route('admin.account');
    }

    public function cancelAccountPasswordChange(Request $request)
    {
        $request->session()->forget('admin_password_change_form');
        $request->session()->forget('admin_password_change_pending');

        return redirect()->route('admin.account');
    }

    // --- Forgot Password (same flow as customer: email → OTP → verify → reset) ---

    public function showForgotPasswordForm()
    {
        return view('Admin.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $request->session()->put('admin_forgot_password_email', $user->email);

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp, $user->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->with('error', 'Could not send the verification code. Please try again later.');
        }

        return redirect()->route('admin.forgot-password.verify-otp')
            ->with('success', 'A 4-digit code has been sent to your email. Enter it below.');
    }

    public function showForgotPasswordOtpForm(Request $request)
    {
        $email = $request->session()->get('admin_forgot_password_email');
        if (!$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Please enter your email first to receive a verification code.');
        }
        return view('Admin.verify-otp-forgot', ['email' => $email]);
    }

    public function resendForgotPasswordOtp(Request $request)
    {
        $email = $request->session()->get('admin_forgot_password_email');
        if (!$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $request->session()->forget('admin_forgot_password_email');
            return redirect()->route('admin.forgot-password')->with('error', 'Account not found.');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp, $user->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.forgot-password.verify-otp')
                ->with('error', 'Could not send the new code. Please try again later.');
        }

        return redirect()->route('admin.forgot-password.verify-otp')
            ->with('success', 'A new 4-digit code has been sent to your email.');
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $email = $request->session()->get('admin_forgot_password_email');
        if (!$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit code.',
            'otp.size'     => 'The code must be 4 digits.',
            'otp.regex'    => 'The code must be 4 digits only.',
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            $request->session()->forget('admin_forgot_password_email');
            return redirect()->route('admin.forgot-password')->with('error', 'Account not found.');
        }

        if ($user->otp !== $request->otp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            return redirect()->back()
                ->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        $user->update(['otp' => null, 'otp_expires_at' => null]);
        return redirect()->route('admin.forgot-password.reset-password')
            ->with('success', 'Code verified. Enter your new password below.');
    }

    public function showResetPasswordForm(Request $request)
    {
        $email = $request->session()->get('admin_forgot_password_email');
        if (!$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Session expired. Please start the process again.');
        }
        return view('Admin.reset-password', ['email' => $email]);
    }

    public function updatePassword(Request $request)
    {
        $email = $request->session()->get('admin_forgot_password_email');
        if (!$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Session expired. Please start the process again.');
        }

        $request->validate([
            'password' => 'required|string|confirmed|min:6',
        ], [
            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min'       => 'Password must be at least 6 characters.',
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            $request->session()->forget('admin_forgot_password_email');
            return redirect()->route('admin.forgot-password')->with('error', 'Account not found.');
        }

        $user->update(['password' => $request->password]);
        $request->session()->forget('admin_forgot_password_email');

        return redirect()->route('admin.login')->with('success', 'Your password has been updated. You can now log in.');
    }
}
