<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
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

        // Best Seller: top 5 flavors by order count (most ordered by customers)
        $orderCounts = Order::selectRaw('product_name, count(*) as order_count')
            ->groupBy('product_name')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();
        $bestSellerNames = $orderCounts->pluck('product_name');
        $flavorsByName = Flavor::whereIn('name', $bestSellerNames)->get()->keyBy('name');
        $bestSellers = $orderCounts->map(fn ($row) => $flavorsByName->get($row->product_name))->filter()->values();
        $bestSeller = $bestSellers->first(); // hero/banner: single most ordered

        // Popular: top 5 most rated by customers (from feedback with flavor_id) for carousel
        $popularFlavorIds = \Illuminate\Support\Facades\DB::table('feedback')
            ->whereNotNull('flavor_id')
            ->selectRaw('flavor_id, avg(rating) as avg_rating')
            ->groupBy('flavor_id')
            ->orderByDesc('avg_rating')
            ->limit(5)
            ->pluck('flavor_id');
        $popularFlavors = $popularFlavorIds->map(fn ($id) => Flavor::find($id))->filter()->values();
        if ($popularFlavors->isEmpty() && $bestSellers->count() > 1) {
            $popularFlavors = $bestSellers->skip(1)->take(5)->values();
        }
        if ($popularFlavors->isEmpty()) {
            $popularFlavors = $flavors->take(5)->values();
        }
        $popular = $popularFlavors->first();

        $favorites = collect();
        $customer = null;
        if ($request->session()->has('customer_id')) {
            $customer = Customer::find($request->session()->get('customer_id'));
        }
        return view('Customer.dashboard', compact('flavors', 'bestSellers', 'bestSeller', 'popularFlavors', 'popular', 'favorites', 'customer'));
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

        // Notify admins: always "updated their Profile" for any account information change
        AdminNotification::notifyProfileUpdated($customer->fresh(), 'Profile');

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
