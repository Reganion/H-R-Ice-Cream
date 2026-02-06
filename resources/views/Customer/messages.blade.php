<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/messages.css') }}">
    <title>Messages – Quinjay Ice Cream</title>
</head>

<body>

    <div class="messages-page">
        <div class="messages-content">
            <!-- Web header (desktop only) -->
            <header class="messages-web-header">
                <a href="{{ route('customer.dashboard') }}" class="header-logo" aria-label="Home">
                    <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="header-logo-img" />
                    <span class="header-logo-text"></span>
                </a>
                <nav class="header-nav" aria-label="Main navigation">
                    <a href="{{ route('customer.dashboard') }}" class="header-nav-link">Home</a>
                    <a href="{{ route('customer.order.history') }}" class="header-nav-link">Order</a>
                    <a href="{{ route('customer.favorite') }}" class="header-nav-link">Favorite</a>
                    <a href="{{ route('customer.messages') }}" class="header-nav-link active" aria-current="page">Messages</a>
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

            <!-- Mobile header: title | trash -->
            <header class="messages-header">
                <h1 class="messages-title">Messages</h1>
                <button type="button" class="messages-trash" aria-label="Delete messages">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </header>

            <!-- Tablist: Chats | Notifications -->
            <div class="messages-tabs" role="tablist" aria-label="Messages sections">
                <button type="button" class="messages-tab active" data-panel="chats" role="tab" aria-selected="true" aria-controls="chats-panel">Chats</button>
                <button type="button" class="messages-tab" data-panel="notifications" role="tab" aria-selected="false" aria-controls="notifications-panel">
                    Notifications
                    @if(($notificationsUnreadCount ?? 0) > 0)
                        <span class="messages-tab-badge">{{ min($notificationsUnreadCount, 99) }}</span>
                    @endif
                </button>
            </div>

            <main class="messages-main">
                <!-- Chats panel -->
                <div class="messages-panel active" id="chats-panel" role="tabpanel" aria-labelledby="chats-tab" aria-label="Chats">
                    <div class="messages-list">
                        @forelse($chats ?? [] as $chat)
                            <a href="#" class="message-row" aria-label="Chat with {{ $chat->sender }}">
                                <div class="message-row-avatar">
                                    <span class="material-symbols-outlined">person</span>
                                </div>
                                <div class="message-row-body">
                                    <span class="message-row-sender">{{ $chat->sender }}</span>
                                    <span class="message-row-preview">{{ $chat->preview }}</span>
                                </div>
                                <span class="message-row-time">{{ $chat->time }}</span>
                            </a>
                        @empty
                            <div class="messages-empty">
                                <p>No chats yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Notifications panel -->
                <div class="messages-panel" id="notifications-panel" role="tabpanel" aria-labelledby="notifications-tab" aria-label="Notifications" hidden>
                    <div class="notifications-list">
                        @forelse($notifications ?? [] as $notif)
                            <div class="notification-row">
                                <div class="notification-row-icon">
                                    <span class="material-symbols-outlined">notifications</span>
                                </div>
                                <div class="notification-row-body">
                                    <p class="notification-row-text">{{ $notif->message }}</p>
                                    <span class="notification-row-time">{{ $notif->time }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="messages-empty">
                                <p>No notifications.</p>
                            </div>
                        @endforelse
                    </div>
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
            <a href="{{ route('customer.messages') }}" class="nav-item active" aria-current="page">
                <span class="material-symbols-outlined nav-icon">chat_bubble</span>
                <span class="nav-label">Messages</span>
            </a>
        </nav>
    </div>

    <script>
        (function() {
            var tabs = document.querySelectorAll('.messages-tab');
            var panels = document.querySelectorAll('.messages-panel');

            function showPanel(panelId) {
                panels.forEach(function(p) {
                    var isActive = p.id === panelId;
                    p.classList.toggle('active', isActive);
                    p.hidden = !isActive;
                });
                tabs.forEach(function(t) {
                    var isSelected = t.getAttribute('data-panel') + '-panel' === panelId;
                    t.classList.toggle('active', isSelected);
                    t.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                });
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var panelId = tab.getAttribute('data-panel') + '-panel';
                    showPanel(panelId);
                });
            });
        })();
    </script>
</body>

</html>
