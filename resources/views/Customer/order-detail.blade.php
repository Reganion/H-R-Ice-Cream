<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/order-detail.css') }}">
    <title>Order {{ $flavor->name }} – Quinjay Ice Cream</title>
</head>

<body>

    <div class="order-detail-page">
        <header class="order-detail-header">
            <a href="{{ url()->previous() ?: route('customer.dashboard') }}" class="header-back" aria-label="Back">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <a href="#" class="header-cart" aria-label="Cart">
                <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                <span class="cart-badge">0</span>
            </a>
        </header>

        <main class="order-detail-main">
            <div class="order-product-image-wrap">
                <img src="{{ asset($flavor->image) }}" alt="{{ $flavor->name }}" class="order-product-image" />
            </div>

            <div class="order-product-info">
                <div class="order-title-row">
                    <h1 class="order-product-name">{{ $flavor->name }}</h1>
                </div>
                <div class="order-meta-row">
                    <span class="order-category">{{ $flavor->category ?? 'Special Flavors' }}</span>
                    <span class="order-rating">
                        <span class="material-symbols-outlined order-star">star</span> 5.0
                    </span>
                </div>
            </div>

            <div class="order-size-section">
                <p class="order-label">Select Gallon size:</p>
                <div class="order-size-buttons" id="size-buttons">
                    <button type="button" class="size-btn" data-size="2">2 gal</button>
                    <button type="button" class="size-btn" data-size="3">3 gal</button>
                    <button type="button" class="size-btn active" data-size="3.5">3.5 gal</button>
                    <button type="button" class="size-btn" data-size="4">4 gal</button>
                    <button type="button" class="size-btn" data-size="5">5 gal</button>
                    <button type="button" class="size-btn" data-size="7">7 gal</button>
                </div>
            </div>

            <div class="order-quantity-row">
                <p class="order-label">Quantity</p>
                <div class="order-quantity-control">
                    <button type="button" class="qty-btn minus" id="qty-minus" aria-label="Decrease">−</button>
                    <span class="qty-value" id="qty-value">0</span>
                    <button type="button" class="qty-btn plus" id="qty-plus" aria-label="Increase">+</button>
                </div>
                <div class="order-subtotal-wrap">
                    <span class="order-subtotal-label">Subtotal</span>
                    <span class="order-subtotal" id="subtotal">₱0</span>
                </div>
            </div>

            <div class="order-actions">
                <a href="#" class="order-checkout-btn" id="checkout-btn">Check Out</a>
                <button type="button" class="order-add-btn" id="add-cart-btn" aria-label="Add to cart">
                    <span class="material-symbols-outlined">add</span>
                </button>
            </div>
        </main>
    </div>

    <script>
        (function() {
            var basePrice = {{ $flavor->price ?? 0 }};
            var qtyEl = document.getElementById('qty-value');
            var subtotalEl = document.getElementById('subtotal');
            var qty = 0;

            function updateSubtotal() {
                var total = basePrice * qty;
                qtyEl.textContent = qty;
                subtotalEl.textContent = '₱' + total.toLocaleString('en-PH', { maximumFractionDigits: 0 });
            }

            document.getElementById('qty-plus').addEventListener('click', function() {
                qty++;
                updateSubtotal();
            });
            document.getElementById('qty-minus').addEventListener('click', function() {
                if (qty > 0) qty--;
                updateSubtotal();
            });

            document.querySelectorAll('.size-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.size-btn').forEach(function(b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                });
            });

            document.getElementById('checkout-btn').addEventListener('click', function(e) {
                e.preventDefault();
                if (qty > 0) {
                    window.location.href = '{{ route("customer.dashboard") }}';
                }
            });
            document.getElementById('add-cart-btn').addEventListener('click', function() {
                qty++;
                updateSubtotal();
            });

            updateSubtotal();
        })();
    </script>
</body>

</html>
