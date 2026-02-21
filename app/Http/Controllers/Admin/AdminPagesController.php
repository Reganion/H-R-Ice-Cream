<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $startOfYear = $now->copy()->startOfYear()->toDateString();
        $endOfYear = $now->copy()->endOfYear()->toDateString();

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

        // Top 3 best sellers by ordered quantity (excluding cancelled orders)
        $topSellers = Order::query()
            ->select('product_name')
            ->selectRaw('SUM(COALESCE(qty, 1)) as total_qty')
            ->whereRaw("LOWER(status) IN ('completed', 'delivered')")
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        if ($topSellers->isEmpty()) {
            $topSellersLabels = ['No Sales Data'];
            $topSellersValues = [1];
        } else {
            $topSellersLabels = $topSellers
                ->map(function ($row) {
                    $name = trim((string) ($row->product_name ?? ''));
                    return $name !== '' ? $name : 'Unknown Product';
                })
                ->values()
                ->all();
            $topSellersValues = $topSellers->pluck('total_qty')->map(fn ($v) => (int) $v)->values()->all();
        }

        // Monthly sales Jan-Dec for current year based on delivery date.
        // Fallback to created_at when delivery_date is missing.
        $salesByMonth = Order::query()
            ->selectRaw('MONTH(COALESCE(delivery_date, created_at)) as month_num')
            ->selectRaw('SUM(COALESCE(amount, 0)) as total_sales')
            ->whereRaw("LOWER(status) IN ('completed', 'delivered')")
            ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [$startOfYear, $endOfYear])
            ->groupByRaw('MONTH(COALESCE(delivery_date, created_at))')
            ->pluck('total_sales', 'month_num');

        $monthlySalesValues = collect(range(1, 12))
            ->map(fn ($month) => (int) round((float) ($salesByMonth[$month] ?? 0)))
            ->values()
            ->all();

        return view('admin.dashboard', compact(
            'orders',
            'totalOrders',
            'assignedCount',
            'pendingCount',
            'deliveredCount',
            'totalLastMonth',
            'assignedLastMonth',
            'pendingLastMonth',
            'deliveredLastMonth',
            'topSellersLabels',
            'topSellersValues',
            'monthlySalesValues'
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
        $customers = Customer::with(['addresses' => function ($query) {
            $query->orderByDesc('is_default')->orderByDesc('created_at');
        }])->orderBy('created_at', 'desc')->get();

        return view('admin.customer', compact('customers'));
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
