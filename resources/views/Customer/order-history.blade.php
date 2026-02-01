<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/order-history.css') }}">
    <title>Order History – Quinjay Ice Cream</title>
</head>

<body>

    <div class="order-history-page">
        <div class="order-history-content">
            <!-- Web header: same as dashboard (shown only on desktop) -->
            <header class="order-history-web-header">
                <a href="{{ route('customer.dashboard') }}" class="header-logo" aria-label="Home">
                    <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="header-logo-img" />
                    <span class="header-logo-text"></span>
                </a>
                <nav class="header-nav" aria-label="Main navigation">
                    <a href="{{ route('customer.dashboard') }}" class="header-nav-link">Home</a>
                    <a href="{{ route('customer.order.history') }}" class="header-nav-link active" aria-current="page">Order</a>
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

            <!-- Mobile header: pill -->
            <header class="order-history-header">
                <div class="header-title-pill">Order History</div>
            </header>

            <div class="order-tabs">
                <div class="order-tabs-inner" role="tablist" aria-label="Filter orders by status">
                    <button type="button" class="order-tab active" data-status="all" role="tab" aria-selected="true" aria-controls="order-list">All</button>
                    <button type="button" class="order-tab" data-status="Completed" role="tab" aria-selected="false" aria-controls="order-list">Completed</button>
                    <button type="button" class="order-tab" data-status="Processing" role="tab" aria-selected="false" aria-controls="order-list">Processing</button>
                    <button type="button" class="order-tab" data-status="Cancelled" role="tab" aria-selected="false" aria-controls="order-list">Cancelled</button>
                </div>
            </div>

            <main class="order-history-main">
                <div class="order-list" id="order-list" role="tabpanel" aria-label="Order list">
                    <div class="order-filter-empty" id="order-filter-empty" aria-live="polite" hidden>
                        <p>No <span id="order-filter-empty-label">orders</span> in this filter.</p>
                    </div>
                    @forelse($orders ?? [] as $order)
                    <div class="order-card" data-status="{{ $order->status ?? 'Processing' }}">
                        <div class="order-card-image-wrap">
                            <img src="{{ asset($order->product_image ?? 'img/yummy.png') }}" alt="{{ $order->product_name ?? 'Order' }}" class="order-card-image" />
                        </div>
                        <div class="order-card-body">
                            <h3 class="order-card-name">{{ $order->product_name ?? 'Ice Cream' }}</h3>
                            <p class="order-card-size">{{ $order->gallon_size ?? '4' }} gal</p>
                            <p class="order-card-price">₱{{ number_format($order->amount ?? 0, 0) }}</p>
                            <p class="order-card-qty">Quantity: {{ $order->quantity ?? 1 }}</p>
                            <div class="order-card-actions">
                                @if(($order->status ?? '') === 'Completed')
                                    <button type="button" class="order-btn order-btn-rate">Rate</button>
                                    <button type="button" class="order-btn order-btn-buy">Buy Again</button>
                                @elseif(($order->status ?? '') === 'Processing')
                                    <button type="button" class="order-btn order-btn-cancel">Cancel</button>
                                @else
                                    <button type="button" class="order-btn order-btn-details">Details</button>
                                    <button type="button" class="order-btn order-btn-buy order-btn-buy-blue">Buy Again</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                        <div class="order-empty">
                            <p>No orders yet.</p>
                            <a href="{{ route('customer.dashboard') }}" class="order-empty-link">Browse flavors</a>
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
            <a href="{{ route('customer.order.history') }}" class="nav-item active" aria-current="page">
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
        (function() {
            var tabs = document.querySelectorAll('.order-tab');
            var cards = document.querySelectorAll('.order-card');
            var filterEmpty = document.getElementById('order-filter-empty');
            var filterEmptyLabel = document.getElementById('order-filter-empty-label');

            function filterOrders() {
                var active = document.querySelector('.order-tab.active');
                var status = active ? active.getAttribute('data-status') : 'all';

                var visibleCount = 0;
                cards.forEach(function(card) {
                    var cardStatus = card.getAttribute('data-status') || '';
                    var show = status === 'all' || cardStatus === status;
                    card.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (filterEmpty) {
                    if (status !== 'all' && visibleCount === 0 && cards.length > 0) {
                        if (filterEmptyLabel) filterEmptyLabel.textContent = status.toLowerCase();
                        filterEmpty.hidden = false;
                    } else {
                        filterEmpty.hidden = true;
                    }
                }
            }

            function setActiveTab(activeTab) {
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                activeTab.classList.add('active');
                activeTab.setAttribute('aria-selected', 'true');
                filterOrders();
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    setActiveTab(tab);
                });

                tab.addEventListener('keydown', function(e) {
                    var idx = Array.prototype.indexOf.call(tabs, tab);
                    var next;
                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        next = tabs[idx + 1] || tabs[0];
                        setActiveTab(next);
                        next.focus();
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        next = tabs[idx - 1] || tabs[tabs.length - 1];
                        setActiveTab(next);
                        next.focus();
                    } else if (e.key === 'Home') {
                        e.preventDefault();
                        setActiveTab(tabs[0]);
                        tabs[0].focus();
                    } else if (e.key === 'End') {
                        e.preventDefault();
                        setActiveTab(tabs[tabs.length - 1]);
                        tabs[tabs.length - 1].focus();
                    }
                });
            });

            filterOrders();
        })();
    </script>
</body>

</html>
