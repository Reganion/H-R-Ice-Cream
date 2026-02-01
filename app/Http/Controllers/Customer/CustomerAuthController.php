<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\FirebaseRealtimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class CustomerAuthController extends Controller
{
    // Login customer
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

        $db = app(FirebaseRealtimeService::class);
        $customer = $db->firstWhere('customers', 'email', $request->email);

        if (!$customer || !Hash::check($request->password, $customer['password'] ?? '')) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => 'Invalid email or password.']);
        }

        $request->session()->put('customer_id', $customer['id']);
        return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
    }

    // Register customer
    public function register(Request $request)
    {
        $db = app(FirebaseRealtimeService::class);
        $existing = $db->firstWhere('customers', 'email', $request->email);

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

        $db->add('customers', [
            'firstname'  => $request->firstname,
            'lastname'   => $request->lastname,
            'email'      => $request->email,
            'contact_no' => $request->contact_no ? trim($request->contact_no) : null,
            'password'   => Hash::make($request->password),
        ]);

        return redirect()->route('customer.login')->with('success', 'Account created successfully! Please log in.');
    }

    // Logout
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->back();
    }

    // Redirect to Google OAuth
    public function redirectToGoogle(Request $request)
    {
        $clientId = config('services.google.client_id');
        if (empty($clientId)) {
            return redirect()->route('customer.login')
                ->with('error', 'Google sign-in is not configured. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to your .env file.');
        }
        $baseUrl = rtrim(config('app.url'), '/');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if ($host && $this->isPrivateIp($host)) {
            return redirect()->route('customer.login')
                ->with('error', 'Google sign-in does not work with private IP addresses (e.g. 10.x, 192.168.x). Set APP_URL=http://127.0.0.1:8000 in .env and open the site at http://127.0.0.1:8000 when using Google sign-in.');
        }
        $redirectUrl = $baseUrl . '/customer/auth/google/callback';
        return Socialite::driver('google')->redirectUrl($redirectUrl)->redirect();
    }

    /** @return bool */
    private function isPrivateIp(string $host): bool
    {
        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }
        if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        $long = ip2long($host);
        if ($long === false) {
            return false;
        }
        $private = [
            [ip2long('10.0.0.0'), ip2long('10.255.255.255')],
            [ip2long('172.16.0.0'), ip2long('172.31.255.255')],
            [ip2long('192.168.0.0'), ip2long('192.168.255.255')],
        ];
        foreach ($private as [$start, $end]) {
            if ($long >= $start && $long <= $end) {
                return true;
            }
        }
        return false;
    }

    // Handle Google OAuth callback (login or register)
    public function handleGoogleCallback(Request $request)
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $redirectUrl = $baseUrl . '/customer/auth/google/callback';
        try {
            $googleUser = Socialite::driver('google')->redirectUrl($redirectUrl)->user();
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('customer.login')
                ->with('error', 'Unable to sign in with Google. Please try again.');
        }

        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? 'User';

        $db = app(FirebaseRealtimeService::class);
        $customer = $db->firstWhere('customers', 'email', $email);

        if ($customer) {
            $request->session()->put('customer_id', $customer['id']);
            return redirect()->route('customer.home')->with('success', 'Welcome back!');
        }

        $parts = explode(' ', $name, 2);
        $firstname = $parts[0] ?? $name;
        $lastname = $parts[1] ?? '';

        $db->add('customers', [
            'firstname'  => $firstname,
            'lastname'   => $lastname,
            'email'      => $email,
            'contact_no' => null,
            'password'   => Hash::make(Str::random(32)),
        ]);

        $newCustomer = $db->firstWhere('customers', 'email', $email);
        $request->session()->put('customer_id', $newCustomer['id']);
        return redirect()->route('customer.home')->with('success', 'Account created successfully!');
    }
}
