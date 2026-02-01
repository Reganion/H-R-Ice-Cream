<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=13">
    <title>Dashboard – Quinjay Ice Cream</title>
</head>

<body>

    <div class="dashboard">
        <div class="dashboard-content">
        <!-- Header: mobile = profile | search | cart | web = logo | nav | search | cart | profile -->
        <header class="dashboard-header">
            <a href="{{ route('customer.dashboard') }}" class="header-logo" aria-label="Home">
                <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="header-logo-img" />
                <span class="header-logo-text"></span>
            </a>
            <nav class="header-nav" aria-label="Main navigation">
                <a href="{{ route('customer.dashboard') }}" class="header-nav-link active" aria-current="page">Home</a>
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
            <a href="{{ route('customer.dashboard') }}" class="header-profile avatar-wrap" aria-label="Profile">
                <img src="{{ asset('img/default-user.png') }}" alt="Profile" class="avatar" />
            </a>
        </header>

        <main class="dashboard-main">
            <!-- Best Seller grid: web only (4 cards with Order Now) -->
            <section class="best-seller-grid-section" aria-label="Best Seller">
                <h2 class="best-seller-grid-heading">Best Seller</h2>
                <div class="best-seller-grid">
                    @foreach(collect($flavors ?? [])->take(4) as $item)
                        <article class="best-seller-card">
                            <div class="best-seller-card-img-wrap">
                                <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="best-seller-card-img" />
                            </div>
                            <h3 class="best-seller-card-name">{{ $item->name }}</h3>
                            <p class="best-seller-card-rating">
                                <span class="material-symbols-outlined best-seller-card-star">star</span>
                                5.0 (Reviews)
                            </p>
                            <p class="best-seller-card-price">₱{{ number_format($item->price ?? 0, 0) }}</p>
                            <a href="{{ route('customer.order.detail', $item->id) }}" class="btn-order-now">Order Now</a>
                        </article>
                    @endforeach
                </div>
            </section>

            <!-- 1. Best Seller (hero section) – mobile -->
            <section class="dashboard-section best-seller-section">
                <span class="section-badge best-seller-badge">Best Seller</span>
                <a href="{{ ($bestSeller ?? null) ? route('customer.order.detail', $bestSeller->id) : '#' }}" class="best-seller-banner" aria-label="View best seller">
                    <div class="best-seller-img-wrap">
                        <div class="best-seller-overlay"></div>
                        @if ($bestSeller ?? null)
                            <img src="{{ asset($bestSeller->image) }}" alt="{{ $bestSeller->name }}" class="best-seller-img" />
                            <div class="best-seller-info">
                                <span class="best-seller-label">{{ $bestSeller->name }}</span>
                                <span class="best-seller-rating">
                                    <span class="material-symbols-outlined star-icon">star</span> 5.0
                                </span>
                            </div>
                        @else
                            <img src="{{ asset('img/yummy.png') }}" alt="Best Seller" class="best-seller-img" />
                            <div class="best-seller-info">
                                <span class="best-seller-label">Vanilla Flavor</span>
                                <span class="best-seller-rating">
                                    <span class="material-symbols-outlined star-icon">star</span> 5.0
                                </span>
                            </div>
                        @endif
                    </div>
                </a>
            </section>

            <!-- 2. Popular (single featured card) -->
            <section class="dashboard-section popular-section">
                <h2 class="section-heading">Popular</h2>
                <a href="{{ $popular ? route('customer.order.detail', $popular->id) : '#' }}" class="popular-card" aria-label="{{ $popular->name ?? 'Popular item' }}">
                    <div class="popular-card-image-wrap">
                        @if ($popular ?? null)
                            <img src="{{ asset($popular->image) }}" alt="{{ $popular->name }}" class="popular-card-image" />
                        @else
                            <img src="{{ asset('img/yummy.png') }}" alt="Popular" class="popular-card-image" />
                        @endif
                    </div>
                    <h3 class="popular-card-name">{{ $popular->name ?? 'Matcha Ice cream' }}</h3>
                    <div class="popular-card-price-row">
                        <span class="popular-card-price">₱{{ $popular ? number_format($popular->price ?? 0, 0) : '1,700' }}</span>
                        <span class="popular-card-rating">
                            <span class="material-symbols-outlined popular-star">star</span> 5.0
                        </span>
                    </div>
                </a>
            </section>

            <!-- 3. Flavors (horizontal scroll) -->
            <section class="dashboard-section flavors-section" id="flavors-section">
                <div class="section-heading-row">
                    <h2 class="section-heading">Flavors</h2>
                    <a href="{{ route('customer.flavors') }}" class="see-all-link" id="flavors-see-all">See All</a>
                </div>
                <div class="flavors-scroll-wrap">
                    <div class="flavors-scroll">
                        @foreach ($flavors ?? [] as $flavor)
                            <a href="{{ route('customer.order.detail', $flavor->id) }}" class="flavor-card-horizontal" data-flavor-id="{{ $flavor->id }}">
                                <div class="flavor-card-h-img-wrap">
                                    <img src="{{ asset($flavor->image) }}" alt="{{ $flavor->name }}" class="flavor-card-h-img" />
                                </div>
                                <h3 class="flavor-card-h-name">{{ $flavor->name }}</h3>
                                <div class="flavor-card-h-price-row">
                                    <span class="flavor-card-h-price">₱{{ number_format($flavor->price ?? 0, 0) }}</span>
                                    <span class="flavor-card-h-rating">
                                        <span class="material-symbols-outlined flavor-star">star</span> 5.0
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>
        </div>

        <!-- Bottom navigation: mobile only -->
        <nav class="bottom-nav" aria-label="Main navigation">
            <a href="{{ route('customer.dashboard') }}" class="nav-item active" aria-current="page">
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

    <script>
        // Flavor cards are links; no extra script needed
    </script>
</body>

</html>
