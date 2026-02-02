<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\Flavor;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerPageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function home()
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        $feedbacks = Feedback::orderBy('feedback_date', 'desc')->get();
        return view('Customer.home', compact('flavors', 'feedbacks'));
    }

    public function dashboard(Request $request)
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        $bestSeller = $flavors->first();
        $popular = $flavors->skip(1)->first() ?? $bestSeller;
        $favorites = collect();
        $customer = null;
        if ($request->session()->has('customer_id')) {
            $customer = Customer::find($request->session()->get('customer_id'));
        }
        return view('Customer.dashboard', compact('flavors', 'bestSeller', 'popular', 'favorites', 'customer'));
    }

    public function myAccount(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in to view your account.');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Session expired. Please log in again.');
        }
        return view('Customer.my-account', compact('customer'));
    }

    public function accountInformation(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in to view your account information.');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Session expired. Please log in again.');
        }
        return view('Customer.account-information', compact('customer'));
    }

    public function editProfile(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in to edit your profile.');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Session expired. Please log in again.');
        }
        return view('Customer.edit-profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $customerId = $request->session()->get('customer_id');
        if (!$customerId) {
            return redirect()->route('customer.login')->with('error', 'Please log in to update your profile.');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            $request->session()->forget('customer_id');
            return redirect()->route('customer.login')->with('error', 'Session expired. Please log in again.');
        }

        $request->validate([
            'firstname'  => 'required|string|max:50',
            'lastname'   => 'required|string|max:50',
            'contact_no' => 'nullable|string|max:20|regex:/^[\d\s\-+()]+$/',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'firstname.required' => 'First name is required.',
            'lastname.required'   => 'Last name is required.',
            'image.image'         => 'The file must be an image.',
            'image.max'           => 'The image may not be greater than 2MB.',
        ]);

        $data = [
            'firstname'  => $request->firstname,
            'lastname'   => $request->lastname,
            'contact_no' => $request->filled('contact_no') ? trim($request->contact_no) : null,
        ];

        if ($request->hasFile('image')) {
            $dir = public_path('img/customers');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('image');
            $name = 'customer_' . $customer->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            $data['image'] = 'img/customers/' . $name;
        }

        $customer->update($data);

        return redirect()->route('customer.account-information')->with('success', 'Profile updated successfully.');
    }

    public function topOrders()
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        return view('Customer.toporder', compact('flavors'));
    }

    public function orderDetail($id)
    {
        $flavor = Flavor::findOrFail($id);
        return view('Customer.order-detail', compact('flavor'));
    }

    public function flavors()
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        return view('Customer.flavors', compact('flavors'));
    }

    public function orderHistory()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        return view('Customer.order-history', compact('orders'));
    }

    public function favorite()
    {
        $favorites = Flavor::orderBy('created_at', 'desc')->get();
        return view('Customer.favorite', compact('favorites'));
    }

    public function messages()
    {
        $chats = collect([
            (object)['id' => 1, 'sender' => '+63 9123456789', 'preview' => "Good day! Ma'am, I'm on at location.", 'time' => '4 hours ago'],
            (object)['id' => 2, 'sender' => 'Chat Assistant', 'preview' => 'Yes! This is Available.', 'time' => '5 hours ago'],
        ]);
        $notifications = collect([
            (object)['message' => 'Your order Strawberry has been successfully delivered', 'time' => '1 minute ago'],
            (object)['message' => 'Your order Mango Graham has been cancelled', 'time' => '4 hours ago'],
            (object)['message' => 'Your personal has been updated', 'time' => '4:15pm'],
            (object)['message' => 'Your personal has been updated', 'time' => '05 November'],
        ]);
        $notificationsUnreadCount = 1;
        return view('Customer.messages', compact('chats', 'notifications', 'notificationsUnreadCount'));
    }

    public function chat($id)
    {
        $chat = (object)[
            'id' => $id,
            'sender' => $id == 1 ? '+639123456789' : 'Chat Assistant',
            'subtitle' => 'Unknown',
        ];
        $messages = collect([
            (object)['incoming' => true, 'text' => "Good day! Ma'am I'm at a location.", 'time' => '12:30 pm'],
            (object)['incoming' => false, 'text' => 'Okay! On the way.', 'time' => '12:31 pm'],
        ]);
        return view('Customer.chat', compact('chat', 'messages'));
    }

    public function about()
    {
        return view('customer.aboutus');
    }

    public function Customerlogin()
    {
        return view('customer.login');
    }

    public function register()
    {
        return view('customer.register');
    }
}
