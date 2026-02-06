<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/dashboard.css') }}?v=14">
    <title>My Account – Quinjay Ice Cream</title>
    <style>
        .account-page { padding: 24px 16px; max-width: 400px; margin: 0 auto; }
        .account-avatar-wrap { width: 100px; height: 100px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; }
        .account-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .account-name { font-size: 20px; font-weight: 700; color: var(--dash-text); text-align: center; margin-bottom: 24px; }
        .account-menu { list-style: none; padding: 0; margin: 0; background: var(--dash-bg); border-radius: 12px; overflow: hidden; box-shadow: var(--dash-shadow-sm); }
        .account-menu-item { display: flex; align-items: center; gap: 12px; padding: 16px 18px; text-decoration: none; color: var(--dash-text); border-bottom: 1px solid var(--dash-border); transition: background 0.2s; }
        .account-menu-item:last-child { border-bottom: none; }
        .account-menu-item:hover { background: var(--dash-pill); }
        .account-menu-item .material-symbols-outlined { font-size: 22px; color: var(--dash-muted); }
        .account-menu-item span:last-child { flex: 1; font-weight: 500; }
        .account-menu-item .chevron { font-size: 20px; color: var(--dash-muted); }
        .account-logout { display: block; width: 100%; margin-top: 24px; padding: 14px; text-align: center; font-size: 16px; font-weight: 600; color: var(--dash-primary); border: 2px solid var(--dash-primary); border-radius: 12px; background: transparent; cursor: pointer; text-decoration: none; transition: background 0.2s, color 0.2s; }
        .account-logout:hover { background: var(--dash-primary); color: #fff; }
        .account-back { display: inline-flex; align-items: center; gap: 6px; color: var(--dash-muted); text-decoration: none; font-weight: 500; margin-bottom: 16px; }
        .account-back:hover { color: var(--dash-text); }
    </style>
</head>

<body>

    <div class="dashboard">
        <div class="dashboard-content">
        <header class="dashboard-header">
            <a href="{{ route('customer.dashboard') }}" class="header-logo" aria-label="Home">
                <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="header-logo-img" />
            </a>
            <nav class="header-nav" aria-label="Main navigation">
                <a href="{{ route('customer.dashboard') }}" class="header-nav-link">Home</a>
                <a href="{{ route('customer.order.history') }}" class="header-nav-link">Order</a>
                <a href="{{ route('customer.favorite') }}" class="header-nav-link">Favorite</a>
                <a href="{{ route('customer.messages') }}" class="header-nav-link">Messages</a>
            </nav>
            <div class="header-search">
                <span class="material-symbols-outlined search-icon">search</span>
                <input type="search" class="search-input" placeholder="Search here..." aria-label="Search" />
            </div>
            <a href="#" class="cart-link" aria-label="Cart">
                <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                <span class="cart-badge">0</span>
            </a>
            <a href="{{ route('customer.my-account') }}" class="header-profile avatar-wrap" aria-label="My Account">
                <img src="{{ asset($customer->image ?? 'img/default-user.png') }}" alt="Profile" class="avatar" />
            </a>
        </header>

        <main class="dashboard-main account-page">
            <a href="{{ route('customer.dashboard') }}" class="account-back">
                <span class="material-symbols-outlined" style="font-size:20px">arrow_back</span> Back
            </a>

            <div class="account-avatar-wrap">
                <img src="{{ asset($customer->image ?? 'img/default-user.png') }}" alt="{{ $customer->firstname }} {{ $customer->lastname }}" />
            </div>
            <h1 class="account-name">{{ $customer->firstname }} {{ $customer->lastname }}</h1>

            <ul class="account-menu">
                <li>
                    <a href="{{ route('customer.account-information') }}" class="account-menu-item">
                        <span class="material-symbols-outlined">person</span>
                        <span>Account Information</span>
                        <span class="material-symbols-outlined chevron">chevron_right</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.change-password') }}" class="account-menu-item">
                        <span class="material-symbols-outlined">lock</span>
                        <span>Change Password</span>
                        <span class="material-symbols-outlined chevron">chevron_right</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="account-menu-item">
                        <span class="material-symbols-outlined">delete</span>
                        <span>Delete Account</span>
                        <span class="material-symbols-outlined chevron">chevron_right</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="account-menu-item">
                        <span class="material-symbols-outlined">credit_card</span>
                        <span>Payment Method</span>
                        <span class="material-symbols-outlined chevron">chevron_right</span>
                    </a>
                </li>
            </ul>

            <a href="{{ route('customer.logout') }}" class="account-logout">Log out</a>
        </main>
        </div>

        <nav class="bottom-nav" aria-label="Main navigation">
            <a href="{{ route('customer.dashboard') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">home</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="{{ route('customer.order.history') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">shopping_bag</span>
                <span class="nav-label">Order</span>
            </a>
            <a href="{{ route('customer.favorite') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">favorite</span>
                <span class="nav-label">Favorite</span>
            </a>
            <a href="{{ route('customer.messages') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">chat_bubble</span>
                <span class="nav-label">Messages</span>
            </a>
        </nav>
    </div>

</body>

</html>
