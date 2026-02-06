<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/dashboard.css') }}?v=14">
    <title>Change Password – Quinjay Ice Cream</title>
    <style>
        .account-page { padding: 24px 16px; max-width: 400px; margin: 0 auto; }
        .account-back { display: inline-flex; align-items: center; gap: 6px; color: var(--dash-muted); text-decoration: none; font-weight: 500; margin-bottom: 16px; }
        .account-back:hover { color: var(--dash-text); }
        .change-pw-title { font-size: 20px; font-weight: 700; color: var(--dash-text); margin-bottom: 8px; }
        .change-pw-subtitle { font-size: 14px; color: var(--dash-muted); margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; color: var(--dash-text); margin-bottom: 6px; }
        .form-group input[type="email"] { width: 100%; padding: 12px 14px; font-size: 16px; border: 1px solid var(--dash-border); border-radius: 10px; background: var(--dash-bg); color: var(--dash-text); }
        .form-group input:focus { outline: none; border-color: var(--dash-primary); }
        .form-group .error-text { font-size: 13px; color: #dc2626; margin-top: 4px; }
        .form-group.has-error input { border-color: #dc2626; }
        .btn-primary { display: block; width: 100%; padding: 14px; text-align: center; font-size: 16px; font-weight: 600; color: #fff; border: none; border-radius: 12px; background: var(--dash-primary); cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: var(--dash-primary-hover, #c40018); }
        .alert { padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
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
            <a href="{{ route('customer.my-account') }}" class="account-back">
                <span class="material-symbols-outlined" style="font-size:20px">arrow_back</span> Back
            </a>

            <h1 class="change-pw-title">Change Password</h1>
            <p class="change-pw-subtitle">Enter your email and we'll send a 4-digit verification code to continue.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <form action="{{ route('customer.change-password.send-otp') }}" method="POST">
                @csrf
                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}" required autocomplete="email" placeholder="your@email.com">
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">Send verification code</button>
            </form>
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
