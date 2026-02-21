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
    <style>
        .logout-confirm-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2200;
            padding: 16px;
        }

        .logout-confirm-modal.open {
            display: flex;
        }

        .logout-confirm-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(18, 24, 38, 0.5);
        }

        .logout-confirm-card {
            position: relative;
            width: min(420px, 92vw);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(13, 20, 33, 0.2);
            padding: 20px;
            z-index: 1;
        }

        .logout-confirm-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 700;
            color: #1f2a3d;
        }

        .logout-confirm-title .material-symbols-outlined {
            color: #ff9800;
            font-size: 24px;
        }

        .logout-confirm-text {
            margin: 0 0 16px;
            font-size: 14px;
            line-height: 1.5;
            color: #4d5a70;
        }

        .logout-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .logout-confirm-btn {
            border: 0;
            border-radius: 10px;
            padding: 9px 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .logout-confirm-btn.cancel {
            background: #eef2f7;
            color: #334155;
        }

        .logout-confirm-btn.confirm {
            background: #ef4444;
            color: #fff;
        }
    </style>
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
        <div class="chat-head-stack" id="chatHeadStack" aria-hidden="true"></div>
        <button type="button" class="float-chat-btn" id="floatChatBtn" aria-label="New message">
            <span class="chat-unread-badge" id="chatUnreadBadge">0</span>
            <span class="material-symbols-outlined">edit</span>
        </button>
    </div>

    <div class="logout-confirm-modal" id="logoutConfirmModal" aria-hidden="true">
        <div class="logout-confirm-backdrop" id="logoutConfirmBackdrop"></div>
        <div class="logout-confirm-card" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle">
            <h3 class="logout-confirm-title" id="logoutConfirmTitle">
                <span class="material-symbols-outlined">logout</span>
                Confirm logout
            </h3>
            <p class="logout-confirm-text">Are you sure you want to logout?</p>
            <div class="logout-confirm-actions">
                <button type="button" class="logout-confirm-btn cancel" id="logoutConfirmCancel">Cancel</button>
                <button type="button" class="logout-confirm-btn confirm" id="logoutConfirmOk">Logout</button>
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
    const chatHeadStack = document.getElementById("chatHeadStack");
    const chatUnreadBadge = document.getElementById("chatUnreadBadge");
    const logoutConfirmModal = document.getElementById("logoutConfirmModal");
    const logoutConfirmBackdrop = document.getElementById("logoutConfirmBackdrop");
    const logoutConfirmCancel = document.getElementById("logoutConfirmCancel");
    const logoutConfirmOk = document.getElementById("logoutConfirmOk");

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
    let keepHeadsPinned = false;
    let pinnedHeads = [];
    let closedHeadIds = new Set();
    let chatHeadsExpanded = false;
    let animateHeadRender = false;
    let lastApiHeadSenders = [];
    let chatAudioCtx = null;
    let lastRingtoneAt = 0;
    let lastUnreadSummaryCount = null;
    const MAX_VISIBLE_HEADS = 4;
    const PINNED_HEADS_STORAGE_KEY = 'admin_chat_pinned_heads_v1';
    const CHAT_POLL_INTERVAL_MS = 1000;
    const UNREAD_SUMMARY_POLL_MS = 1000;
    const CHAT_RINGTONE_MIN_GAP_MS = 1200;

    function getHeaders() {
        return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    }

    function ensureChatAudioContext() {
        if (chatAudioCtx) return chatAudioCtx;
        var Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        try {
            chatAudioCtx = new Ctx();
        } catch (e) {
            chatAudioCtx = null;
        }
        return chatAudioCtx;
    }

    function unlockChatRingtone() {
        var ctx = ensureChatAudioContext();
        if (!ctx) return;
        if (ctx.state === 'suspended') {
            ctx.resume().catch(function() {});
        }
    }

    function playIncomingRingtone() {
        var nowMs = Date.now();
        if (nowMs - lastRingtoneAt < CHAT_RINGTONE_MIN_GAP_MS) return;
        var ctx = ensureChatAudioContext();
        if (!ctx) return;
        if (ctx.state === 'suspended') {
            ctx.resume().catch(function() {});
            return;
        }
        lastRingtoneAt = nowMs;

        var t0 = ctx.currentTime;
        var gain = ctx.createGain();
        gain.gain.setValueAtTime(0.0001, t0);
        gain.connect(ctx.destination);

        function addTone(freq, startAt, duration) {
            var osc = ctx.createOscillator();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startAt);
            osc.connect(gain);
            osc.start(startAt);
            osc.stop(startAt + duration);
        }

        // Messenger-like short "pop-pop" alert (inspired style, not exact audio copy).
        gain.gain.exponentialRampToValueAtTime(0.15, t0 + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.03, t0 + 0.12);
        gain.gain.exponentialRampToValueAtTime(0.13, t0 + 0.16);
        gain.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.34);
        addTone(988.0, t0, 0.09);       // B5
        addTone(1318.51, t0 + 0.12, 0.11); // E6
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
                    const unreadCount = typeof c.unread_count === 'number' ? c.unread_count : 0;
                    const unreadBadgeHtml = unreadCount > 0
                        ? '<span class="chat-customer-unread">' + (unreadCount > 99 ? '99+' : unreadCount) + '</span>'
                        : '';
                    item.innerHTML =
                        '<div class="chat-customer-avatar">' + avatarHtml + '</div>' +
                        '<div class="chat-customer-name-row">' +
                        '<div class="chat-customer-name">' + escapeHtml(c.full_name || 'Customer') + '</div>' +
                        unreadBadgeHtml +
                        '</div>';
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
                var incomingCustomerCount = 0;
                data.messages.forEach(function(m) {
                    if (m.id && chatMessages.querySelector('[data-message-id="' + m.id + '"]')) return;
                    if ((m.sender_type || '') !== 'admin') incomingCustomerCount++;
                    appendMessage(m);
                    if (m.id && m.id > lastMessageId) lastMessageId = m.id;
                });
                if (incomingCustomerCount > 0) playIncomingRingtone();
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

    function savePinnedHeads() {
        try {
            localStorage.setItem(PINNED_HEADS_STORAGE_KEY, JSON.stringify(pinnedHeads || []));
        } catch (e) { /* ignore storage errors */ }
    }

    function loadPinnedHeads() {
        try {
            const raw = localStorage.getItem(PINNED_HEADS_STORAGE_KEY);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function clearPinnedHeadsStorage() {
        try {
            localStorage.removeItem(PINNED_HEADS_STORAGE_KEY);
        } catch (e) { /* ignore storage errors */ }
    }

    function hideChatHeads() {
        if (!chatHeadStack) return;
        chatHeadStack.innerHTML = '';
        chatHeadStack.classList.remove('visible');
        chatHeadStack.setAttribute('aria-hidden', 'true');
        chatHeadsExpanded = false;
    }

    function senderKey(sender) {
        return sender && sender.customer_id ? String(sender.customer_id) : '';
    }

    function mergeSendersWithPinned(apiSenders) {
        const merged = [];
        const used = new Set();

        pinnedHeads.forEach(function(sender) {
            const key = senderKey(sender);
            if (!key || used.has(key)) return;
            used.add(key);
            merged.push(sender);
        });

        (Array.isArray(apiSenders) ? apiSenders : []).forEach(function(sender) {
            const key = senderKey(sender);
            if (!key || closedHeadIds.has(key)) return;
            if (used.has(key)) {
                // keep pinned position but refresh unread count/preview if available
                const idx = merged.findIndex(function(x) { return senderKey(x) === key; });
                if (idx >= 0) {
                    merged[idx] = Object.assign({}, merged[idx], sender);
                }
                return;
            }
            used.add(key);
            merged.push(sender);
        });

        return merged;
    }

    function pinCurrentCustomerHead() {
        if (!selectedCustomerId) return;
        const key = String(selectedCustomerId);
        const name = chatHeaderName ? (chatHeaderName.textContent || '').trim() : '';
        let avatarUrl = '';
        if (chatHeaderAvatar && chatHeaderAvatar.querySelector('img')) {
            const img = chatHeaderAvatar.querySelector('img');
            if (img && img.src) avatarUrl = img.src;
        }

        const newPinned = {
            customer_id: key,
            full_name: name || selectedCustomerName || 'Customer',
            image_url: avatarUrl || '',
            preview: 'Chat',
            unread_count: unreadCount > 0 ? unreadCount : 0
        };

        pinnedHeads = pinnedHeads.filter(function(sender) {
            return senderKey(sender) !== key;
        });
        pinnedHeads.unshift(newPinned);
        closedHeadIds.delete(key);
        savePinnedHeads();
    }

    function renderChatHeads(senders) {
        if (!chatHeadStack) return;
        if (Array.isArray(senders)) {
            lastApiHeadSenders = senders;
        }
        chatHeadStack.innerHTML = '';

        const finalSenders = mergeSendersWithPinned(lastApiHeadSenders);
        if (!Array.isArray(finalSenders) || finalSenders.length === 0) {
            hideChatHeads();
            return;
        }

        const hiddenCount = Math.max(0, finalSenders.length - MAX_VISIBLE_HEADS);
        if (hiddenCount === 0) chatHeadsExpanded = false;
        const visibleSenders = (!chatHeadsExpanded && hiddenCount > 0)
            ? finalSenders.slice(0, MAX_VISIBLE_HEADS)
            : finalSenders;

        if (hiddenCount > 0 || chatHeadsExpanded) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'chat-head-toggle';
            const toggleIcon = chatHeadsExpanded ? 'expand_less' : 'expand_more';
            const toggleLabel = chatHeadsExpanded ? 'Hide' : 'See more';
            const toggleCount = chatHeadsExpanded ? '' : '<span class="chat-head-toggle-count">+' + hiddenCount + '</span>';
            toggleBtn.innerHTML =
                '<span class="material-symbols-outlined">' + toggleIcon + '</span>' +
                '<span class="chat-head-toggle-label">' + toggleLabel + '</span>' +
                toggleCount;
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const willExpand = !chatHeadsExpanded;
                if (willExpand) {
                    chatHeadsExpanded = true;
                    animateHeadRender = true;
                    renderChatHeads();
                    return;
                }

                if (chatHeadStack) chatHeadStack.classList.add('is-collapsing');
                setTimeout(function() {
                    chatHeadsExpanded = false;
                    animateHeadRender = true;
                    if (chatHeadStack) chatHeadStack.classList.remove('is-collapsing');
                    renderChatHeads();
                }, 140);
            });
            chatHeadStack.appendChild(toggleBtn);
        }

        visibleSenders.forEach(function(sender) {
            const customerId = sender && sender.customer_id ? String(sender.customer_id) : '';
            const fullName = sender && sender.full_name ? String(sender.full_name) : 'Customer';
            const preview = sender && sender.preview ? String(sender.preview) : 'New message';
            const avatarUrl = sender && sender.image_url ? String(sender.image_url) : '';
            const senderUnread = sender && typeof sender.unread_count === 'number' ? sender.unread_count : 0;

            const head = document.createElement('div');
            head.className = 'chat-head visible';
            head.setAttribute('aria-hidden', 'false');
            if (customerId) head.setAttribute('data-customer-id', customerId);
            if (animateHeadRender) head.classList.add('chat-head-smooth');

            head.innerHTML =
                '<div class="chat-head-bubble">' +
                '<span class="chat-head-name">' + escapeHtml(fullName) + '</span>' +
                '<span class="chat-head-preview">' + escapeHtml(preview.substring(0, 40)) + '</span>' +
                '</div>' +
                '<div class="chat-head-avatar-wrap ' + (senderUnread > 0 ? 'has-unread' : '') + '">' +
                '<div class="chat-head-avatar">' + (avatarUrl ? '<img src="' + escapeHtml(avatarUrl) + '" alt="" />' : '<span class="material-symbols-outlined">person</span>') + '</div>' +
                '<span class="chat-head-avatar-badge">' + (senderUnread > 99 ? '99+' : senderUnread) + '</span>' +
                '</div>';

            head.addEventListener('click', function() {
                const reopenId = parseInt(customerId || '0', 10);
                head.classList.add('removing');
                if (customerId) {
                    pinnedHeads = pinnedHeads.filter(function(sender) {
                        return senderKey(sender) !== customerId;
                    });
                    closedHeadIds.delete(customerId);
                    savePinnedHeads();
                }
                setTimeout(function() {
                    if (head.parentElement) {
                        head.parentElement.removeChild(head);
                        if (chatHeadStack && chatHeadStack.children.length === 0) {
                            hideChatHeads();
                        }
                    }
                    openChat(reopenId || null);
                }, 170);
            });

            chatHeadStack.appendChild(head);
            if (animateHeadRender) {
                requestAnimationFrame(function() {
                    head.classList.add('is-visible');
                });
            }
        });

        chatHeadStack.classList.add('visible');
        chatHeadStack.setAttribute('aria-hidden', 'false');
        animateHeadRender = false;
    }

    function openChat(targetCustomerId) {
        stopUnreadSummaryPoll();
        panel.classList.add('open');
        panel.setAttribute('aria-hidden', 'false');
        unreadCount = 0;
        updateUnreadUI();
        // Keep minimized profile stack when opening via floating button.
        keepHeadsPinned = true;

        if (targetCustomerId) {
            closedHeadIds.delete(String(targetCustomerId));
            panel.classList.remove('view-new-message');
            panel.classList.add('view-conversation');
            selectCustomer(parseInt(targetCustomerId, 10));
            return;
        }

        panel.classList.remove('view-conversation');
        panel.classList.add('view-new-message');
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
                if (lastUnreadSummaryCount !== null && n > lastUnreadSummaryCount) {
                    playIncomingRingtone();
                }
                lastUnreadSummaryCount = n;
                unreadCount = n;
                updateUnreadUI();
                if (n > 0) {
                    renderChatHeads(Array.isArray(data.senders) ? data.senders : []);
                } else {
                    if (!keepHeadsPinned && pinnedHeads.length === 0) {
                        hideChatHeads();
                    } else {
                        renderChatHeads([]);
                    }
                }
            });
    }

    function startUnreadSummaryPoll() {
        stopUnreadSummaryPoll();
        pollUnreadSummary();
        unreadSummaryPollId = setInterval(pollUnreadSummary, UNREAD_SUMMARY_POLL_MS);
    }

    function closeChat(shouldKeepProfile) {
        stopPolling();
        panel.classList.remove('open');
        panel.setAttribute('aria-hidden', 'true');
        if (shouldKeepProfile) {
            keepHeadsPinned = true;
            pinCurrentCustomerHead();
            renderChatHeads([]);
        } else {
            keepHeadsPinned = false;
            pinnedHeads = [];
            savePinnedHeads();
            hideChatHeads();
        }

        if (unreadCount > 0 || !shouldKeepProfile) pollUnreadSummary();
        startUnreadSummaryPoll();
    }

    // Close from "New message" header: keep existing minimized bubbles.
    function closeFromNewMessage() {
        closeChat(true);
    }

    // Close from customer conversation header: full close/clear.
    function closeFromConversation() {
        stopPolling();
        panel.classList.remove('open');
        panel.setAttribute('aria-hidden', 'true');

        if (selectedCustomerId) {
            const closingId = String(selectedCustomerId);
            pinnedHeads = pinnedHeads.filter(function(sender) {
                return senderKey(sender) !== closingId;
            });
            closedHeadIds.add(closingId);
            savePinnedHeads();
        }

        keepHeadsPinned = pinnedHeads.length > 0;
        if (keepHeadsPinned) {
            renderChatHeads([]);
        } else {
            hideChatHeads();
        }

        pollUnreadSummary();
        startUnreadSummaryPoll();
    }

    btn.addEventListener('click', function() {
        // Floating button works like minimize/toggle (does not clear minimized profiles).
        if (panel.classList.contains('open')) closeChat(true); else openChat(null);
    });

    if (closeBtn) closeBtn.addEventListener('click', closeFromNewMessage);
    if (convHeader) {
        var minBtn = convHeader.querySelector("button[aria-label='Minimize']");
        if (minBtn) minBtn.addEventListener('click', function() { closeChat(true); });
    }
    var chatConvClose = document.getElementById('chatConvClose');
    if (chatConvClose) chatConvClose.addEventListener('click', closeFromConversation);
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
        playIncomingRingtone();
        if (!panel.classList.contains('open')) pollUnreadSummary();
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

    // Restore minimized heads after browser refresh.
    const restoredPinnedHeads = loadPinnedHeads();
    if (restoredPinnedHeads.length > 0) {
        pinnedHeads = restoredPinnedHeads;
        keepHeadsPinned = true;
        renderChatHeads([]);
    }

    var pendingLogoutUrl = '';
    function openLogoutConfirm(url) {
        pendingLogoutUrl = url || '';
        if (!logoutConfirmModal) return;
        logoutConfirmModal.classList.add('open');
        logoutConfirmModal.setAttribute('aria-hidden', 'false');
    }

    function closeLogoutConfirm() {
        pendingLogoutUrl = '';
        if (!logoutConfirmModal) return;
        logoutConfirmModal.classList.remove('open');
        logoutConfirmModal.setAttribute('aria-hidden', 'true');
    }

    function doConfirmedLogout() {
        if (!pendingLogoutUrl) return;
        pinnedHeads = [];
        closedHeadIds.clear();
        keepHeadsPinned = false;
        clearPinnedHeadsStorage();
        hideChatHeads();
        var separator = pendingLogoutUrl.indexOf('?') >= 0 ? '&' : '?';
        window.location.replace(pendingLogoutUrl + separator + 't=' + Date.now());
    }

    var logoutLinks = document.querySelectorAll('.logout-link');
    if (logoutLinks && logoutLinks.length) {
        logoutLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openLogoutConfirm(link.getAttribute('href'));
            });
        });
    }

    if (logoutConfirmBackdrop) logoutConfirmBackdrop.addEventListener('click', closeLogoutConfirm);
    if (logoutConfirmCancel) logoutConfirmCancel.addEventListener('click', closeLogoutConfirm);
    if (logoutConfirmOk) logoutConfirmOk.addEventListener('click', doConfirmedLogout);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && logoutConfirmModal && logoutConfirmModal.classList.contains('open')) {
            closeLogoutConfirm();
        }
    });

    document.addEventListener('click', unlockChatRingtone, { passive: true });
    document.addEventListener('keydown', unlockChatRingtone);
    document.addEventListener('touchstart', unlockChatRingtone, { passive: true });

    startUnreadSummaryPoll();
});
</script>

</body>

</html>
