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
use Illuminate\Support\Facades\Cache;

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
        $throughToday = $now->copy()->endOfDay(); // Summary boxes: from start of this month through today only
        // Last month = calendar month before this one (same date logic as bar chart & completed-orders modal)
        $firstDayLastMonth = $now->copy()->startOfMonth()->subMonthNoOverflow();
        $startOfLastMonth = $firstDayLastMonth->copy()->startOfMonth();
        $endOfLastMonth = $firstDayLastMonth->copy()->endOfMonth();
        $currentYear = (int) $now->year;
        $currentMonth = $now->format('Y-m');
        $availableYears = $this->getDashboardAvailableYears($currentYear);
        $availableMonths = $this->getDashboardAvailableMonths($currentMonth);
        $selectedYear = $currentYear;
        $selectedMonth = $request->query('month', $currentMonth);
        if (!in_array($selectedMonth, $availableMonths, true)) {
            $selectedMonth = $currentMonth;
        }

        // Summary boxes: orders from start of this month through today
        $totalOrders = $this->countOrdersBetween($startOfThisMonth, $throughToday);
        $assignedCount = $this->countOrdersByStatusBetween('assigned', $startOfThisMonth, $throughToday);
        $pendingCount = $this->countOrdersByStatusBetween('pending', $startOfThisMonth, $throughToday);
        $completedCount = $this->countOrdersByStatusBetween('completed', $startOfThisMonth, $throughToday);

        // Use same date as bar chart & modal: COALESCE(delivery_date, created_at) so "last month" matches
        $totalLastMonth = $this->countOrdersByOrderDateBetween($startOfLastMonth, $endOfLastMonth);
        $assignedLastMonth = $this->countOrdersByStatusAndOrderDateBetween('assigned', $startOfLastMonth, $endOfLastMonth);
        $pendingLastMonth = $this->countOrdersByStatusAndOrderDateBetween('pending', $startOfLastMonth, $endOfLastMonth);
        $completedLastMonth = $this->countOrdersByStatusAndOrderDateBetween('completed', $startOfLastMonth, $endOfLastMonth);

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

    /**
     * Count orders by created_at in the date range (same as Orders page: "from start of this month").
     * Includes all statuses so Total Orders matches the count on the Orders page.
     */
    private function countOrdersBetween(Carbon $startDate, Carbon $endDate): int
    {
        return Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Count orders by status, using created_at for date range (consistent with Orders page and Total).
     * 'completed' matches both completed and delivered.
     */
    private function countOrdersByStatusBetween(string $status, Carbon $startDate, Carbon $endDate): int
    {
        $status = strtolower(trim($status));
        $query = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($status === 'completed') {
            $query->whereRaw("LOWER(TRIM(status)) IN ('completed', 'delivered')");
        } else {
            $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
        }

        return $query->count();
    }

    /**
     * Count orders by order date (delivery_date or created_at) in the range.
     * Matches bar chart and completed-orders modal so "last month" summary is consistent.
     */
    private function countOrdersByOrderDateBetween(Carbon $startDate, Carbon $endDate): int
    {
        return Order::query()
            ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->count();
    }

    /**
     * Count orders by status, using order date (delivery_date or created_at) for the range.
     * 'completed' matches both completed and delivered. Used for "last month" summary to match bar chart.
     */
    private function countOrdersByStatusAndOrderDateBetween(string $status, Carbon $startDate, Carbon $endDate): int
    {
        $status = strtolower(trim($status));
        $query = Order::query()
            ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);

        if ($status === 'completed') {
            $query->whereRaw("LOWER(TRIM(status)) IN ('completed', 'delivered')");
        } else {
            $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
        }

        return $query->count();
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

        $buildTopSellersQuery = function (bool $onlyFulfilled = true) use ($startOfMonth, $endOfMonth) {
            $query = Order::query()
                ->select('product_name')
                ->selectRaw('SUM(COALESCE(qty, 1)) as total_qty')
                ->whereRaw('DATE(COALESCE(delivery_date, created_at)) BETWEEN ? AND ?', [$startOfMonth, $endOfMonth]);

            if ($onlyFulfilled) {
                $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) IN ('completed', 'delivered')");
            } else {
                // Fallback to real monthly data when no fulfilled orders exist yet.
                $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) <> 'cancelled'");
            }

            return $query
                ->groupBy('product_name')
                ->orderByDesc('total_qty')
                ->limit(3)
                ->get();
        };

        $topSellers = $buildTopSellersQuery(true);
        if ($topSellers->isEmpty()) {
            $topSellers = $buildTopSellersQuery(false);
        }

        $topSellers = $topSellers
            ->filter(function ($row) {
                return trim((string) ($row->product_name ?? '')) !== '';
            })
            ->values();

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
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(status)) = 'pending' THEN 1
                    WHEN LOWER(TRIM(status)) = 'preparing' THEN 2
                    WHEN LOWER(TRIM(status)) IN ('walk_in', 'walk-in', 'walk in', 'walkin') THEN 3
                    WHEN LOWER(TRIM(status)) = 'assigned' THEN 4
                    WHEN LOWER(TRIM(status)) = 'ready' THEN 5
                    WHEN LOWER(TRIM(status)) IN ('out for delivery', 'out_for_delivery') THEN 6
                    WHEN LOWER(TRIM(status)) IN ('completed', 'delivered') THEN 7
                    WHEN LOWER(TRIM(status)) = 'cancelled' THEN 8
                    ELSE 9
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();
        $flavors = Flavor::orderBy('name', 'asc')->get();
        $gallons = Gallon::orderBy('size', 'asc')->get();
        return view('admin.orders', compact('orders', 'flavors', 'gallons'));
    }

    public function records()
    {
        return view('admin.records');
    }

    public function drivers()
    {
        $drivers = Driver::whereIn('status', [
                Driver::STATUS_AVAILABLE,
                Driver::STATUS_ON_ROUTE,
                Driver::STATUS_OFF_DUTY,
                Driver::STATUS_DEACTIVATE,
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $onlineKeyPrefix = 'api_driver_online:';
        $drivers->each(function (Driver $driver) use ($onlineKeyPrefix): void {
            if (
                $driver->status === Driver::STATUS_DEACTIVATE ||
                $driver->status === Driver::STATUS_ARCHIVE ||
                $driver->status === Driver::STATUS_ON_ROUTE
            ) {
                return;
            }

            $isOnline = Cache::has($onlineKeyPrefix . $driver->id);
            $expectedStatus = $isOnline ? Driver::STATUS_AVAILABLE : Driver::STATUS_OFF_DUTY;

            if ($driver->status !== $expectedStatus) {
                $driver->status = $expectedStatus;
                $driver->save();
            }
        });

        $drivers = $drivers->fresh();

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
        $drivers = Driver::where('status', Driver::STATUS_ARCHIVE)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.archive', compact('drivers'));
    }

    public function addAdmin()
    {
        return view('admin.add-admin');
    }
}
