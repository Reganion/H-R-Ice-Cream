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

    public function dashboard(Request $request)
    {
        $now = Carbon::now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $currentYear = (int) $now->year;
        $currentMonth = $now->format('Y-m');
        $availableYears = $this->getDashboardAvailableYears($currentYear);
        $availableMonths = $this->getDashboardAvailableMonths($currentMonth);
        $selectedYear = $currentYear;
        $selectedMonth = $request->query('month', $currentMonth);
        if (!in_array($selectedMonth, $availableMonths, true)) {
            $selectedMonth = $currentMonth;
        }

        $totalOrders = $this->countOrdersBetween($startOfThisMonth, $endOfThisMonth);
        $assignedCount = $this->countOrdersByStatusBetween('assigned', $startOfThisMonth, $endOfThisMonth);
        $pendingCount = $this->countOrdersByStatusBetween('pending', $startOfThisMonth, $endOfThisMonth);
        $completedCount = $this->countOrdersByStatusBetween('completed', $startOfThisMonth, $endOfThisMonth);

        $totalLastMonth = $this->countOrdersBetween($startOfLastMonth, $endOfLastMonth);
        $assignedLastMonth = $this->countOrdersByStatusBetween('assigned', $startOfLastMonth, $endOfLastMonth);
        $pendingLastMonth = $this->countOrdersByStatusBetween('pending', $startOfLastMonth, $endOfLastMonth);
        $completedLastMonth = $this->countOrdersByStatusBetween('completed', $startOfLastMonth, $endOfLastMonth);

        $topSellersForMonth = $this->getTopSellersForMonth($selectedMonth);
        $chartData = $this->getDashboardChartData($selectedYear);
        $topSellersLabels = $topSellersForMonth['topSellersLabels'];
        $topSellersValues = $topSellersForMonth['topSellersValues'];
        $monthlySalesValues = $chartData['monthlySalesValues'];

        return view('admin.dashboard', compact(
            'totalOrders',
            'assignedCount',
            'pendingCount',
            'completedCount',
            'totalLastMonth',
            'assignedLastMonth',
            'pendingLastMonth',
            'completedLastMonth',
            'selectedYear',
            'availableYears',
            'selectedMonth',
            'availableMonths',
            'topSellersLabels',
            'topSellersValues',
            'monthlySalesValues'
        ));
    }

    private function countOrdersBetween(Carbon $startDate, Carbon $endDate): int
    {
        return Order::query()
            ->whereRaw(
                'DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?',
                [$startDate->toDateString(), $endDate->toDateString()]
            )
            ->count();
    }

    private function countOrdersByStatusBetween(string $status, Carbon $startDate, Carbon $endDate): int
    {
        return Order::query()
            ->whereRaw(
                'DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?',
                [$startDate->toDateString(), $endDate->toDateString()]
            )
            ->whereRaw('LOWER(status) = ?', [strtolower($status)])
            ->count();
    }

    public function dashboardChartData(Request $request)
    {
        $now = Carbon::now();
        $currentYear = (int) $now->year;
        $currentMonth = $now->format('Y-m');
        $availableYears = $this->getDashboardAvailableYears($currentYear);
        $availableMonths = $this->getDashboardAvailableMonths($currentMonth);
        $selectedYear = (int) $request->query('year', $currentYear);
        $selectedMonth = (string) $request->query('month', $currentMonth);
        if (!in_array($selectedYear, $availableYears, true)) {
            $selectedYear = $currentYear;
        }
        if (!in_array($selectedMonth, $availableMonths, true)) {
            $selectedMonth = $currentMonth;
        }

        $topSellersForMonth = $this->getTopSellersForMonth($selectedMonth);
        $chartData = $this->getDashboardChartData($selectedYear);

        return response()->json([
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
            'topSellersLabels' => $topSellersForMonth['topSellersLabels'],
            'topSellersValues' => $topSellersForMonth['topSellersValues'],
            'monthlySalesValues' => $chartData['monthlySalesValues'],
        ]);
    }

    public function dashboardCompletedOrders(Request $request)
    {
        $now = Carbon::now();
        $year = (int) $request->query('year', $now->year);
        $month = (int) $request->query('month', $now->month);
        $month = max(1, min(12, $month));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $orders = Order::query()
            ->whereRaw("LOWER(status) IN ('completed', 'delivered')")
            ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [$startOfMonth, $endOfMonth])
            ->orderByRaw('COALESCE(delivery_date, created_at) DESC')
            ->get()
            ->map(function (Order $order) {
                $deliveryDate = $order->delivery_date ? Carbon::parse($order->delivery_date) : null;
                $deliveryTime = $order->delivery_time ? Carbon::parse($order->delivery_time) : null;
                $createdAt = $order->created_at ? Carbon::parse($order->created_at) : null;

                return [
                    'id' => $order->id,
                    'transaction_id' => $order->transaction_id ?? '',
                    'customer_name' => $order->customer_name ?? '',
                    'product_name' => $order->product_name ?? '',
                    'product_type' => $order->product_type ?? '',
                    'gallon_size' => $order->gallon_size ?? '',
                    'delivery_address' => $order->delivery_address ?? '',
                    'customer_phone' => $order->customer_phone ?? '',
                    'customer_image_url' => asset($order->customer_image ?? 'img/default-user.png'),
                    'product_image_url' => asset($order->product_image ?? 'img/default-product.png'),
                    'payment_method' => $order->payment_method ?? '',
                    'amount' => (float) $order->amount,
                    'status' => 'completed',
                    'delivery_date_formatted' => $deliveryDate ? $deliveryDate->format('d M Y') : '—',
                    'delivery_time_formatted' => $deliveryTime ? $deliveryTime->format('h:i A') : '—',
                    'created_at_formatted' => $createdAt ? $createdAt->format('d M Y') : '—',
                ];
            })
            ->values();

        return response()->json([
            'selectedYear' => $year,
            'selectedMonth' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
            'orders' => $orders,
        ]);
    }

    private function getDashboardAvailableYears(int $fallbackYear): array
    {
        $years = Order::query()
            ->selectRaw('YEAR(COALESCE(delivery_date, created_at)) as year_num')
            ->whereNotNull('created_at')
            ->groupByRaw('YEAR(COALESCE(delivery_date, created_at))')
            ->orderByDesc('year_num')
            ->pluck('year_num')
            ->map(fn ($year) => (int) $year)
            ->filter(fn ($year) => $year > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($years)) {
            return [$fallbackYear];
        }

        if (!in_array($fallbackYear, $years, true)) {
            array_unshift($years, $fallbackYear);
        }

        return array_values(array_unique($years));
    }

    private function getDashboardAvailableMonths(string $fallbackMonth): array
    {
        $months = Order::query()
            ->selectRaw("DATE_FORMAT(COALESCE(delivery_date, created_at), '%Y-%m') as month_key")
            ->whereNotNull('created_at')
            ->groupByRaw("DATE_FORMAT(COALESCE(delivery_date, created_at), '%Y-%m')")
            ->orderByDesc('month_key')
            ->pluck('month_key')
            ->map(fn ($m) => (string) $m)
            ->filter(fn ($m) => $m !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($months)) {
            return [$fallbackMonth];
        }

        if (!in_array($fallbackMonth, $months, true)) {
            array_unshift($months, $fallbackMonth);
        }

        return array_values(array_unique($months));
    }

    private function getTopSellersForMonth(string $yearMonth): array
    {
        $date = Carbon::createFromFormat('Y-m', $yearMonth);
        $startOfMonth = $date->copy()->startOfMonth()->toDateString();
        $endOfMonth = $date->copy()->endOfMonth()->toDateString();

        $topSellers = Order::query()
            ->select('product_name')
            ->selectRaw('SUM(COALESCE(qty, 1)) as total_qty')
            ->whereRaw("LOWER(status) IN ('completed', 'delivered')")
            ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [$startOfMonth, $endOfMonth])
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        if ($topSellers->isEmpty()) {
            return [
                'topSellersLabels' => ['No Sales Data'],
                'topSellersValues' => [1],
            ];
        }

        $topSellersLabels = $topSellers
            ->map(function ($row) {
                $name = trim((string) ($row->product_name ?? ''));
                return $name !== '' ? $name : 'Unknown Product';
            })
            ->values()
            ->all();
        $topSellersValues = $topSellers->pluck('total_qty')->map(fn ($v) => (int) $v)->values()->all();

        return [
            'topSellersLabels' => $topSellersLabels,
            'topSellersValues' => $topSellersValues,
        ];
    }

    private function getDashboardChartData(int $year): array
    {
        $startOfYear = Carbon::create($year, 1, 1)->toDateString();
        $endOfYear = Carbon::create($year, 12, 31)->toDateString();

        $topSellers = Order::query()
            ->select('product_name')
            ->selectRaw('SUM(COALESCE(qty, 1)) as total_qty')
            ->whereRaw("LOWER(status) IN ('completed', 'delivered')")
            ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [$startOfYear, $endOfYear])
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

        return [
            'topSellersLabels' => $topSellersLabels,
            'topSellersValues' => $topSellersValues,
            'monthlySalesValues' => $monthlySalesValues,
        ];
    }

    public function flavors(Request $request)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $order = strtolower($request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Flavor::query();

        if ($sortBy === 'price') {
            $query->orderBy('price', $order);
        } elseif ($sortBy === 'status') {
            $query->leftJoin('ingredients', function ($join) {
                $join->on('flavors.flavor_type', '=', 'ingredients.name')
                    ->where('ingredients.type', '=', 'Flavor');
            })
                ->select('flavors.*')
                ->orderByRaw("CASE WHEN ingredients.status = 'available' THEN 0 ELSE 1 END " . $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $flavors = $query->get();
        $flavorTypes = Ingredient::where('type', 'Flavor')->orderBy('name', 'asc')->get();

        return view('admin.flavors', compact('flavors', 'flavorTypes', 'sortBy', 'order'));
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
