<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'H&R Ice Cream Admin') </title>
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/notification.css') }}">
</head>

<body>
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

                <div class="notification" id="notifBtn">
                    <span class="material-symbols-outlined">notifications</span>

                    <!-- DROPDOWN -->
                    <div class="notification-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <span class="title">Notifications</span>

                            <div class="mark-read">
                                <span class="material-symbols-outlined">done_all</span>
                                <span>Mark all as read</span>
                            </div>
                        </div>

                        <div class="notif-tabs">
                            <span class="active">All</span>
                            <span class="unread-tab">Unread <span class="unread-count">2</span></span>
                        </div>


                        <div class="notif-list">

                            <!-- PROFILE UPDATE SAMPLE -->
                            <div class="notif-item unread">
                                <div class="notif-icon profile">
                                    <img src="{{ asset('img/kyle.jpg') }}" alt="Profile">
                                </div>


                                <div class="notif-text">
                                    <span>
                                        <strong>Kyle Reganion</strong>
                                        <span class="muted">changed his</span>
                                        <span class="highlight">Phone Number</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- PROFILE UPDATE SAMPLE -->
                            <div class="notif-item unread">
                                <div class="notif-icon profile">
                                    <img src="{{ asset('img/jade.jpg') }}" alt="Profile">
                                </div>


                                <div class="notif-text">
                                    <span>
                                        <strong>Jade Sestual</strong>
                                        <span class="muted">changed his</span>
                                        <span class="highlight">Phone Number</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item unread">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Strawberry</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Mango</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Ube Macapuno</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Coookies & Cream</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Vanilla</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Pandan</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>

                            <!-- DELIVERY SUCCESS SAMPLE -->
                            <div class="notif-item">
                                <div class="notif-icon delivered">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>

                                <div class="notif-text">
                                    <span>
                                        <strong>Mango Graham</strong>
                                        <span class="muted">delivered</span>
                                        <span class="highlight">Successfully</span>
                                    </span>

                                    <span class="notif-time">2 hrs ago</span>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="content-area">
            @yield('content')
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
    const markAll = document.querySelector(".mark-read");
    const unreadCountElem = document.querySelector(".unread-count");
    const tabs = document.querySelectorAll(".notif-tabs span");
    const notifList = document.querySelector(".notif-list");

    // Helper function to update unread count
    function updateUnreadCount() {
        const unreadItems = document.querySelectorAll(".notif-item.unread");
        unreadCountElem.textContent = unreadItems.length;
        unreadCountElem.style.display = unreadItems.length === 0 ? "none" : "inline-block";
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

    // Toggle dropdown on bell click
    if (notifBtn) {
        notifBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            notifDropdown.style.display =
                notifDropdown.style.display === "block" ? "none" : "block";
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

    // Click on individual notification to mark as read
    notifList.addEventListener("click", (e) => {
        const item = e.target.closest(".notif-item");
        if (!item) return;

        if (item.classList.contains("unread")) {
            item.classList.remove("unread");
            updateUnreadCount();
        }
    });

    // Mark all as read
    if (markAll) {
        markAll.addEventListener("click", () => {
            const allUnread = document.querySelectorAll(".notif-item.unread");
            allUnread.forEach(item => item.classList.remove("unread"));
            updateUnreadCount();
            markAll.classList.add("read");
        });
    }

    // Tab switching (All / Unread)
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const allItems = document.querySelectorAll(".notif-item");
            if (tab.textContent.includes("All")) {
                allItems.forEach(item => item.style.display = "flex");
            } else if (tab.textContent.includes("Unread")) {
                allItems.forEach(item => {
                    item.style.display = item.classList.contains("unread") ? "flex" : "none";
                });
            }
        });
    });

    // Initialize unread count
    updateUnreadCount();
</script>

</body>

</html>
