<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Helpers\FirebaseHelper;
use App\Services\FirebaseRealtimeService;
use Illuminate\Http\Request;

class CustomerPageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function home()
    {
        $db = app(FirebaseRealtimeService::class);
        $flavors = collect(FirebaseHelper::toObjects($db->all('flavors', 'created_at', 'desc')));
        $feedbacks = collect(FirebaseHelper::toObjects($db->all('feedback', 'feedback_date', 'desc')));
        return view('Customer.home', compact('flavors', 'feedbacks'));
    }

    public function dashboard()
    {
        $db = app(FirebaseRealtimeService::class);
        $all = $db->all('flavors', 'created_at', 'desc');
        $flavors = collect(FirebaseHelper::toObjects($all));
        $bestSeller = count($all) > 0 ? (object) $all[0] : null;
        $popular = count($all) > 1 ? (object) $all[1] : $bestSeller;
        $favorites = collect();
        return view('Customer.dashboard', compact('flavors', 'bestSeller', 'popular', 'favorites'));
    }

    public function topOrders()
    {
        $db = app(FirebaseRealtimeService::class);
        $flavors = collect(FirebaseHelper::toObjects($db->all('flavors', 'created_at', 'desc')));
        return view('Customer.toporder', compact('flavors'));
    }

    public function orderDetail($id)
    {
        $db = app(FirebaseRealtimeService::class);
        $row = $db->get('flavors', $id);
        if ($row === null) {
            abort(404);
        }
        $flavor = (object) $row;
        return view('Customer.order-detail', compact('flavor'));
    }

    public function flavors()
    {
        $db = app(FirebaseRealtimeService::class);
        $flavors = collect(FirebaseHelper::toObjects($db->all('flavors', 'created_at', 'desc')));
        return view('Customer.flavors', compact('flavors'));
    }

    public function orderHistory()
    {
        $db = app(FirebaseRealtimeService::class);
        $orders = collect(FirebaseHelper::toObjects($db->all('orders', 'created_at', 'desc')));
        return view('Customer.order-history', compact('orders'));
    }

    public function favorite()
    {
        $db = app(FirebaseRealtimeService::class);
        $favorites = collect(FirebaseHelper::toObjects($db->all('flavors', 'created_at', 'desc')));
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
