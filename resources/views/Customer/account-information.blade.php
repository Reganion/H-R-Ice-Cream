<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/dashboard.css') }}?v=14">
    <title>Account Information – Quinjay Ice Cream</title>
    <style>
        .account-info-page { padding: 24px 16px; max-width: 400px; margin: 0 auto; }
        .account-info-avatar { width: 100px; height: 100px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; }
        .account-info-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .account-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .account-info-field { margin-bottom: 16px; }
        .account-info-field.full { grid-column: 1 / -1; }
        .account-info-field label { display: block; font-size: 12px; font-weight: 600; color: var(--dash-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.03em; }
        .account-info-field .value { padding: 12px 14px; background: var(--dash-pill); border: 1px solid var(--dash-border); border-radius: 10px; font-size: 15px; color: var(--dash-text); }
        .account-info-actions { margin-top: 24px; display: flex; justify-content: flex-end; }
        .btn-edit-profile { padding: 12px 24px; font-size: 15px; font-weight: 600; color: #fff; background: #2563eb; border: none; border-radius: 10px; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.2s; }
        .btn-edit-profile:hover { background: #1d4ed8; }
        .account-info-back { display: inline-flex; align-items: center; gap: 6px; color: var(--dash-muted); text-decoration: none; font-weight: 500; margin-bottom: 16px; }
        .account-info-back:hover { color: var(--dash-text); }
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

        <main class="dashboard-main account-info-page">
            @if (session('success'))
                <div style="margin-bottom:1rem;padding:0.75rem;background:#d4edda;color:#155724;border-radius:8px;">{{ session('success') }}</div>
            @endif
            <a href="{{ route('customer.my-account') }}" class="account-info-back">
                <span class="material-symbols-outlined" style="font-size:20px">arrow_back</span> Back
            </a>

            <div class="account-info-avatar">
                <img src="{{ asset($customer->image ?? 'img/default-user.png') }}" alt="{{ $customer->firstname }} {{ $customer->lastname }}" />
            </div>

            <div class="account-info-grid">
                <div class="account-info-field">
                    <label>First name</label>
                    <div class="value">{{ $customer->firstname }}</div>
                </div>
                <div class="account-info-field">
                    <label>Last name</label>
                    <div class="value">{{ $customer->lastname }}</div>
                </div>
                <div class="account-info-field full">
                    <label>Phone number</label>
                    <div class="value">{{ $customer->contact_no ?? '—' }}</div>
                </div>
                <div class="account-info-field full">
                    <label>Email address</label>
                    <div class="value">{{ $customer->email }}</div>
                </div>
            </div>

            <div class="account-info-actions">
                <a href="{{ route('customer.edit-profile') }}" class="btn-edit-profile">Edit Profile</a>
            </div>
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
