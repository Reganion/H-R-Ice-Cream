<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'H&R Ice Cream Admin') </title>
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/notification.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/dashboard.css') }}">
</head>

<body>
    <!-- Toast alerts: drop from top, slide up on exit -->
    <div class="admin-toast-container" id="adminToastContainer" aria-live="polite"></div>
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
    <aside class="sidebar">
        <div>
            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="H&R Logo" />
            </div>

            <div class="menu-label">MENU</div>
            <ul class="menu">
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <span class="material-symbols-outlined">grid_view</span>
                        Dashboard
                    </a>
                </li>

                <li
                    class="dropdown-parent {{ request()->routeIs('admin.flavors', 'admin.ingredients', 'admin.gallon') ? 'open' : '' }}">
                    <a class="dropdown">
                        <div class="left-label">
                            <span class="material-symbols-outlined">icecream</span>
                            Product
                        </div>
                        <span class="material-symbols-outlined arrow-icon">keyboard_arrow_down</span>
                    </a>

                    <ul class="submenu">
                        <li class="{{ request()->routeIs('admin.flavors') ? 'active' : '' }}">
                            <a href="{{ route('admin.flavors') }}">Flavor</a>
                        </li>
                        <li class="{{ request()->routeIs('admin.ingredients') ? 'active' : '' }}">
                            <a href="{{ route('admin.ingredients') }}">Ingredients</a>
                        </li>
                        <li class="{{ request()->routeIs('admin.gallon') ? 'active' : '' }}">
                            <a href="{{ route('admin.gallon') }}">Gallon</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                    <a href="{{ route('admin.orders') }}">
                        <span class="material-symbols-outlined">shopping_basket</span>
                        Orders
                    </a>
                </li>

                <li class="{{ request()->routeIs('admin.drivers') ? 'active' : '' }}">
                    <a href="{{ route('admin.drivers') }}">
                        <span class="material-symbols-outlined">groups</span>
                        Drivers
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.customer') ? 'active' : '' }}">
                    <a href="{{ route('admin.customer') }}">
                        <span class="material-symbols-outlined">person</span>
                        Customer
                    </a>
                </li>

                <li class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports') }}">
                        <span class="material-symbols-outlined">description</span>
                        Reports
                    </a>
                </li>

                <li class="{{ request()->routeIs('admin.archive') ? 'active' : '' }}">
                    <a href="{{ route('admin.archive') }}">
                        <span class="material-symbols-outlined">archive</span>
                        Archive
                    </a>
                </li>
            </ul>
        </div>

        <ul class="account-settings">
            <li class="settings-label">SETTINGS</li>

            <li class="{{ request()->routeIs('admin.account') ? 'active' : '' }}">
                <a href="{{ route('admin.account') }}">
                    <span class="material-symbols-outlined">account_circle</span>
                    Account
                </a>
            </li>

            <li><a href="{{ route('admin.logout') }}" class="logout-link"><span class="material-symbols-outlined">logout</span>Logout</a>
            </li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <div class="user">
                <img src="{{ $adminUser && $adminUser->image ? asset($adminUser->image) : asset('img/kyle.jpg') }}" alt="Profile">
                <div class="user-info">
                    <strong>Hello, {{ $adminUser ? trim(($adminUser->first_name ?? '') . ' ' . ($adminUser->last_name ?? '')) ?: 'Admin' : 'Admin' }}</strong>
                    <small id="manila-date"></small>
                </div>
            </div>

            <div class="right-side">
                @if (request()->routeIs('admin.dashboard'))
                    <div class="search-bar">
                        <span class="material-symbols-outlined search-icon">search</span>
                        <input type="text" placeholder="Search">
                    </div>
                @endif

                @php
                    $adminUnreadCount = isset($adminNotifications) ? $adminNotifications->whereNull('read_at')->count() : 0;
                @endphp
                <div class="notification {{ $adminUnreadCount > 0 ? 'has-unread' : '' }}" id="notifBtn">
                    <span class="material-symbols-outlined notif-bell">notifications</span>
                    <span class="notif-badge {{ $adminUnreadCount === 0 ? 'zero' : '' }}" id="notifBadge" data-count="{{ $adminUnreadCount }}">{{ $adminUnreadCount }}</span>

                    <!-- DROPDOWN -->
                    <div class="notification-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <span class="title">Notifications</span>

                            <div class="mark-read {{ $adminUnreadCount === 0 ? 'read' : '' }}" id="markAllReadBtn">
                                <span class="material-symbols-outlined">done_all</span>
                                <span>Mark all as read</span>
                            </div>
                        </div>

                        <div class="notif-tabs">
                            <span class="notif-tab active" data-tab="all">All</span>
                            <span class="notif-tab unread-tab" data-tab="unread">Unread <span class="unread-count">{{ isset($adminNotifications) ? $adminNotifications->whereNull('read_at')->count() : 0 }}</span></span>
                        </div>

                        <div class="notif-list">
                            @forelse (isset($adminNotifications) ? $adminNotifications : [] as $notif)
                                <div class="notif-item {{ $notif->read_at ? '' : 'unread' }}" data-id="{{ $notif->id }}" data-type="{{ $notif->type }}" data-related-id="{{ $notif->type === 'order_new' ? ($notif->related_id ?? '') : '' }}">
                                    @if (($notif->type === 'profile_update' || $notif->type === 'address_update' || $notif->type === 'order_new') && $notif->image_url)
                                        <div class="notif-icon profile">
                                            <img src="{{ asset($notif->image_url) }}" alt="">
                                        </div>
                                    @elseif ($notif->type === 'order_new')
                                        <div class="notif-icon delivered">
                                            <span class="material-symbols-outlined">shopping_cart</span>
                                        </div>
                                    @elseif ($notif->type === 'address_update')
                                        <div class="notif-icon delivered">
                                            <span class="material-symbols-outlined">location_on</span>
                                        </div>
                                    @else
                                        <div class="notif-icon delivered">
                                            <span class="material-symbols-outlined">notifications_active</span>
                                        </div>
                                    @endif
                                    <div class="notif-text">
                                        <span>
                                            <strong>{{ $notif->title ?? 'Notification' }}</strong>@if (!empty($notif->data['subtitle']))
                                                <span class="muted"> {{ $notif->data['subtitle'] }}</span>@endif
                                            @if (!empty($notif->data['highlight']))
                                                <span class="highlight"> {{ $notif->data['highlight'] }}</span>
                                            @endif
                                            @if ($notif->message && empty($notif->data['subtitle']))
                                                <span class="muted"> {{ $notif->message }}</span>
                                            @endif
                                        </span>
                                        <span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="notif-empty">No notifications yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <!-- Order details modal (only for order notifications) -->
    <div class="order-details-modal" id="orderDetailsModal" aria-hidden="true" role="dialog" aria-labelledby="orderDetailsModalTitle">
        <div class="order-details-backdrop" id="orderDetailsBackdrop"></div>
        <div class="order-details-panel">
            <div class="order-details-header">
                <h2 id="orderDetailsModalTitle" class="order-details-title">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Order details
                </h2>
                <button type="button" class="order-details-close" id="orderDetailsClose" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="order-details-body" id="orderDetailsBody">
                <div class="order-details-loading" id="orderDetailsLoading">
                    <span class="material-symbols-outlined spin">progress_activity</span>
                    <span>Loading order…</span>
                </div>
                <div class="order-details-content" id="orderDetailsContent" style="display: none;"></div>
                <div class="order-details-error" id="orderDetailsError" style="display: none;">
                    <span class="material-symbols-outlined">error_outline</span>
                    <span>Could not load order details.</span>
                </div>
            </div>
            <div class="order-details-footer">
                <a href="{{ route('admin.orders') }}" class="order-details-btn order-details-btn-primary" id="orderDetailsViewAll">View all orders</a>
            </div>
        </div>
    </div>

    <!-- Floating chat: Admin ↔ Customer (available on all admin pages) -->
    <div class="float-chat-wrap" id="floatChatWrap">
        <div class="float-chat-panel view-new-message" id="floatChatPanel" aria-hidden="true">
            <div class="chat-new-msg">
                <div class="chat-new-msg-header">
                    <span class="title">New message</span>
                    <button type="button" class="chat-new-msg-close" id="floatChatClose" aria-label="Close">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="chat-to-wrap">
                    <label for="chatToInput">To:</label>
                    <input type="text" class="chat-to-input" id="chatToInput" placeholder="Search customers" />
                </div>
                <div class="chat-customers" id="chatCustomerList">
                    <div class="chat-placeholder chat-loading" id="chatCustomerListPlaceholder" style="padding:16px;max-height:none;">Loading customers…</div>
                </div>
            </div>
            <div class="chat-conversation">
                <div class="chat-header" id="chatConvHeader">
                    <button type="button" class="chat-header-back" id="chatBackToNewMsg" aria-label="Back to New message">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </button>
                    <div class="chat-header-avatar" id="chatHeaderAvatar">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <span class="chat-header-name" id="chatHeaderName">Select a customer</span>
                    <span class="chat-header-caret material-symbols-outlined" aria-hidden="true">keyboard_arrow_down</span>
                    <div class="chat-header-actions">
                        <button type="button" aria-label="Minimize"><span class="material-symbols-outlined">remove</span></button>
                        <button type="button" class="chat-header-close" id="chatConvClose" aria-label="Close"><span class="material-symbols-outlined">close</span></button>
                    </div>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-placeholder" id="chatPlaceholder">Select a customer to view messages.</div>
                </div>
                <div class="chat-input-wrap">
                    <div class="chat-input-actions">
                        <input type="file" id="chatFileInput" accept="image/*" multiple hidden />
                        <button type="button" id="chatAttachBtn" aria-label="Attach images"><span class="material-symbols-outlined">image</span></button>
                    </div>
                    <textarea class="chat-input" id="chatInput" placeholder="Aa" rows="1"></textarea>
                    <div class="chat-input-right">
                        <button type="button" class="chat-thumbs" id="chatThumbs" aria-label="Like"><span class="material-symbols-outlined">thumb_up</span></button>
                        <button type="button" class="chat-send" id="chatSend" aria-label="Send"><span class="material-symbols-outlined">send</span></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="chat-head" id="chatHead" aria-hidden="true">
            <div class="chat-head-bubble">
                <span class="chat-head-name" id="chatHeadName">Customer</span>
                <span class="chat-head-preview" id="chatHeadPreview">New message</span>
            </div>
            <div class="chat-head-avatar-wrap" id="chatHeadAvatarWrap">
                <div class="chat-head-avatar" id="chatHeadAvatar">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <span class="chat-head-avatar-badge" id="chatHeadAvatarBadge">0</span>
                <button type="button" class="chat-head-dismiss" id="chatHeadDismiss" aria-label="Dismiss">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>
        <button type="button" class="float-chat-btn" id="floatChatBtn" aria-label="New message">
            <span class="chat-unread-badge" id="chatUnreadBadge">0</span>
            <span class="material-symbols-outlined">edit</span>
        </button>
    </div>

