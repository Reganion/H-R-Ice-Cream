@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Customer Details')
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/app.css') }}">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow: hidden;
            background: rgb(242, 242, 242);
            border-top-left-radius: 30px;
            min-height: 0;
        }

        .customer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 20px;
        }

        .customer-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .customer-header h2 {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
        }

        .customer-tabs {
            display: inline-flex;
            background: #fff;
            border-radius: 10px;
        }

        .customer-tabs button {
            border: none;
            background: transparent;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #000;
            cursor: pointer;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .customer-tabs button.active {
            background: #0066ff;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.35);
        }

        .customer-search {
            position: relative;
            width: min(360px, 100%);
        }

        .customer-search span {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #888;
        }

        .customer-search input {
            width: 100%;
            padding: 12px 40px;
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        .customer-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .customer-scroll {
            flex: 1;
            min-height: 0;
            overflow: auto;
            padding-bottom: 12px;
        }

        .customer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .customer-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .card-orders-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 999px;
            background: #e0ecff;
            color: #0056ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .card-orders-btn:hover {
            background: #cce0ff;
            transform: translateY(-1px);
        }

        .card-orders-btn .material-symbols-outlined {
            font-size: 18px;
            line-height: 1;
        }

        .orders-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 3000;
        }

        .orders-modal-overlay.show {
            display: flex;
        }

        .orders-modal-card {
            width: min(920px, 100%);
            max-height: min(88vh, 760px);
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
        }

        .orders-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
        }

        .orders-modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .orders-modal-close {
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 999px;
            background: #f3f4f6;
            color: #374151;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .orders-modal-close .material-symbols-outlined {
            font-size: 20px;
            line-height: 1;
        }

        .orders-modal-body {
            padding: 14px 18px 18px;
            overflow: auto;
        }

        .orders-modal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-modal-table th,
        .orders-modal-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 8px;
            text-align: left;
            font-size: 13px;
            vertical-align: top;
        }

        .orders-modal-table th {
            color: #64748b;
            font-weight: 600;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
        }

        .orders-modal-empty {
            text-align: center;
            color: #6b7280;
            padding: 22px 12px;
            font-size: 14px;
        }

        .customer-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px;
        }

        .customer-card h4 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        .customer-tags {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .customer-tags .code {
            background: #eee;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 14px;
            text-align: left;
        }

        .customer-info p {
            margin: 0;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 13px;
        }

        .customer-info p strong {
            color: #4b5563;
            font-weight: 600;
            white-space: nowrap;
        }

        .customer-info p span {
            text-align: right;
            color: #111827;
            word-break: break-word;
        }

        .customer-status-row {
            margin-top: auto;
            display: flex;
            justify-content: center;
        }

        .status {
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status.active {
            background: #d4f5e9;
            color: #0b8f5a;
        }

        .status.inactive {
            background: #ffd6d6;
            color: #d40000;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #666;
            background: #fff;
            border-radius: 14px;
        }

        @media (max-width: 900px) {
            .customer-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

</head>

<body>
    @section('content')
        @include('admin.partials.alert')

        <div class="content-area">
            <div class="customer-header">
                <div class="customer-left">
                    <h2>Customer list</h2>
                    @php
                        $customerCountAll = $customers->count();
                        $customerCountActive = $customers->where('status', 'active')->count();
                        $customerCountInactive = $customers->where('status', 'inactive')->count();
                    @endphp
                    <div class="customer-tabs">
                        <button type="button" class="active" data-filter="all">All ({{ $customerCountAll }})</button>
                        <button type="button" data-filter="active">Active ({{ $customerCountActive }})</button>
                        <button type="button" data-filter="inactive">Inactive ({{ $customerCountInactive }})</button>
                    </div>
                </div>

                <div class="customer-search">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="customerSearchInput" placeholder="Search by customer name">
                </div>
            </div>

            <div class="customer-body">
                <div class="customer-scroll">
                    <div class="customer-grid" id="customerGrid">
                        @forelse ($customers as $customer)
                            @php
                                $statusValue = ($customer->status === 'inactive') ? 'inactive' : 'active';
                                $statusLabel = ucfirst($statusValue);
                                $primaryAddress = $customer->addresses->first();
                                $fullAddress = collect([
                                    $primaryAddress?->street_name,
                                    $primaryAddress?->barangay,
                                    $primaryAddress?->city,
                                    $primaryAddress?->province,
                                    $primaryAddress?->postal_code,
                                ])->filter()->implode(', ');
                            @endphp
                            <div class="customer-card" data-status="{{ $statusValue }}" data-name="{{ strtolower($customer->full_name) }}">
                                <button type="button" class="card-orders-btn js-view-orders" data-customer-name="{{ e($customer->full_name) }}" title="View customer orders" aria-label="View customer orders">
                                    <span class="material-symbols-outlined">receipt_long</span>
                                </button>
                                <img src="{{ $customer->image ? asset($customer->image) : asset('img/default-user.png') }}" alt="{{ $customer->full_name }}">
                                <h4>{{ $customer->full_name }}</h4>
                                <div class="customer-tags">
                                    <span class="code">CUS{{ str_pad((string) $customer->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="customer-info">
                                    <p><strong>Phone</strong><span>{{ $primaryAddress?->contact_no ?? $customer->contact_no ?? '—' }}</span></p>
                                    <p><strong>Email</strong><span>{{ $customer->email ?? '—' }}</span></p>
                                    <p><strong>Address</strong><span>{{ $fullAddress !== '' ? $fullAddress : '—' }}</span></p>
                                </div>
                                <div class="customer-status-row">
                                    <span class="status {{ $statusValue }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                No customers found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="orders-modal-overlay" id="customerOrdersModal" aria-hidden="true">
            <div class="orders-modal-card">
                <div class="orders-modal-header">
                    <h3 id="customerOrdersTitle">Order Records</h3>
                    <button type="button" class="orders-modal-close" id="customerOrdersClose" aria-label="Close order records">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="orders-modal-body">
                    <table class="orders-modal-table">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Product</th>
                                <th>Schedule</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="customerOrdersBody">
                            <tr>
                                <td colspan="5" class="orders-modal-empty">Select a customer to view records.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            (function() {
                const tabs = Array.from(document.querySelectorAll(".customer-tabs button"));
                const cards = Array.from(document.querySelectorAll(".customer-card"));
                const searchInput = document.getElementById("customerSearchInput");
                const viewOrderButtons = Array.from(document.querySelectorAll(".js-view-orders"));
                const ordersModal = document.getElementById("customerOrdersModal");
                const ordersClose = document.getElementById("customerOrdersClose");
                const ordersBody = document.getElementById("customerOrdersBody");
                const ordersTitle = document.getElementById("customerOrdersTitle");
                let allOrders = null;

                function applyFilters() {
                    const activeTab = document.querySelector(".customer-tabs button.active");
                    const statusFilter = activeTab ? activeTab.dataset.filter : "all";
                    const keyword = (searchInput?.value || "").trim().toLowerCase();

                    cards.forEach((card) => {
                        const cardStatus = card.dataset.status || "active";
                        const cardName = card.dataset.name || "";
                        const matchesStatus = statusFilter === "all" || cardStatus === statusFilter;
                        const matchesSearch = keyword === "" || cardName.includes(keyword);
                        card.style.display = (matchesStatus && matchesSearch) ? "" : "none";
                    });
                }

                tabs.forEach((tab) => {
                    tab.addEventListener("click", () => {
                        tabs.forEach((btn) => btn.classList.remove("active"));
                        tab.classList.add("active");
                        applyFilters();
                    });
                });

                function escapeHtml(value) {
                    return String(value ?? "")
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#39;");
                }

                function normalize(value) {
                    return String(value || "").trim().toLowerCase().replace(/\s+/g, " ");
                }

                function closeOrdersModal() {
                    if (!ordersModal) return;
                    ordersModal.classList.remove("show");
                    ordersModal.setAttribute("aria-hidden", "true");
                }

                function renderOrderRows(rows) {
                    if (!ordersBody) return;
                    if (!rows.length) {
                        ordersBody.innerHTML = '<tr><td colspan="5" class="orders-modal-empty">No order records found for this customer.</td></tr>';
                        return;
                    }

                    ordersBody.innerHTML = rows.map((order) => {
                        const transaction = order.transaction_id ? "#" + escapeHtml(order.transaction_id) : "—";
                        const product = escapeHtml(order.product_name || "—");
                        const scheduleDate = escapeHtml(order.delivery_date_formatted || "—");
                        const scheduleTime = escapeHtml(order.delivery_time_formatted || "—");
                        const schedule = scheduleDate !== "—" && scheduleTime !== "—" ? (scheduleDate + ", " + scheduleTime) : (scheduleDate !== "—" ? scheduleDate : scheduleTime);
                        const amount = "PHP " + Number(order.amount || 0).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        const status = escapeHtml(order.status || "pending");

                        return '<tr>' +
                            '<td>' + transaction + '</td>' +
                            '<td>' + product + '</td>' +
                            '<td>' + schedule + '</td>' +
                            '<td>' + amount + '</td>' +
                            '<td>' + status + '</td>' +
                            '</tr>';
                    }).join("");
                }

                async function loadOrdersForCustomer(customerName) {
                    if (!ordersBody || !ordersTitle || !ordersModal) return;

                    ordersTitle.textContent = "Order Records - " + customerName;
                    ordersBody.innerHTML = '<tr><td colspan="5" class="orders-modal-empty">Loading order records...</td></tr>';
                    ordersModal.classList.add("show");
                    ordersModal.setAttribute("aria-hidden", "false");

                    try {
                        if (!Array.isArray(allOrders)) {
                            const response = await fetch("{{ route('admin.orders.list') }}", {
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest"
                                }
                            });
                            if (!response.ok) {
                                throw new Error("Unable to load orders.");
                            }
                            const payload = await response.json();
                            allOrders = Array.isArray(payload.orders) ? payload.orders : [];
                        }

                        const target = normalize(customerName);
                        const rows = allOrders.filter((order) => {
                            const orderName = normalize(order.customer_name);
                            return orderName === target || orderName.includes(target) || target.includes(orderName);
                        });
                        renderOrderRows(rows);
                    } catch (error) {
                        ordersBody.innerHTML = '<tr><td colspan="5" class="orders-modal-empty">Failed to load customer orders.</td></tr>';
                    }
                }

                viewOrderButtons.forEach((button) => {
                    button.addEventListener("click", () => {
                        const customerName = button.dataset.customerName || "";
                        if (!customerName) return;
                        loadOrdersForCustomer(customerName);
                    });
                });

                ordersClose?.addEventListener("click", closeOrdersModal);
                ordersModal?.addEventListener("click", (event) => {
                    if (event.target === ordersModal) {
                        closeOrdersModal();
                    }
                });
                document.addEventListener("keydown", (event) => {
                    if (event.key === "Escape") {
                        closeOrdersModal();
                    }
                });

                searchInput?.addEventListener("input", applyFilters);
            })();
        </script>

    @endsection
</body>

</html>
