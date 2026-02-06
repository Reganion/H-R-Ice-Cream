@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
    @section('title', 'Dashboard')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">


    <link rel="stylesheet" href="{{ asset('assets/css/Admin/dashboard.css') }}">
</head>

<body>
    @section('content')
        <div class="content-area">

            <!-- SUMMARY BOXES -->
            <div class="summary-boxes">
                <div class="summary-box">
                    <h4>Total Orders</h4>
                    <h2>{{ $totalOrders ?? 0 }} <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: {{ $totalLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Assigned Orders</h4>
                    <h2>{{ $assignedCount ?? 0 }} <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: {{ $assignedLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Pending Orders</h4>
                    <h2>{{ $pendingCount ?? 0 }} <span class="icon" style="background:#f59e0b"></span></h2>
                    <p>Last month: {{ $pendingLastMonth ?? 0 }}</p>
                </div>

                <div class="summary-box">
                    <h4>Delivered Orders</h4>
                    <h2>{{ $deliveredCount ?? 0 }} <span class="icon" style="background:#22c55e"></span></h2>
                    <p>Last month: {{ $deliveredLastMonth ?? 0 }}</p>
                </div>
            </div>

            <!-- TABS -->
            <div class="order-tabs">
                <button class="tab-btn active">All Orders</button>
                <button class="tab-btn">New Orders</button>
                <button class="tab-btn">Assigned Orders</button>
                <button class="tab-btn">Pending Orders</button>
                <button class="tab-btn">Delivered Orders</button>
            </div>

            <!-- DATE FILTER -->
            <div class="date-filter">
                <div class="date-input-wrapper">
                    <input type="date" id="startDate" />
                    <span>to</span>
                    <input type="date" id="endDate" />
                </div>
                <div class="date-filter-box" id="dateDisplayBox">
                    <span class="material-symbols-outlined">calendar_today</span>
                    <span id="dateFormatted" class="formatted-date"></span>
                </div>
            </div>



            <!-- ORDERS TABLE -->
            <div class="orders-table">
                <!-- Scrollable area -->
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">deployed_code</span>
                                        Transaction ID
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">person</span>
                                        Customer Name
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">event_available</span>
                                        Delivery Schedule
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">icecream</span>
                                        Product Name
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">payments</span>
                                        Product Price
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        <span class="material-symbols-outlined">android_cell_4_bar</span>
                                        Status
                                    </span>
                                </th>
                            </tr>
                        </thead>


                        <tbody>
                            @php
                                $orders = $orders ?? collect();
                            @endphp
                            @forelse($orders as $order)
                                @php
                                    $createdAt = $order->created_at ? \Carbon\Carbon::parse($order->created_at) : null;
                                    $deliveryDate = $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date) : null;
                                    $deliveryTime = $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time) : null;
                                    $orderDateStr = $createdAt ? $createdAt->format('Y-m-d') : '';
                                    $deliverySchedule = $deliveryDate ? $deliveryDate->format('d M Y') . ($deliveryTime ? ', ' . $deliveryTime->format('h:i A') : '') : '—';
                                    $status = strtolower($order->status ?? 'pending');
                                    $displayStatus = $status;
                                    if ($status === 'pending' && $createdAt) {
                                        $displayStatus = $createdAt->gte(now()->subMinutes(5)) ? 'new' : 'pending';
                                    }
                                    $statusClass = 'status-pending';
                                    if ($displayStatus === 'delivered') $statusClass = 'status-delivered';
                                    elseif ($displayStatus === 'assigned') $statusClass = 'status-assigned';
                                    elseif ($displayStatus === 'new') $statusClass = 'status-new';
                                    elseif ($displayStatus === 'cancelled') $statusClass = 'status-cancelled';
                                    $statusLabel = $displayStatus === 'new' ? 'New' : ucfirst($displayStatus);
                                @endphp
                                <tr data-status="{{ $displayStatus }}" data-order-date="{{ $orderDateStr }}">
                                    <td>
                                        <div class="td-title">#{{ $order->transaction_id }}</div>
                                        <div class="td-sub">{{ $createdAt ? $createdAt->format('d M Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">{{ $order->customer_name ?? '—' }}</div>
                                        <div class="td-sub">{{ $order->customer_phone ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">{{ $deliverySchedule }}</div>
                                        <div class="td-sub">{{ $order->delivery_address ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">{{ $order->product_name ?? '—' }}</div>
                                        <div class="td-sub">{{ ($order->product_type ?? '') . ($order->gallon_size ? ' (' . $order->gallon_size . ')' : '') ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="td-title">₱{{ number_format((float)($order->amount ?? 0), 0) }}</div>
                                        <div class="td-sub">{{ $order->payment_method ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="status-dot {{ $statusClass }}"></span>
                                        <span class="td-title">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #888;">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="showingText" style="text-align:center; margin:10px 0; color:#555; font-size:14px; display:none;">
                </div>



                <!-- Pagination always visible at bottom -->
                <div class="pagination">
                    <button id="prevBtn" class="page-nav">
                        <span class="material-symbols-outlined">arrow_left_alt</span>
                        <span class="nav-text">Previous</span>
                    </button>

                    <div id="pageNumbers" class="page-numbers"></div>

                    <button id="nextBtn" class="page-nav">
                        <span class="nav-text">Next</span>
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </button>
                </div>


            </div>
        </div>
    @endsection
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tbody = document.querySelector(".orders-table tbody");
            const rows = Array.from(tbody.querySelectorAll("tr[data-status]"));
            const pageNumbersContainer = document.getElementById("pageNumbers");
            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");
            const pagination = document.querySelector(".pagination");
            const showingText = document.getElementById("showingText");

            const rowsPerPage = 10;
            let currentPage = 1;
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            // ✔ RULE: If total rows <= 10 → show text, hide pagination
            if (totalRows <= rowsPerPage) {
                pagination.style.display = "none";

                // show “Showing X out of 10”
                showingText.style.display = "block";
                showingText.textContent = `Showing ${totalRows} data`;

                // show all rows
                rows.forEach(row => row.style.display = "");
                return; // Stop here — no need to run pagination code
            }

            showingText.style.display = "none";
            pagination.style.display = "flex";

            function renderPageButtons() {
                pageNumbersContainer.innerHTML = "";
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.className = i === currentPage ? "active" : "";
                    btn.addEventListener("click", () => {
                        currentPage = i;
                        displayRows(currentPage);
                    });
                    pageNumbersContainer.appendChild(btn);
                }
            }

            function displayRows(page) {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                rows.forEach((row, idx) => {
                    row.style.display = idx >= start && idx < end ? "" : "none";
                });

                prevBtn.disabled = page === 1;
                nextBtn.disabled = page === totalPages;

                const btns = pageNumbersContainer.querySelectorAll("button");
                btns.forEach((b, idx) => {
                    b.classList.toggle("active", idx + 1 === page);
                });

                document.querySelector(".table-scroll").scrollTop = 0;
            }

            prevBtn.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayRows(currentPage);
                }
            });

            nextBtn.addEventListener("click", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayRows(currentPage);
                }
            });

            document.addEventListener("dashboardFiltersApplied", function() {
                const visible = rows.filter(r => r.style.display !== "none");
                const total = visible.length;
                const pages = Math.max(1, Math.ceil(total / rowsPerPage));
                currentPage = currentPage > pages ? pages : currentPage;
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const toShow = visible.slice(start, end);
                rows.forEach(r => { r.style.display = toShow.includes(r) ? "" : "none"; });
                pageNumbersContainer.innerHTML = "";
                for (let i = 1; i <= pages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.className = i === currentPage ? "active" : "";
                    const pageNum = i;
                    btn.addEventListener("click", () => { currentPage = pageNum; document.dispatchEvent(new CustomEvent("dashboardFiltersApplied")); });
                    pageNumbersContainer.appendChild(btn);
                }
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === pages;
                showingText.style.display = total <= rowsPerPage && total > 0 ? "block" : "none";
                showingText.textContent = total <= rowsPerPage && total > 0 ? "Showing " + total + " data" : (total === 0 ? "No data to show" : "");
                pagination.style.display = total > rowsPerPage ? "flex" : "none";
            });

            renderPageButtons();
            displayRows(currentPage);
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rows = document.querySelectorAll(".orders-table tbody tr[data-status]");
            const searchInput = document.querySelector(".search-bar input");
            const tabButtons = document.querySelectorAll(".tab-btn");
            const startDateInput = document.getElementById("startDate");
            const endDateInput = document.getElementById("endDate");

            function formatDateReadable(dateString) {
                if (!dateString) return "";
                const d = new Date(dateString);
                const day = d.getDate();
                const month = d.toLocaleString("en-US", { month: "short" });
                const year = d.getFullYear();
                return `${day} ${month}, ${year}`;
            }

            function updateDateDisplay() {
                const start = document.getElementById("startDate").value;
                const end = document.getElementById("endDate").value;
                const display = document.getElementById("dateFormatted");
                if (start && end) {
                    display.textContent = `${formatDateReadable(start)} to ${formatDateReadable(end)}`;
                } else if (start) {
                    display.textContent = formatDateReadable(start);
                } else if (end) {
                    display.textContent = formatDateReadable(end);
                } else {
                    display.textContent = "";
                }
            }

            if (startDateInput) startDateInput.addEventListener("change", updateDateDisplay);
            if (endDateInput) endDateInput.addEventListener("change", updateDateDisplay);

            function getTabStatusFilter(activeTabText) {
                const t = activeTabText.trim();
                if (t === "All Orders") return null;
                if (t === "New Orders") return "new";
                if (t === "Assigned Orders") return "assigned";
                if (t === "Pending Orders") return "pending";
                if (t === "Delivered Orders") return "delivered";
                return null;
            }

            function applyFilters() {
                const searchText = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : "";
                const activeTab = document.querySelector(".tab-btn.active");
                const activeTabText = activeTab ? activeTab.textContent.trim() : "All Orders";
                const statusFilter = getTabStatusFilter(activeTabText);
                const startDate = startDateInput && startDateInput.value ? startDateInput.value : "";
                const endDate = endDateInput && endDateInput.value ? endDateInput.value : "";

                rows.forEach(row => {
                    const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();
                    const orderDate = row.getAttribute("data-order-date") || "";
                    const rowText = row.textContent.toLowerCase();

                    let matchesSearch = !searchText || rowText.includes(searchText);
                    let matchesTab = !statusFilter || rowStatus === statusFilter;
                    let matchesDate = true;
                    if (startDate && orderDate < startDate) matchesDate = false;
                    if (endDate && orderDate > endDate) matchesDate = false;

                    row.style.display = (matchesSearch && matchesTab && matchesDate) ? "" : "none";
                });

                // Re-run pagination logic so "visible" rows are repaginated (optional: trigger custom event)
                const paginationEvent = new CustomEvent("dashboardFiltersApplied");
                document.dispatchEvent(paginationEvent);
            }

            if (searchInput) searchInput.addEventListener("keyup", applyFilters);
            if (startDateInput) startDateInput.addEventListener("change", applyFilters);
            if (endDateInput) endDateInput.addEventListener("change", applyFilters);

            tabButtons.forEach(btn => {
                btn.addEventListener("click", () => {
                    tabButtons.forEach(b => b.classList.remove("active"));
                    btn.classList.add("active");
                    applyFilters();
                });
            });
        });
    </script>

</body>

</html>