<script>
    // ========================
    // Dropdown Menu Toggle
    // ========================
    const dropdownParent = document.querySelector('.dropdown-parent');
    const dropdownLink = dropdownParent.querySelector('a.dropdown');

    dropdownLink.addEventListener('click', (e) => {
        e.preventDefault();
        dropdownParent.classList.toggle('open');
    });

    // ========================
    // Manila Time Display
    // ========================
    function updateManilaTime() {
        const options = {
            timeZone: "Asia/Manila",
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "2-digit"
        };
        const now = new Date();
        const formatted = now.toLocaleDateString("en-US", options);
        document.getElementById("manila-date").textContent = formatted;
    }
    setInterval(updateManilaTime, 1000);
    updateManilaTime();

    // ========================
    // Hamburger Menu Toggle
    // ========================
    const hamburger = document.createElement('div');
    hamburger.className = 'hamburger';
    hamburger.innerHTML = '<span class="material-symbols-outlined">menu</span>';
    document.querySelector('.top-bar').prepend(hamburger);

    const sidebar = document.querySelector('.sidebar');
    hamburger.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });

    // ========================
    // Adjust Top-Bar for Non-Dashboard Pages
    // ========================
    document.addEventListener('DOMContentLoaded', () => {
        const isDashboard = "{{ request()->routeIs('admin.dashboard') }}" === '1';
        if (!isDashboard) {
            const topBar = document.querySelector('.top-bar');
            const user = document.querySelector('.user');
            const rightSide = document.querySelector('.right-side');

            function adjustTopBar() {
                if (window.innerWidth <= 600) {
                    topBar.style.flexDirection = 'row';
                    topBar.style.alignItems = 'center';
                    user.style.order = '1';
                    user.style.flex = '1';
                    rightSide.style.order = '2';
                    rightSide.style.width = 'auto';
                    rightSide.style.marginLeft = 'auto';
                    rightSide.style.justifyContent = 'flex-end';
                } else {
                    topBar.style.flexDirection = '';
                    topBar.style.alignItems = '';
                    user.style.order = '';
                    user.style.flex = '';
                    rightSide.style.order = '';
                    rightSide.style.width = '';
                    rightSide.style.marginLeft = '';
                    rightSide.style.justifyContent = '';
                }
            }

            adjustTopBar();
            window.addEventListener('resize', adjustTopBar);
        }
    });

    // ========================
    // Notifications Dropdown
    // ========================
    const notifBtn = document.getElementById("notifBtn");
    const notifDropdown = document.getElementById("notifDropdown");
    const notifBadge = document.getElementById("notifBadge");
    const markAll = document.querySelector(".mark-read");
    const unreadCountElem = document.querySelector(".unread-count");
    const tabs = document.querySelectorAll(".notif-tabs .notif-tab");
    const notifList = document.querySelector(".notif-list");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Keep badge count in sync; only update from DOM when we actually mark read (not when opening dropdown)
    function updateUnreadCount() {
        const unreadItems = document.querySelectorAll("#notifDropdown .notif-item.unread");
        const count = unreadItems.length;
        if (unreadCountElem) {
            unreadCountElem.textContent = count;
            unreadCountElem.style.display = count === 0 ? "none" : "inline-block";
        }
        if (notifBadge) {
            notifBadge.textContent = count;
            notifBadge.setAttribute("data-count", count);
            notifBadge.classList.toggle("zero", count === 0);
        }
        if (notifBtn) {
            notifBtn.classList.toggle("has-unread", count > 0);
        }
        if (markAll) {
            markAll.classList.toggle("read", count === 0);
        }
    }

    // Adjust dropdown position only for mobile
    function adjustNotifDropdownMobile() {
        const isDashboard = "{{ request()->routeIs('admin.dashboard') }}" === '1';
        if (window.innerWidth <= 600 && notifDropdown) {
            notifDropdown.style.width = "90vw";
            notifDropdown.style.right = "5px";
            notifDropdown.style.bottom = "5px";

            if (isDashboard) {
                notifDropdown.style.top = "160px";
            } else {
                notifDropdown.style.top = ""; // reset for non-dashboard pages
            }
        } else if (notifDropdown) {
            // Reset mobile-specific styles on desktop
            notifDropdown.style.top = "";
            notifDropdown.style.right = "";
            notifDropdown.style.bottom = "";
            notifDropdown.style.width = "";
        }
    }

    // Call on load and resize
    adjustNotifDropdownMobile();
    window.addEventListener("resize", adjustNotifDropdownMobile);

    // Toggle dropdown on bell click (do not recalculate count — badge stays correct)
    if (notifBtn) {
        notifBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            e.preventDefault();
            const isOpen = notifDropdown.style.display === "block";
            notifDropdown.style.display = isOpen ? "none" : "block";
        });
    }

    // Prevent dropdown from closing when clicking inside
    notifDropdown.addEventListener("click", (e) => e.stopPropagation());

    // Close dropdown when clicking outside
    document.addEventListener("click", function(e) {
        if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
            notifDropdown.style.display = "none";
        }
    });

    // Order details modal elements
    const orderDetailsModal = document.getElementById("orderDetailsModal");
    const orderDetailsBackdrop = document.getElementById("orderDetailsBackdrop");
    const orderDetailsClose = document.getElementById("orderDetailsClose");
    const orderDetailsBody = document.getElementById("orderDetailsBody");
    const orderDetailsLoading = document.getElementById("orderDetailsLoading");
    const orderDetailsContent = document.getElementById("orderDetailsContent");
    const orderDetailsError = document.getElementById("orderDetailsError");

    function openOrderDetailsModal() {
        if (orderDetailsModal) {
            orderDetailsModal.classList.add("is-open");
            orderDetailsModal.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
        }
    }

    function closeOrderDetailsModal() {
        if (orderDetailsModal) {
            orderDetailsModal.classList.remove("is-open");
            orderDetailsModal.setAttribute("aria-hidden", "true");
            document.body.style.overflow = "";
        }
    }

    function renderOrderDetailsContent(order) {
        const o = order || {};
        const productHtml = `<div class="product-row"><img src="${escapeHtml(o.product_image_url || '')}" alt=""><div class="product-info"><div class="product-name">${escapeHtml(o.product_name || '—')}</div><div class="product-meta">${escapeHtml(o.product_type || '')} ${escapeHtml(o.gallon_size || '')}</div></div></div>`;
        const customerHtml = `<div class="detail-row"><img class="detail-avatar" src="${escapeHtml(o.customer_image_url || '')}" alt=""><div><div class="detail-value">${escapeHtml(o.customer_name || '—')}</div><div class="detail-value" style="font-weight:400;font-size:13px;">${escapeHtml(o.customer_phone || '')}</div></div></div>`;
        const deliveryHtml = `<div class="detail-row"><span class="detail-label">Address</span><span class="detail-value">${escapeHtml(o.delivery_address || '—')}</span></div><div class="detail-row"><span class="detail-label">Date & time</span><span class="detail-value">${escapeHtml((o.delivery_date_formatted || '') + ' ' + (o.delivery_time_formatted || ''))}</span></div>`;
        const paymentHtml = `<div class="detail-row"><span class="detail-label">Payment</span><span class="detail-value">${escapeHtml(o.payment_method || '—')}</span></div><div class="detail-row"><span class="detail-label">Amount</span><span class="detail-value amount">₱${Number(o.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span></div><div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">${escapeHtml((o.status || '—') + '')}</span></div>`;
        return `<div class="detail-section"><div class="detail-section-title"><span class="material-symbols-outlined">inventory_2</span> Product</div>${productHtml}</div><div class="detail-section"><div class="detail-section-title"><span class="material-symbols-outlined">person</span> Customer</div>${customerHtml}</div><div class="detail-section"><div class="detail-section-title"><span class="material-symbols-outlined">location_on</span> Delivery</div>${deliveryHtml}</div><div class="detail-section"><div class="detail-section-title"><span class="material-symbols-outlined">payments</span> Payment & status</div>${paymentHtml}</div>`;
    }

    async function showOrderDetailsModal(orderId) {
        if (!orderDetailsModal || !orderId) return;
        orderDetailsLoading.style.display = "flex";
        orderDetailsContent.style.display = "none";
        orderDetailsError.style.display = "none";
        openOrderDetailsModal();

        try {
            const res = await fetch(`/admin/orders/${orderId}`, {
                headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
                credentials: "same-origin"
            });
            const data = await res.json().catch(() => ({}));
            orderDetailsLoading.style.display = "none";
            if (res.ok && data.order) {
                orderDetailsContent.innerHTML = renderOrderDetailsContent(data.order);
                orderDetailsContent.style.display = "block";
            } else {
                orderDetailsError.style.display = "flex";
            }
        } catch (err) {
            orderDetailsLoading.style.display = "none";
            orderDetailsError.style.display = "flex";
        }
    }

    if (orderDetailsBackdrop) orderDetailsBackdrop.addEventListener("click", closeOrderDetailsModal);
    if (orderDetailsClose) orderDetailsClose.addEventListener("click", closeOrderDetailsModal);
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && orderDetailsModal && orderDetailsModal.classList.contains("is-open")) closeOrderDetailsModal(); });

    // Click on individual notification: mark as read; if order notification, show order details modal
    if (notifList) {
        notifList.addEventListener("click", async (e) => {
            const item = e.target.closest(".notif-item[data-id]");
            if (!item) return;

            const notifType = item.getAttribute("data-type");
            const relatedId = item.getAttribute("data-related-id");
            if (notifType === "order_new" && relatedId) {
                notifDropdown.style.display = "none";
                showOrderDetailsModal(relatedId);
            }

            if (item.classList.contains("unread")) {
                const id = item.getAttribute("data-id");
                try {
                    const res = await fetch(`/admin/notifications/${id}/read`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        credentials: "same-origin",
                        body: JSON.stringify({})
                    });
                    if (res.ok) {
                        item.classList.remove("unread");
                        updateUnreadCount();
                    }
                } catch (err) {
                    console.error("Mark read failed", err);
                }
            }
        });
    }

    // Mark all as read (API + UI) — persists in DB
    if (markAll) {
        markAll.addEventListener("click", async () => {
            if (markAll.classList.contains("read")) return;
            try {
                const res = await fetch("{{ route('admin.notifications.mark-all-read') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    credentials: "same-origin",
                    body: JSON.stringify({})
                });
                const data = res.ok ? await res.json().catch(() => ({})) : null;
                if (res.ok && (data === null || data.success !== false)) {
                    document.querySelectorAll(".notif-item.unread").forEach(item => {
                        item.classList.remove("unread");
                        const activeTab = document.querySelector(".notif-tab.active");
                        if (activeTab && activeTab.getAttribute("data-tab") === "unread") {
                            item.style.display = "none";
                        }
                    });
                    updateUnreadCount();
                    markAll.classList.add("read");
                }
            } catch (err) {
                console.error("Mark all read failed", err);
            }
        });
    }

    // Tab switching (All / Unread)
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const allItems = document.querySelectorAll(".notif-item[data-id]");
            const tabType = tab.getAttribute("data-tab");
            if (tabType === "all") {
                allItems.forEach(item => { item.style.display = "flex"; });
            } else if (tabType === "unread") {
                allItems.forEach(item => {
                    item.style.display = item.classList.contains("unread") ? "flex" : "none";
                });
            }
        });
    });

    // Initialize unread count
    updateUnreadCount();

    // ========================
    // Real-time notifications (polling)
    // ========================
    const NOTIFICATIONS_POLL_URL = "{{ route('admin.notifications.index') }}";
    const NOTIFICATIONS_POLL_INTERVAL_MS = 10000; // 10 seconds
    const TOAST_DURATION_MS = 5000;
    // Baseline = IDs we already know about (no toast for these). First poll after load sets baseline only.
    let lastSeenNotifIds = new Set(Array.from(document.querySelectorAll(".notif-item[data-id]")).map(el => String(el.getAttribute("data-id"))));
    let firstPollDone = false;

    function getToastMessage(notif) {
        const data = notif.data || {};
        const subtitle = (data.subtitle || '').trim();
        const highlight = (data.highlight || '').trim();
        const name = (notif.title || 'Someone').trim();
        if (subtitle && highlight) {
            return name + " " + subtitle + " " + highlight;
        }
        if (notif.message) return name + " — " + notif.message;
        if (notif.type === 'order_new') return name + " placed a new order.";
        return name;
    }

    function showToast(notif) {
        const container = document.getElementById("adminToastContainer");
        if (!container) return;
        const message = getToastMessage(notif);
        const hasImage = (notif.type === 'profile_update' || notif.type === 'address_update' || notif.type === 'order_new') && notif.image_url;
        const iconHtml = hasImage
            ? `<div class="toast-icon"><img src="${escapeHtml(notif.image_url)}" alt=""></div>`
            : `<div class="toast-icon"><span class="material-symbols-outlined" style="font-size:22px">notifications_active</span></div>`;
        const el = document.createElement("div");
        el.className = "admin-toast";
        el.setAttribute("role", "alert");
        el.innerHTML = iconHtml + `<span class="toast-message">${escapeHtml(message)}</span><button type="button" class="toast-close" aria-label="Close"><span class="material-symbols-outlined" style="font-size:18px">close</span></button>`;
        container.appendChild(el);
        requestAnimationFrame(() => el.classList.add("toast-enter"));

        function dismiss() {
            el.classList.remove("toast-enter");
            el.classList.add("toast-exit");
            setTimeout(() => el.remove(), 350);
        }

        el.querySelector(".toast-close").addEventListener("click", dismiss);
        const t = setTimeout(dismiss, TOAST_DURATION_MS);
        el._toastTimer = t;
    }

    function buildNotifIcon(notif) {
        const hasImage = (notif.type === 'profile_update' || notif.type === 'address_update' || notif.type === 'order_new') && notif.image_url;
        if (hasImage) {
            return `<div class="notif-icon profile"><img src="${escapeHtml(notif.image_url)}" alt=""></div>`;
        }
        if (notif.type === 'order_new') {
            return `<div class="notif-icon delivered"><span class="material-symbols-outlined">shopping_cart</span></div>`;
        }
        if (notif.type === 'address_update') {
            return `<div class="notif-icon delivered"><span class="material-symbols-outlined">location_on</span></div>`;
        }
        return `<div class="notif-icon delivered"><span class="material-symbols-outlined">notifications_active</span></div>`;
    }

    function escapeHtml(str) {
        if (str == null) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderNotifItem(notif) {
        const isUnread = !notif.read_at;
        const data = notif.data || {};
        const subtitle = data.subtitle ? ` <span class="muted">${escapeHtml(data.subtitle)}</span>` : '';
        const highlight = data.highlight ? ` <span class="highlight">${escapeHtml(data.highlight)}</span>` : '';
        const message = notif.message && !data.subtitle ? ` <span class="muted">${escapeHtml(notif.message)}</span>` : '';
        const isOrder = notif.type === 'order_new' && notif.related_id;
        const dataType = escapeHtml(String(notif.type || ''));
        const dataRelatedId = isOrder ? escapeHtml(String(notif.related_id)) : '';
        return `<div class="notif-item ${isUnread ? 'unread' : ''}" data-id="${escapeHtml(String(notif.id))}" data-type="${dataType}" data-related-id="${dataRelatedId}">${buildNotifIcon(notif)}<div class="notif-text"><span><strong>${escapeHtml(notif.title)}</strong>${subtitle}${highlight}${message}</span><span class="notif-time">${escapeHtml(notif.created_at_human || '')}</span></div></div>`;
    }

    async function pollNotifications() {
        try {
            const res = await fetch(NOTIFICATIONS_POLL_URL, {
                headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
                credentials: "same-origin"
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !Array.isArray(data.notifications)) return;

            const currentIds = new Set((data.notifications || []).map(n => String(n.id)));

            // First poll after page load = set baseline only, never show toasts (fixes refresh showing all)
            if (!firstPollDone) {
                firstPollDone = true;
                lastSeenNotifIds = new Set(currentIds);
                const list = document.querySelector(".notif-list");
                if (list) {
                    const activeTab = document.querySelector(".notif-tab.active");
                    const tabType = activeTab ? activeTab.getAttribute("data-tab") : "all";
                    if (data.notifications.length === 0) {
                        list.innerHTML = '<div class="notif-empty">No notifications yet.</div>';
                    } else {
                        list.innerHTML = data.notifications.map(renderNotifItem).join("");
                        if (tabType === "unread") {
                            list.querySelectorAll(".notif-item").forEach(item => {
                                item.style.display = item.classList.contains("unread") ? "flex" : "none";
                            });
                        }
                    }
                }
            } else {
                const hasNew = [...currentIds].some(id => !lastSeenNotifIds.has(id));
                if (hasNew) {
                    const list = document.querySelector(".notif-list");
                    if (list) {
                        const activeTab = document.querySelector(".notif-tab.active");
                        const tabType = activeTab ? activeTab.getAttribute("data-tab") : "all";
                        if (data.notifications.length === 0) {
                            list.innerHTML = '<div class="notif-empty">No notifications yet.</div>';
                        } else {
                            list.innerHTML = data.notifications.map(renderNotifItem).join("");
                            if (tabType === "unread") {
                                list.querySelectorAll(".notif-item").forEach(item => {
                                    item.style.display = item.classList.contains("unread") ? "flex" : "none";
                                });
                            }
                        }
                    }
                    const newNotifs = data.notifications.filter(n => !lastSeenNotifIds.has(String(n.id)));
                    newNotifs.forEach(showToast);
                    lastSeenNotifIds = new Set(currentIds);
                }
            }

            // Always update badge count so it stays accurate
            const count = typeof data.unread_count === 'number' ? data.unread_count : 0;
            if (notifBadge) {
                notifBadge.textContent = count;
                notifBadge.setAttribute("data-count", count);
                notifBadge.classList.toggle("zero", count === 0);
            }
            if (notifBtn) notifBtn.classList.toggle("has-unread", count > 0);
            if (unreadCountElem) {
                unreadCountElem.textContent = count;
                unreadCountElem.style.display = count === 0 ? "none" : "inline-block";
            }
            if (markAll) markAll.classList.toggle("read", count === 0);
        } catch (err) {
            console.warn("Notifications poll failed", err);
        }
    }

    if (NOTIFICATIONS_POLL_URL && document.getElementById("notifBtn")) {
        setInterval(pollNotifications, NOTIFICATIONS_POLL_INTERVAL_MS);
        setTimeout(pollNotifications, 2000); // First refresh shortly after load
    }
</script>

{{-- Admin floating chat (available on all admin pages) --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const panel = document.getElementById("floatChatPanel");
    const btn = document.getElementById("floatChatBtn");
    const closeBtn = document.getElementById("floatChatClose");
    const chatInput = document.getElementById("chatInput");
    const chatSend = document.getElementById("chatSend");
    const chatMessages = document.getElementById("chatMessages");
    const chatPlaceholder = document.getElementById("chatPlaceholder");
    const chatCustomerList = document.getElementById("chatCustomerList");
    const chatCustomerListPlaceholder = document.getElementById("chatCustomerListPlaceholder");
    const chatHeaderName = document.getElementById("chatHeaderName");
    const chatHeaderAvatar = document.getElementById("chatHeaderAvatar");
    const chatToInput = document.getElementById("chatToInput");
    const chatThumbs = document.getElementById("chatThumbs");
    const convHeader = document.getElementById("chatConvHeader");
    const chatHead = document.getElementById("chatHead");
    const chatHeadName = document.getElementById("chatHeadName");
    const chatHeadPreview = document.getElementById("chatHeadPreview");
    const chatHeadAvatar = document.getElementById("chatHeadAvatar");
    const chatHeadAvatarWrap = document.getElementById("chatHeadAvatarWrap");
    const chatHeadAvatarBadge = document.getElementById("chatHeadAvatarBadge");
    const chatHeadDismiss = document.getElementById("chatHeadDismiss");
    const chatUnreadBadge = document.getElementById("chatUnreadBadge");

    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    const chatCustomersUrl = "{{ route('admin.chat.customers') }}";
    const chatCustomerShowUrl = "{{ url('admin/chat/customers') }}";
    const chatUnreadSummaryUrl = "{{ route('admin.chat.unread-summary') }}";
    const chatSendUrl = "{{ url('admin/chat/customers') }}";

    if (!panel || !btn) return;

    let selectedCustomerId = null;
    let selectedCustomerName = null;
    let unreadCount = 0;
    let searchDebounce = null;
    let lastMessageId = 0;
    let pollIntervalId = null;
    let unreadSummaryPollId = null;
    var chatHeadPinnedByMinimize = false;
    const CHAT_POLL_INTERVAL_MS = 2500;
    const UNREAD_SUMMARY_POLL_MS = 5000;

    function getHeaders() {
        return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    }

    function loadCustomers(q) {
        if (!chatCustomerList || !chatCustomerListPlaceholder) return;
        chatCustomerListPlaceholder.style.display = 'block';
        chatCustomerListPlaceholder.textContent = 'Loading customers…';
        chatCustomerList.querySelectorAll('.chat-customer-item').forEach(function(el) { el.remove(); });
        const url = q ? chatCustomersUrl + '?q=' + encodeURIComponent(q) : chatCustomersUrl;
        fetch(url, { headers: getHeaders(), credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                chatCustomerListPlaceholder.style.display = 'none';
                if (!data.success || !data.data || !data.data.length) {
                    chatCustomerListPlaceholder.textContent = 'No customers found.';
                    chatCustomerListPlaceholder.style.display = 'block';
                    return;
                }
                data.data.forEach(function(c) {
                    const item = document.createElement('div');
                    item.className = 'chat-customer-item';
                    item.setAttribute('data-customer-id', c.id);
                    item.setAttribute('data-customer-name', c.full_name || '');
                    item.setAttribute('tabindex', '0');
                    const avatarHtml = c.image_url ? '<img src="' + escapeHtml(c.image_url) + '" alt="" />' : '<span class="material-symbols-outlined">person</span>';
                    item.innerHTML = '<div class="chat-customer-avatar">' + avatarHtml + '</div><div class="chat-customer-name">' + escapeHtml(c.full_name || 'Customer') + '</div>';
                    item.addEventListener('click', function() { selectCustomer(c.id); });
                    chatCustomerList.appendChild(item);
                });
            })
            .catch(function() {
                chatCustomerListPlaceholder.textContent = 'Failed to load customers.';
                chatCustomerListPlaceholder.style.display = 'block';
            });
    }

    function stopPolling() {
        if (pollIntervalId) { clearInterval(pollIntervalId); pollIntervalId = null; }
    }

    function startPolling() {
        stopPolling();
        pollIntervalId = setInterval(pollNewMessages, CHAT_POLL_INTERVAL_MS);
    }

    function pollNewMessages() {
        if (!selectedCustomerId || !panel.classList.contains('open') || panel.classList.contains('view-new-message')) return;
        var url = chatCustomerShowUrl + '/' + selectedCustomerId + '/messages?after_id=' + lastMessageId;
        fetch(url, { headers: getHeaders(), credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success || !data.messages || !data.messages.length) return;
                data.messages.forEach(function(m) {
                    if (m.id && chatMessages.querySelector('[data-message-id="' + m.id + '"]')) return;
                    appendMessage(m);
                    if (m.id && m.id > lastMessageId) lastMessageId = m.id;
                });
                if (data.messages.length && chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
            });
    }

    function selectCustomer(customerId) {
        selectedCustomerId = customerId;
        lastMessageId = 0;
        stopPolling();
        chatMessages.querySelectorAll('.chat-msg').forEach(function(m) { m.remove(); });
        if (chatPlaceholder) { chatPlaceholder.textContent = 'Loading messages…'; chatPlaceholder.style.display = 'flex'; }
        fetch(chatCustomerShowUrl + '/' + customerId, { headers: getHeaders(), credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) return;
                const customer = data.customer || {};
                var fname = (customer.firstname || '').trim();
                var lname = (customer.lastname || '').trim();
                selectedCustomerName = (customer.full_name || (fname + ' ' + lname).trim() || customer.email || 'Customer');
                if (chatHeaderName) { chatHeaderName.textContent = selectedCustomerName; chatHeaderName.title = selectedCustomerName; }
                if (chatHeaderAvatar) {
                    var imgUrl = customer.image_url;
                    chatHeaderAvatar.innerHTML = imgUrl ? '<img src="' + escapeHtml(String(imgUrl)) + '" alt="" />' : '<span class="material-symbols-outlined">person</span>';
                }
                chatCustomerList.querySelectorAll('.chat-customer-item').forEach(function(i) {
                    i.classList.toggle('active', parseInt(i.getAttribute('data-customer-id'), 10) === customerId);
                });
                if (chatPlaceholder) {
                    chatPlaceholder.style.display = (data.messages && data.messages.length) ? 'none' : 'flex';
                    chatPlaceholder.textContent = (data.messages && data.messages.length) ? '' : 'No messages yet. Say hi!';
                }
                var msgs = data.messages || [];
                msgs.forEach(function(m) { appendMessage(m); if (m.id && m.id > lastMessageId) lastMessageId = m.id; });
                panel.classList.remove('view-new-message');
                panel.classList.add('view-conversation');
                startPolling();
                if (chatInput) chatInput.focus();
            })
            .catch(function() {
                if (chatPlaceholder) { chatPlaceholder.textContent = 'Failed to load messages.'; chatPlaceholder.style.display = 'flex'; }
            });
    }

    function appendMessage(m) {
        if (chatPlaceholder) chatPlaceholder.style.display = 'none';
        if (m.id && m.id > lastMessageId) lastMessageId = m.id;
        const isAdmin = (m.sender_type || '') === 'admin';
        const timeStr = m.created_at ? new Date(m.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
        const div = document.createElement('div');
        div.className = 'chat-msg ' + (isAdmin ? 'admin' : 'customer');
        if (m.id) div.setAttribute('data-message-id', m.id);
        if (m.image_url) {
            div.classList.add('chat-msg-img');
            div.innerHTML = '<img src="' + escapeHtml(m.image_url) + '" alt="Image" class="chat-msg-image" /><div class="chat-msg-time">' + timeStr + '</div>';
        } else {
            div.innerHTML = '<span>' + escapeHtml(m.body || '') + '</span><div class="chat-msg-time">' + timeStr + '</div>';
        }
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function updateUnreadUI() {
        if (chatUnreadBadge) chatUnreadBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        if (btn) { if (unreadCount > 0) btn.classList.add('has-unread'); else btn.classList.remove('has-unread'); }
    }

    function hideChatHead() {
        if (chatHead) { chatHead.classList.remove('visible'); chatHead.setAttribute('aria-hidden', 'true'); }
        if (chatHeadAvatarWrap) chatHeadAvatarWrap.classList.remove('has-unread');
    }

    function showChatHead(name, preview, avatarUrl, count, pinnedByMinimize) {
        if (chatHeadName) chatHeadName.textContent = name || 'Customer';
        if (chatHeadPreview) chatHeadPreview.textContent = (preview || 'New message').substring(0, 40);
        if (chatHeadAvatar) {
            chatHeadAvatar.innerHTML = avatarUrl ? '<img src="' + escapeHtml(avatarUrl) + '" alt="" />' : '<span class="material-symbols-outlined">person</span>';
        }
        if (chatHeadAvatarBadge) chatHeadAvatarBadge.textContent = (count > 99 ? '99+' : count) || '0';
        if (chatHeadAvatarWrap) { if (count > 0) chatHeadAvatarWrap.classList.add('has-unread'); else chatHeadAvatarWrap.classList.remove('has-unread'); }
        if (pinnedByMinimize === true) chatHeadPinnedByMinimize = true;
        if (chatHead) { chatHead.classList.add('visible'); chatHead.setAttribute('aria-hidden', 'false'); }
    }

    function openChat() {
        stopUnreadSummaryPoll();
        panel.classList.add('open');
        panel.setAttribute('aria-hidden', 'false');
        panel.classList.remove('view-conversation');
        panel.classList.add('view-new-message');
        unreadCount = 0;
        updateUnreadUI();
        chatHeadPinnedByMinimize = false;
        hideChatHead();
        loadCustomers(chatToInput ? chatToInput.value.trim() : '');
    }

    function stopUnreadSummaryPoll() {
        if (unreadSummaryPollId) { clearInterval(unreadSummaryPollId); unreadSummaryPollId = null; }
    }

    function pollUnreadSummary() {
        if (panel.classList.contains('open')) return;
        fetch(chatUnreadSummaryUrl, { headers: getHeaders(), credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) return;
                var n = data.unread_count || 0;
                unreadCount = n;
                updateUnreadUI();
                if (n > 0 && data.last_from) {
                    var from = data.last_from;
                    showChatHead(from.full_name || 'Customer', from.preview || 'New message', from.image_url || '', n, false);
                } else if (n === 0 && !chatHeadPinnedByMinimize) hideChatHead();
            });
    }

    function startUnreadSummaryPoll() {
        stopUnreadSummaryPoll();
        pollUnreadSummary();
        unreadSummaryPollId = setInterval(pollUnreadSummary, UNREAD_SUMMARY_POLL_MS);
    }

    function closeChat() {
        stopPolling();
        panel.classList.remove('open');
        panel.setAttribute('aria-hidden', 'true');
        if (panel.classList.contains('view-conversation') && selectedCustomerId) {
            var name = chatHeaderName ? chatHeaderName.textContent : (selectedCustomerName || 'Customer');
            var preview = 'Chat';
            var lastMsg = chatMessages ? chatMessages.querySelector('.chat-msg:last-child .chat-msg-time') : null;
            if (lastMsg && lastMsg.previousElementSibling) {
                var txt = lastMsg.previousElementSibling.textContent || lastMsg.parentElement.textContent || '';
                preview = (txt.trim() || 'Chat').substring(0, 40);
            } else if (chatMessages) {
                var lastBubble = chatMessages.querySelector('.chat-msg:last-child');
                if (lastBubble) preview = (lastBubble.textContent || 'Chat').trim().substring(0, 40);
            }
            var avatarUrl = '';
            if (chatHeaderAvatar && chatHeaderAvatar.querySelector('img')) { var img = chatHeaderAvatar.querySelector('img'); if (img && img.src) avatarUrl = img.src; }
            showChatHead(name, preview, avatarUrl, unreadCount, true);
        } else if (unreadCount > 0) pollUnreadSummary();
        startUnreadSummaryPoll();
    }

    btn.addEventListener('click', function() {
        if (panel.classList.contains('open')) closeChat(); else openChat();
    });

    if (closeBtn) closeBtn.addEventListener('click', closeChat);
    if (chatHeadDismiss) {
        chatHeadDismiss.addEventListener('click', function(e) {
            e.stopPropagation();
            chatHeadPinnedByMinimize = false;
            unreadCount = 0;
            updateUnreadUI();
            hideChatHead();
        });
    }
    if (chatHead) {
        chatHead.addEventListener('click', function(e) {
            if (e.target.closest('.chat-head-dismiss')) return;
            var reopenId = selectedCustomerId;
            openChat();
            if (reopenId) setTimeout(function() { selectCustomer(reopenId); }, 50);
        });
    }
    if (convHeader) {
        var minBtn = convHeader.querySelector("button[aria-label='Minimize']");
        if (minBtn) minBtn.addEventListener('click', closeChat);
    }
    var chatConvClose = document.getElementById('chatConvClose');
    if (chatConvClose) chatConvClose.addEventListener('click', closeChat);
    if (chatToInput && chatCustomerList) {
        chatToInput.addEventListener('input', function() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function() { loadCustomers((chatToInput.value || '').trim()); }, 300);
        });
    }
    var chatBackToNewMsg = document.getElementById('chatBackToNewMsg');
    if (chatBackToNewMsg) {
        chatBackToNewMsg.addEventListener('click', function() {
            stopPolling();
            panel.classList.remove('view-conversation');
            panel.classList.add('view-new-message');
        });
    }

    function onIncomingMessage(senderName, messagePreview, avatarUrl) {
        unreadCount++;
        updateUnreadUI();
        if (!panel.classList.contains('open')) showChatHead(senderName || 'Customer', messagePreview || 'New message', avatarUrl || '', unreadCount, false);
    }
    window.adminChatIncoming = onIncomingMessage;
    document.addEventListener('chatIncoming', function(e) {
        var d = e.detail || {};
        onIncomingMessage(d.senderName || d.name, d.messagePreview || d.preview || d.message);
    });

    function escapeHtml(s) {
        var el = document.createElement('div');
        el.textContent = s;
        return el.innerHTML;
    }

    var chatAttachBtn = document.getElementById('chatAttachBtn');
    var chatFileInput = document.getElementById('chatFileInput');
    if (chatAttachBtn && chatFileInput) {
        chatAttachBtn.addEventListener('click', function() { if (!selectedCustomerId) return; chatFileInput.click(); });
        chatFileInput.addEventListener('change', function() {
            var files = chatFileInput.files;
            if (!files || !files.length || !selectedCustomerId) return;
            var imageFiles = [];
            for (var i = 0; i < files.length; i++) { if (files[i].type.startsWith('image/')) imageFiles.push(files[i]); }
            if (!imageFiles.length) { chatFileInput.value = ''; return; }
            imageFiles.forEach(function(file) {
                var formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('image', file);
                fetch(chatSendUrl + '/' + selectedCustomerId + '/messages', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                    credentials: 'same-origin'
                }).then(function(res) { return res.json(); }).then(function(data) {
                    if (data.success && data.message) {
                        addImageMessage(data.message.image_url, true);
                        if (data.message.id) lastMessageId = data.message.id;
                    }
                });
            });
            chatFileInput.value = '';
        });
    }

    function addImageMessage(src, isAdmin) {
        if (!src) return;
        if (chatPlaceholder) chatPlaceholder.style.display = 'none';
        var timeStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        var div = document.createElement('div');
        div.className = 'chat-msg chat-msg-img ' + (isAdmin ? 'admin' : 'customer');
        div.innerHTML = '<img src="' + escapeHtml(src) + '" alt="Image" class="chat-msg-image" /><div class="chat-msg-time">' + timeStr + '</div>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    if (chatThumbs) chatThumbs.addEventListener('click', function() {
        if (!selectedCustomerId) return;
        sendMessage('👍', true);
    });

    function sendMessage(text, appendOnSuccess) {
        if (!selectedCustomerId) return;
        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('body', text);
        fetch(chatSendUrl + '/' + selectedCustomerId + '/messages', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
            credentials: 'same-origin'
        }).then(function(res) { return res.json(); }).then(function(data) {
            if (appendOnSuccess && data.success && data.message) {
                appendMessage(data.message);
                if (data.message.id) lastMessageId = data.message.id;
            }
        });
    }

    if (chatSend && chatInput) {
        chatSend.addEventListener('click', function() {
            var text = chatInput.value.trim();
            if (!text) return;
            chatInput.value = '';
            chatInput.style.height = 'auto';
            sendMessage(text, true);
        });
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chatSend.click(); }
        });
    }

    startUnreadSummaryPoll();
});
</script>

</body>

</html>
