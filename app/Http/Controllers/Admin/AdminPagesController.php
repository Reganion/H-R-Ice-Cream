<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Flavor;
use App\Models\Gallon;
use App\Models\Ingredient;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminPagesController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function dashboard()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $orders = Order::orderBy('created_at', 'desc')->get();
        $fiveMinutesAgo = $now->copy()->subMinutes(5);

        $totalOrders = $orders->count();
        $assignedCount = $orders->where('status', 'assigned')->count();
        $pendingCount = $orders->where('status', 'pending')->filter(fn ($o) => $o->created_at && $o->created_at->lt($fiveMinutesAgo))->count();
        $deliveredCount = $orders->where('status', 'delivered')->count();

        $ordersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->get();
        $totalLastMonth = $ordersLastMonth->count();
        $assignedLastMonth = $ordersLastMonth->where('status', 'assigned')->count();
        $pendingLastMonth = $ordersLastMonth->where('status', 'pending')->count();
        $deliveredLastMonth = $ordersLastMonth->where('status', 'delivered')->count();

        return view('admin.dashboard', compact(
            'orders',
            'totalOrders',
            'assignedCount',
            'pendingCount',
            'deliveredCount',
            'totalLastMonth',
            'assignedLastMonth',
            'pendingLastMonth',
            'deliveredLastMonth'
        ));
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
        $orders = Order::query()
            ->orderByRaw("
                CASE
                    WHEN LOWER(status) = 'pending' THEN 1
                    WHEN LOWER(status) = 'walk_in' THEN 2
                    WHEN LOWER(status) = 'assigned' THEN 3
                    WHEN LOWER(status) IN ('completed', 'delivered') THEN 4
                    WHEN LOWER(status) = 'cancelled' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();
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
