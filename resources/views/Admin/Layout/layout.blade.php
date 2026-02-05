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
    <link rel="stylesheet" href="{{ asset('css/Admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/notification.css') }}">
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

</body>

</html>
