<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FirebaseHelper;
use App\Models\Driver;
use App\Services\FirebaseRealtimeService;
use Illuminate\Http\Request;

class AdminPagesController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function flavors()
    {
        $db = app(FirebaseRealtimeService::class);
        $flavors = collect(FirebaseHelper::toObjects($db->all('flavors', 'created_at', 'desc')));
        $flavorTypes = collect(FirebaseHelper::toObjects($db->where('ingredients', 'type', 'Flavor', 'name', 'asc')));
        return view('admin.flavors', compact('flavors', 'flavorTypes'));
    }

    public function ingredients()
    {
        $db = app(FirebaseRealtimeService::class);
        $ingredients = collect(FirebaseHelper::toObjects($db->all('ingredients', 'name', 'asc')));
        return view('admin.ingredients', compact('ingredients'));
    }

    public function gallon()
    {
        $db = app(FirebaseRealtimeService::class);
        $gallons = collect(FirebaseHelper::toObjects($db->all('gallons', 'size', 'asc')));
        return view('admin.gallon', compact('gallons'));
    }

    public function orders()
    {
        $db = app(FirebaseRealtimeService::class);
        $orders = collect(FirebaseHelper::toObjects($db->all('orders', 'created_at', 'desc')));
        $flavors = collect(FirebaseHelper::toObjects($db->all('flavors', 'name', 'asc')));
        $gallons = collect(FirebaseHelper::toObjects($db->all('gallons', 'size', 'asc')));
        return view('admin.orders', compact('orders', 'flavors', 'gallons'));
    }

    public function drivers()
    {
        $db = app(FirebaseRealtimeService::class);
        $all = $db->all('drivers', 'created_at', 'desc');
        $drivers = collect(FirebaseHelper::toObjects(
            array_values(array_filter($all, fn ($d) => ($d['status'] ?? '') !== Driver::STATUS_DEACTIVATE))
        ));
        return view('admin.drivers', compact('drivers'));
    }

    public function customer()
    {
        return view('admin.customer');
    }

    public function account()
    {
        return view('admin.account');
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function archive()
    {
        return view('admin.archive');
    }

    public function addAdmin()
    {
        return view('admin.add-admin');
    }
}
