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
            <div class="dashboard-main-panel"></div>
        </div>
    @endsection

</body>

</html>
