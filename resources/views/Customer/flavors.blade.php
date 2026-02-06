<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/flavors.css') }}">
    <title>Flavors – Quinjay Ice Cream</title>
</head>

<body>

    <div class="flavors-page">
        <!-- Top bar: back | Flavors pill | cart -->
        <header class="flavors-header">
            <a href="{{ route('customer.dashboard') }}" class="header-back" aria-label="Back">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="header-title-pill">Flavors</div>
            <a href="#" class="header-cart" aria-label="Cart">
                <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                <span class="cart-badge">0</span>
            </a>
        </header>

        <!-- Search bar -->
        <div class="flavors-search-wrap">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="search" class="flavors-search-input" id="flavors-search" placeholder="Search flavors" aria-label="Search flavors" />
        </div>

        <!-- Category filters (horizontal scroll) -->
        <div class="flavors-filters-wrap">
            <div class="flavors-filters" role="tablist">
                <button type="button" class="filter-pill active" data-filter="all" role="tab">All</button>
                <button type="button" class="filter-pill" data-filter="Plain Flavor" role="tab">Plain Flavors</button>
                <button type="button" class="filter-pill" data-filter="Special Flavor" role="tab">Special Flavors</button>
                <button type="button" class="filter-pill" data-filter="Topping" role="tab">Toppings</button>
            </div>
        </div>

        <!-- Flavor grid -->
        <main class="flavors-main">
            <div class="flavors-grid" id="flavors-grid">
                @foreach ($flavors ?? [] as $flavor)
                    <div class="flavor-card-menu" data-category="{{ $flavor->category ?? '' }}" data-name="{{ strtolower($flavor->name ?? '') }}">
                        <a href="{{ route('customer.order.detail', $flavor->id) }}" class="flavor-card-image-link">
                            <div class="flavor-card-image-wrap">
                                <img src="{{ asset($flavor->mobile_image ?? $flavor->image) }}" alt="{{ $flavor->name }}" class="flavor-card-image" />
                            </div>
                            <h3 class="flavor-card-name">{{ $flavor->name }}</h3>
                            <p class="flavor-card-price">₱{{ number_format($flavor->price ?? 0, 0) }}</p>
                        </a>
                        <button type="button" class="flavor-add-btn" data-flavor-id="{{ $flavor->id }}" aria-label="Add {{ $flavor->name }} to cart">
                            <span class="material-symbols-outlined">add</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </main>
    </div>

    <script>
        (function() {
            var search = document.getElementById('flavors-search');
            var pills = document.querySelectorAll('.filter-pill');
            var cards = document.querySelectorAll('.flavor-card-menu');
            var grid = document.getElementById('flavors-grid');

            function matchesTopping(cat) {
                return (cat || '').indexOf('Topping') !== -1;
            }

            function filterCards() {
                var filter = document.querySelector('.filter-pill.active');
                var filterValue = filter ? filter.getAttribute('data-filter') : 'all';
                var query = (search && search.value) ? search.value.trim().toLowerCase() : '';

                cards.forEach(function(card) {
                    var category = card.getAttribute('data-category') || '';
                    var name = card.getAttribute('data-name') || '';
                    var matchCategory = filterValue === 'all' ||
                        (filterValue === 'Topping' ? matchesTopping(category) : category === filterValue);
                    var matchSearch = !query || name.indexOf(query) !== -1;
                    card.style.display = (matchCategory && matchSearch) ? '' : 'none';
                });
            }

            if (search) {
                search.addEventListener('input', filterCards);
            }

            pills.forEach(function(pill) {
                pill.addEventListener('click', function() {
                    pills.forEach(function(p) { p.classList.remove('active'); });
                    pill.classList.add('active');
                    filterCards();
                });
            });

            document.querySelectorAll('.flavor-add-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var id = this.getAttribute('data-flavor-id');
                    if (id) window.location.href = '{{ url("order") }}/' + id;
                });
            });
        })();
    </script>
</body>

</html>
