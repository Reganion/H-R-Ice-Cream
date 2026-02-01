<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Flavor;
use App\Models\Gallon;
use App\Models\Ingredient;
use App\Models\Order;
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
        $flavors = Flavor::orderBy('created_at', 'desc')->get();
        $flavorTypes = Ingredient::where('type', 'Flavor')->orderBy('name', 'asc')->get();
        return view('admin.flavors', compact('flavors', 'flavorTypes'));
    }

    public function ingredients()
    {
        $ingredients = Ingredient::orderBy('name', 'asc')->get();
        return view('admin.ingredients', compact('ingredients'));
    }

    public function gallon()
    {
        $gallons = Gallon::orderBy('size', 'asc')->get();
        return view('admin.gallon', compact('gallons'));
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        $flavors = Flavor::orderBy('name', 'asc')->get();
        $gallons = Gallon::orderBy('size', 'asc')->get();
        return view('admin.orders', compact('orders', 'flavors', 'gallons'));
    }

    public function drivers()
    {
        $drivers = Driver::where('status', '!=', Driver::STATUS_DEACTIVATE)
            ->orderBy('created_at', 'desc')
            ->get();
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
