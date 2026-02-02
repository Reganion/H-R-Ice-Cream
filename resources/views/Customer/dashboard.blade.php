<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=20">
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
            <a href="{{ $customer ? route('customer.my-account') : route('customer.login') }}" class="header-profile avatar-wrap" aria-label="{{ $customer ? 'My Account' : 'Log in' }}">
                <img src="{{ asset($customer->image ?? 'img/default-user.png') }}" alt="Profile" class="avatar" />
            </a>
        </header>

        <main class="dashboard-main">
            <!-- Best Seller grid: web only (5 cards, most ordered) -->
            <section class="best-seller-grid-section" aria-label="Best Seller">
                <h2 class="best-seller-grid-heading">Best Seller</h2>
                <div class="best-seller-grid">
                    @foreach(collect($bestSellers ?? [])->take(5) as $item)
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

            <!-- 1. Best Seller carousel (mobile only: multiple slides, 5s delay, gap) -->
            <section class="dashboard-section best-seller-section">
                <span class="section-badge best-seller-badge">Best Seller</span>
                <div class="best-seller-carousel" id="best-seller-carousel" aria-roledescription="carousel">
                    <div class="best-seller-carousel-track" id="best-seller-carousel-track">
                        @foreach(collect($bestSellers ?? [])->take(5) as $item)
                        <div class="best-seller-carousel-slide" data-slide="{{ $loop->index }}">
                            <a href="{{ route('customer.order.detail', $item->id) }}" class="best-seller-banner" aria-label="{{ $item->name }}">
                                <div class="best-seller-img-wrap">
                                    <div class="best-seller-overlay"></div>
                                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="best-seller-img" />
                                    <div class="best-seller-info">
                                        <span class="best-seller-label">{{ $item->name }}</span>
                                        <span class="best-seller-rating">
                                            <span class="material-symbols-outlined star-icon">star</span> 5.0
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                        @if(collect($bestSellers ?? [])->isEmpty())
                        <div class="best-seller-carousel-slide" data-slide="0">
                            <a href="#" class="best-seller-banner" aria-label="Best Seller">
                                <div class="best-seller-img-wrap">
                                    <div class="best-seller-overlay"></div>
                                    <img src="{{ asset('img/yummy.png') }}" alt="Best Seller" class="best-seller-img" />
                                    <div class="best-seller-info">
                                        <span class="best-seller-label">Vanilla Flavor</span>
                                        <span class="best-seller-rating">
                                            <span class="material-symbols-outlined star-icon">star</span> 5.0
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- 2. Popular carousel (mobile only: multiple slides, 5s delay, gap) -->
            <section class="dashboard-section popular-section">
                <span class="section-badge popular-badge">Popular</span>
                <div class="popular-carousel" id="popular-carousel" aria-roledescription="carousel">
                    <div class="popular-carousel-track" id="popular-carousel-track">
                        @foreach(collect($popularFlavors ?? [])->take(5) as $item)
                        <div class="popular-carousel-slide" data-slide="{{ $loop->index }}">
                            <a href="{{ route('customer.order.detail', $item->id) }}" class="popular-banner best-seller-banner" aria-label="{{ $item->name }}">
                                <div class="popular-img-wrap best-seller-img-wrap">
                                    <div class="best-seller-overlay"></div>
                                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="best-seller-img" />
                                    <div class="best-seller-info">
                                        <span class="best-seller-label">{{ $item->name }}</span>
                                        <span class="best-seller-rating">
                                            <span class="material-symbols-outlined star-icon">star</span> 5.0
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                        @if(collect($popularFlavors ?? [])->isEmpty())
                        <div class="popular-carousel-slide" data-slide="0">
                            <a href="#" class="popular-banner best-seller-banner" aria-label="Popular">
                                <div class="popular-img-wrap best-seller-img-wrap">
                                    <div class="best-seller-overlay"></div>
                                    <img src="{{ asset('img/yummy.png') }}" alt="Popular" class="best-seller-img" />
                                    <div class="best-seller-info">
                                        <span class="best-seller-label">Matcha Ice cream</span>
                                        <span class="best-seller-rating">
                                            <span class="material-symbols-outlined star-icon">star</span> 5.0
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
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
        (function () {
            var CAROUSEL_GAP = 16;
            var DELAY_MS = 5000;

            function runCarousel(carouselId, trackId) {
                var carousel = document.getElementById(carouselId);
                var track = document.getElementById(trackId);
                if (!carousel || !track) return;
                var slides = track.querySelectorAll('[data-slide]');
                if (slides.length <= 1) return;

                var index = 0;
                function layout() {
                    if (window.innerWidth >= 769) {
                        track.style.width = '';
                        track.style.transform = '';
                        slides.forEach(function (s) { s.style.width = ''; });
                        return;
                    }
                    var w = carousel.offsetWidth;
                    var total = slides.length * w + (slides.length - 1) * CAROUSEL_GAP;
                    track.style.width = total + 'px';
                    slides.forEach(function (s) { s.style.width = w + 'px'; });
                    track.style.transform = 'translateX(-' + index * (w + CAROUSEL_GAP) + 'px)';
                }
                function go() {
                    if (window.innerWidth >= 769) return;
                    index = (index + 1) % slides.length;
                    var w = carousel.offsetWidth;
                    track.style.transform = 'translateX(-' + index * (w + CAROUSEL_GAP) + 'px)';
                }
                layout();
                window.addEventListener('resize', layout);
                setInterval(go, DELAY_MS);
            }

            runCarousel('best-seller-carousel', 'best-seller-carousel-track');
            runCarousel('popular-carousel', 'popular-carousel-track');
        })();
    </script>
</body>

</html>
