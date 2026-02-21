@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
    @section('title', 'Dashboard')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">


    <link rel="stylesheet" href="{{ asset('assets/css/Admin/dashboard.css') }}">
</head>

<body>
    @section('content')
        @php
            $topSellersLabelsData = $topSellersLabels ?? ['No Sales Data'];
            $topSellersValuesData = $topSellersValues ?? [1];
            $monthlySalesValuesData = $monthlySalesValues ?? array_fill(0, 12, 0);
        @endphp
        <div class="dashboard-page">

            <!-- SUMMARY BOXES -->
            <div class="summary-boxes">
                <div class="summary-box">
                    <h4>Total Orders</h4>
                    <h2>{{ $totalOrders ?? 0 }} <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: {{ $totalLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Assigned Orders</h4>
                    <h2>{{ $assignedCount ?? 0 }} <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: {{ $assignedLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Pending Orders</h4>
                    <h2>{{ $pendingCount ?? 0 }} <span class="icon" style="background:#f59e0b"></span></h2>
                    <p>Last month: {{ $pendingLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Delivered Orders</h4>
                    <h2>{{ $deliveredCount ?? 0 }} <span class="icon" style="background:#22c55e"></span></h2>
                    <p>Last month: {{ $deliveredLastMonth ?? 0 }}</p>
                </div>
            </div>

            <!-- MAIN PANEL -->
            <div class="dashboard-main-panel">
                <div class="main-panel-grid">
                    <div class="panel-section chart-panel">
                        <h4>Top 3 Best Sellers</h4>
                        <div class="chart-canvas-wrap">
                            <canvas id="topSellersPieChart"></canvas>
                        </div>
                    </div>
                    <div class="panel-section chart-panel">
                        <h4>Monthly Sales (Jan - Dec)</h4>
                        <div class="chart-canvas-wrap">
                            <canvas id="monthlySalesBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function() {
                if (typeof Chart === 'undefined') return;
                const isPhone = window.matchMedia('(max-width: 480px)').matches;

                const topSellersLabels = @json($topSellersLabelsData);
                const topSellersValues = @json($topSellersValuesData);

                const monthlySalesLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const monthlySalesValues = (@json($monthlySalesValuesData) || []).map(v => Number(v) || 0);
                const yStep = 5000;
                const monthlyMaxValue = Math.max(...monthlySalesValues, 0);
                const yAxisMax = Math.max(20000, Math.ceil(monthlyMaxValue / yStep) * yStep);

                const pieEl = document.getElementById('topSellersPieChart');
                if (pieEl) {
                    new Chart(pieEl, {
                        type: 'pie',
                        data: {
                            labels: topSellersLabels,
                            datasets: [{
                                data: topSellersValues,
                                backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: isPhone ? 10 : 14,
                                        font: {
                                            size: isPhone ? 10 : 12
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                const barEl = document.getElementById('monthlySalesBarChart');
                if (barEl) {
                    new Chart(barEl, {
                        type: 'bar',
                        data: {
                            labels: monthlySalesLabels,
                            datasets: [{
                                label: 'Sales',
                                data: monthlySalesValues,
                                backgroundColor: '#3b82f6',
                                borderRadius: 6,
                                maxBarThickness: 28
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: yAxisMax,
                                    ticks: {
                                        stepSize: yStep,
                                        callback: function(value) {
                                            return '₱' + Number(value).toLocaleString('en-PH');
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: isPhone ? 50 : 0,
                                        minRotation: isPhone ? 50 : 0,
                                        font: {
                                            size: isPhone ? 10 : 12
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            })();
        </script>
    @endsection

</body>

</html>
