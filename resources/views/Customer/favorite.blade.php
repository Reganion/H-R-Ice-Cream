<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/favorite.css') }}">
    <title>My Favorite List – Quinjay Ice Cream</title>
</head>

<body>

    <div class="favorite-page">
        <div class="favorite-content">
            <!-- Web header: same as dashboard (shown only on desktop) -->
            <header class="favorite-web-header">
                <a href="{{ route('customer.dashboard') }}" class="header-logo" aria-label="Home">
                    <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="header-logo-img" />
                    <span class="header-logo-text"></span>
                </a>
                <nav class="header-nav" aria-label="Main navigation">
                    <a href="{{ route('customer.dashboard') }}" class="header-nav-link">Home</a>
                    <a href="{{ route('customer.order.history') }}" class="header-nav-link">Order</a>
                    <a href="{{ route('customer.favorite') }}" class="header-nav-link active" aria-current="page">Favorite</a>
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
                <a href="{{ route('customer.dashboard') }}" class="header-profile avatar-wrap" aria-label="Profile">
                    <img src="{{ asset('img/default-user.png') }}" alt="Profile" class="avatar" />
                </a>
            </header>

            <!-- Mobile header: back | pill | cart -->
            <header class="favorite-header">
                <a href="{{ route('customer.dashboard') }}" class="favorite-header-back" aria-label="Back">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div class="favorite-header-pill">My Favorite List</div>
                <a href="#" class="favorite-header-cart" aria-label="Cart">
                    <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                </a>
            </header>

            <main class="favorite-main">
                <div class="favorite-grid" id="favorite-grid">
                    @forelse($favorites ?? [] as $item)
                        <a href="{{ route('customer.order.detail', $item->id) }}" class="favorite-card" aria-label="{{ $item->name }}">
                            <div class="favorite-card-img-wrap">
                                <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="favorite-card-img" />
                            </div>
                            <h3 class="favorite-card-name">{{ $item->name }}</h3>
                            <p class="favorite-card-price">₱{{ number_format($item->price ?? 0, 0) }}</p>
                            <span class="favorite-card-heart material-symbols-outlined" aria-hidden="true">favorite</span>
                        </a>
                    @empty
                        <div class="favorite-empty">
                            <p>No favorites yet.</p>
                            <a href="{{ route('customer.dashboard') }}" class="favorite-empty-link">Browse flavors</a>
                        </div>
                    @endforelse
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
            <a href="{{ route('customer.favorite') }}" class="nav-item active" aria-current="page">
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
