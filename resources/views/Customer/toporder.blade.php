<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/flavors.css') }}">
    <title>Order – Quinjay Ice Cream</title>
</head>

<body>

    <div class="flavors-page">
        <header class="flavors-header">
            <a href="{{ route('customer.dashboard') }}" class="header-back" aria-label="Back">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="header-title-pill">Order</div>
            <a href="#" class="header-cart" aria-label="Cart">
                <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                <span class="cart-badge">0</span>
            </a>
        </header>

        <main class="flavors-main">
            <div class="flavors-grid" id="flavors-grid">
                @foreach ($flavors ?? [] as $flavor)
                    <div class="flavor-card-menu">
                        <div class="flavor-card-image-wrap">
                            <img src="{{ asset($flavor->image) }}" alt="{{ $flavor->name }}" class="flavor-card-image" />
                        </div>
                        <h3 class="flavor-card-name">{{ $flavor->name }}</h3>
                        <p class="flavor-card-price">₱{{ number_format($flavor->price ?? 0, 0) }}</p>
                        <button type="button" class="flavor-add-btn" data-flavor-id="{{ $flavor->id }}" aria-label="Add {{ $flavor->name }} to cart">
                            <span class="material-symbols-outlined">add</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('.flavor-add-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-flavor-id');
                if (id) {
                    // Add to cart / proceed – for now go to dashboard
                    window.location.href = '{{ route("customer.dashboard") }}';
                }
            });
        });
    </script>
</body>

</html>
