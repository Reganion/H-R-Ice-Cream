<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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

    public function dashboard()
    {
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        $bestSeller = $flavors->first();
        $popular = $flavors->skip(1)->first() ?? $bestSeller;
        $favorites = collect();
        return view('Customer.dashboard', compact('flavors', 'bestSeller', 'popular', 'favorites'));
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
