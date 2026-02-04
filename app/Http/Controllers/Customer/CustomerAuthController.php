<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class CustomerAuthController extends Controller
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

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !\Illuminate\Support\Facades\Hash::check($request->password, $customer->password)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => 'Invalid email or password.']);
        }

        if (!$customer->isVerified()) {
            $request->session()->put('pending_verify_email', $customer->email);
            return redirect()->route('customer.verify-otp')
                ->with('error', 'Please verify your email with the 4-digit code to continue.');
        }

        $request->session()->put('customer_id', $customer->id);
        return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
    }

    public function register(Request $request)
    {
        $existing = Customer::where('email', $request->email)->first();

        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname'  => 'required|string|max:50',
            'email'     => 'required|email|max:100',
            'contact_no'=> 'nullable|string|max:20|regex:/^[\d\s\-+()]+$/',
            'password'  => 'required|string|confirmed|min:6',
        ], [
            'firstname.required' => 'First name is required.',
            'lastname.required'  => 'Last name is required.',
            'email.required'     => 'Email is required.',
            'email.unique'       => 'This email is already registered.',
            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min'       => 'Password must be at least 6 characters.',
        ]);

        if ($existing !== null) {
            return redirect()->back()
                ->withInput($request->only('firstname', 'lastname', 'email', 'contact_no'))
                ->withErrors(['email' => 'This email is already registered.']);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $otpExpiresAt = now()->addMinutes(10);

        $customer = Customer::create([
            'firstname'         => $request->firstname,
            'lastname'          => $request->lastname,
            'email'             => $request->email,
            'contact_no'        => $request->contact_no ? trim($request->contact_no) : null,
            'image'             => 'img/default-user.png',
            'status'            => Customer::STATUS_ACTIVE,
            'password'          => \Illuminate\Support\Facades\Hash::make($request->password),
            'otp'               => $otp,
            'otp_expires_at'    => $otpExpiresAt,
        ]);

        $request->session()->put('pending_verify_email', $customer->email);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            $message = 'Account created but we could not send the verification email. ';
            if (config('app.debug')) {
                $message .= 'Error: ' . $e->getMessage();
            } else {
                $message .= 'Check MAIL_* in .env (Gmail: use App Password, port 587 + TLS or 465 + SSL).';
            }
            return redirect()->route('customer.verify-otp')
                ->with('error', $message);
        }

        return redirect()->route('customer.verify-otp')->with('success', 'We sent a 4-digit code to your email. Enter it below to verify.');
    }

    public function showOtpForm(Request $request)
    {
        $email = $request->session()->get('pending_verify_email');
        if (!$email) {
            return redirect()->route('customer.register')
                ->with('error', 'Please register first to verify your email.');
        }
        return view('Customer.verify-otp', ['email' => $email]);
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('pending_verify_email');
        if (!$email) {
            return redirect()->route('customer.register')
                ->with('error', 'Session expired. Please register again.');
        }

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('pending_verify_email');
            return redirect()->route('customer.register')->with('error', 'Account not found. Please register again.');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('customer.verify-otp')
                ->with('error', 'Could not send the new code. Please try again later.');
        }

        return redirect()->route('customer.verify-otp')
            ->with('success', 'A new 4-digit code has been sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $email = $request->session()->get('pending_verify_email');
        if (!$email) {
            return redirect()->route('customer.register')
                ->with('error', 'Session expired. Please register again.');
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit code.',
            'otp.size'     => 'The code must be 4 digits.',
            'otp.regex'    => 'The code must be 4 digits only.',
        ]);

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('pending_verify_email');
            return redirect()->route('customer.register')->with('error', 'Account not found. Please register again.');
        }

        if ($customer->otp !== $request->otp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        if ($customer->otp_expires_at && $customer->otp_expires_at->isPast()) {
            return redirect()->back()
                ->withErrors(['otp' => 'This code has expired. Please request a new one by registering again.']);
        }

        $customer->update([
            'email_verified_at' => now(),
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);
        $request->session()->forget('pending_verify_email');

        return redirect()->route('customer.login')->with('success', 'Email verified. You can now log in.');
    }

    public function showForgotPasswordForm()
    {
        return view('Customer.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $request->session()->put('forgot_password_email', $customer->email);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->with('error', 'Could not send the verification code. Please try again later.');
        }

        return redirect()->route('customer.forgot-password.verify-otp')
            ->with('success', 'A 4-digit code has been sent to your email. Enter it below.');
    }

    public function showForgotPasswordOtpForm(Request $request)
    {
        $email = $request->session()->get('forgot_password_email');
        if (!$email) {
            return redirect()->route('customer.forgot-password')
                ->with('error', 'Please enter your email first to receive a verification code.');
        }
        return view('Customer.verify-otp-forgot', ['email' => $email]);
    }

    public function resendForgotPasswordOtp(Request $request)
    {
        $email = $request->session()->get('forgot_password_email');
        if (!$email) {
            return redirect()->route('customer.forgot-password')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('forgot_password_email');
            return redirect()->route('customer.forgot-password')->with('error', 'Account not found.');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('customer.forgot-password.verify-otp')
                ->with('error', 'Could not send the new code. Please try again later.');
        }

        return redirect()->route('customer.forgot-password.verify-otp')
            ->with('success', 'A new 4-digit code has been sent to your email.');
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $email = $request->session()->get('forgot_password_email');
        if (!$email) {
            return redirect()->route('customer.forgot-password')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit code.',
            'otp.size'     => 'The code must be 4 digits.',
            'otp.regex'    => 'The code must be 4 digits only.',
        ]);

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('forgot_password_email');
            return redirect()->route('customer.forgot-password')->with('error', 'Account not found.');
        }

        if ($customer->otp !== $request->otp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        if ($customer->otp_expires_at && $customer->otp_expires_at->isPast()) {
            return redirect()->back()
                ->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        $customer->update(['otp' => null, 'otp_expires_at' => null]);
        return redirect()->route('customer.forgot-password.reset-password')
            ->with('success', 'Code verified. Enter your new password below.');
    }

    public function showResetPasswordForm(Request $request)
    {
        $email = $request->session()->get('forgot_password_email');
        if (!$email) {
            return redirect()->route('customer.forgot-password')
                ->with('error', 'Session expired. Please start the process again.');
        }
        return view('Customer.reset-password', ['email' => $email]);
    }

    public function updatePassword(Request $request)
    {
        $email = $request->session()->get('forgot_password_email');
        if (!$email) {
            return redirect()->route('customer.forgot-password')
                ->with('error', 'Session expired. Please start the process again.');
        }

        $request->validate([
            'password' => 'required|string|confirmed|min:6',
        ], [
            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min'       => 'Password must be at least 6 characters.',
        ]);

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('forgot_password_email');
            return redirect()->route('customer.forgot-password')->with('error', 'Account not found.');
        }

        $customer->update(['password' => $request->password]);
        $request->session()->forget('forgot_password_email');

        return redirect()->route('customer.login')->with('success', 'Your password has been updated. You can now log in.');
    }

    // --- Change Password (logged-in customer: email → OTP → current + new password → keep logged in or re-login) ---

    public function showChangePasswordForm(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in to change your password.');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Session expired. Please log in again.');
        }
        return view('Customer.change-password', ['customer' => $customer]);
    }

    public function sendChangePasswordOtp(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in to change your password.');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Session expired. Please log in again.');
        }

        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        if (strcasecmp($customer->email, $request->email) !== 0) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This email does not match your account.']);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $request->session()->put('change_password_email', $customer->email);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->with('error', 'Could not send the verification code. Please try again later.');
        }

        return redirect()->route('customer.change-password.verify-otp')
            ->with('success', 'A 4-digit code has been sent to your email. Enter it below.');
    }

    public function showChangePasswordOtpForm(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in first.');
        }
        $email = $request->session()->get('change_password_email');
        if (!$email) {
            return redirect()->route('customer.change-password')
                ->with('error', 'Please enter your email first to receive a verification code.');
        }
        return view('Customer.change-password-verify-otp', ['email' => $email]);
    }

    public function resendChangePasswordOtp(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in first.');
        }
        $email = $request->session()->get('change_password_email');
        if (!$email) {
            return redirect()->route('customer.change-password')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('change_password_email');
            return redirect()->route('customer.change-password')->with('error', 'Account not found.');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $customer->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($customer->email)->send(new OtpVerificationMail($otp, $customer->email));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('customer.change-password.verify-otp')
                ->with('error', 'Could not send the new code. Please try again later.');
        }

        return redirect()->route('customer.change-password.verify-otp')
            ->with('success', 'A new 4-digit code has been sent to your email.');
    }

    public function verifyChangePasswordOtp(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in first.');
        }
        $email = $request->session()->get('change_password_email');
        if (!$email) {
            return redirect()->route('customer.change-password')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $request->validate([
            'otp' => 'required|string|size:4|regex:/^\d{4}$/',
        ], [
            'otp.required' => 'Please enter the 4-digit code.',
            'otp.size'     => 'The code must be 4 digits.',
            'otp.regex'    => 'The code must be 4 digits only.',
        ]);

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('change_password_email');
            return redirect()->route('customer.change-password')->with('error', 'Account not found.');
        }

        if ($customer->otp !== $request->otp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        if ($customer->otp_expires_at && $customer->otp_expires_at->isPast()) {
            return redirect()->back()
                ->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        $customer->update(['otp' => null, 'otp_expires_at' => null]);
        return redirect()->route('customer.change-password.new-password')
            ->with('success', 'Code verified. Enter your current password and new password below.');
    }

    public function showChangePasswordNewPasswordForm(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in first.');
        }
        $email = $request->session()->get('change_password_email');
        if (!$email) {
            return redirect()->route('customer.change-password')
                ->with('error', 'Session expired. Please start the process again.');
        }
        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('change_password_email');
            return redirect()->route('customer.change-password')->with('error', 'Account not found.');
        }
        return view('Customer.change-password-new-password', ['customer' => $customer]);
    }

    public function updateChangePassword(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in first.');
        }
        $email = $request->session()->get('change_password_email');
        if (!$email) {
            return redirect()->route('customer.change-password')
                ->with('error', 'Session expired. Please start the process again.');
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|confirmed|min:6',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required'         => 'New password is required.',
            'password.confirmed'        => 'New passwords do not match.',
            'password.min'               => 'New password must be at least 6 characters.',
        ]);

        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            $request->session()->forget('change_password_email');
            return redirect()->route('customer.change-password')->with('error', 'Account not found.');
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $customer->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $customer->update(['password' => $request->password]);
        $keepLoggedIn = $request->boolean('keep_logged_in');
        $request->session()->forget('change_password_email');

        if ($keepLoggedIn) {
            return redirect()->route('customer.my-account')->with('success', 'Your password has been updated. You are still logged in.');
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login')->with('success', 'Your password has been updated. Please log in again.');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->back();
    }

}
