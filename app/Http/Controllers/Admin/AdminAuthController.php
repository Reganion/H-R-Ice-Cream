<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function redirectToGoogle(Request $request)
    {
        $clientId = config('services.google.client_id');
        if (empty($clientId)) {
            return redirect()->route('admin.login')
                ->with('error', 'Google sign-in is not configured. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to your .env file.');
        }
        $baseUrl = rtrim(config('app.url'), '/');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if ($host && $this->isPrivateIp($host)) {
            return redirect()->route('admin.login')
                ->with('error', 'Google sign-in does not work with private IP addresses (e.g. 10.x, 192.168.x). Set APP_URL=http://127.0.0.1:8000 in .env and open the site at http://127.0.0.1:8000 when using Google sign-in.');
        }
        $redirectUrl = $baseUrl . '/admin/auth/google/callback';
        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
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

    public function handleGoogleCallback(Request $request)
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $redirectUrl = $baseUrl . '/admin/auth/google/callback';
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->user();
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('admin.login')
                ->with('error', 'Unable to sign in with Google. Please try again.');
        }

        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? 'Admin';
        $avatar = $googleUser->getAvatar();

        $user = User::where('email', $email)->first();

        if ($user) {
            $request->session()->put('admin_id', $user->id);
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
        }

        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] ?? $name;
        $lastName = $parts[1] ?? '';

        $imagePath = null;
        if ($avatar) {
            $dir = public_path('img/admins');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = time() . '_google_' . Str::random(8) . '.jpg';
            $imagePath = 'img/admins/' . $filename;
            $imageData = @file_get_contents($avatar);
            if ($imageData !== false) {
                file_put_contents($dir . '/' . $filename, $imageData);
            } else {
                $imagePath = null;
            }
        }

        $newUser = User::create([
            'name'       => $firstName . ' ' . $lastName,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'image'      => $imagePath,
            'password'   => Hash::make(Str::random(32)),
        ]);

        $request->session()->put('admin_id', $newUser->id);
        return redirect()->route('admin.dashboard')->with('success', 'Account created successfully!');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
