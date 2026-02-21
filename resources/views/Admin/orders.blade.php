@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Order Management')
    <link rel="stylesheet" href="{{ asset('assets/css/Admin/order.css') }}">
</head>

<body>
    @section('content')
        <div class="content-area orders-page">

            <!-- HEADER -->
            <div class="orders-header">
                <h2>Order list</h2>

                <div class="orders-actions">
                    <div class="orders-search">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="searchInput" placeholder="Search by product or customer name">
                    </div>

                    <div class="filter-wrapper">
                        <button class="btn-filter" id="filterBtn">
                            <span class="material-symbols-outlined">tune</span>
                            Filter
                        </button>

                        <div class="filter-dropdown" id="filterDropdown">
                            <label><input type="checkbox" value="pending"> Pending</label>
                            <label><input type="checkbox" value="assigned"> Assigned</label>
                            <label><input type="checkbox" value="completed"> Completed</label>
                            <label><input type="checkbox" value="cancelled"> Cancelled</label>
                            <label><input type="checkbox" value="walk_in"> Walk-In</label>
                        </div>
                    </div>

                    <button class="btn-add" id="addOrderBtn">
                        <span class="material-symbols-outlined">add</span>
                        Add Order
                    </button>
                </div>
            </div>


            <!-- TABLE WRAPPER -->
            <div class="orders-table">

                <!-- SCROLL AREA -->
                <div class="table-scroll">
                    <table class="orders-data">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Customer Name</th>
                                <th>Transaction ID</th>
                                <th>Delivery Schedule</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>


                        <tbody id="orders-tbody">
                            @forelse ($orders as $order)
                                @php
                                    $statusRaw = trim((string) ($order->status ?? ''));
                                    $statusLower = strtolower($statusRaw);
                                    $statusKey = match ($statusLower) {
                                        'walk-in', 'walk_in', 'walk in' => 'walk_in',
                                        'assigned' => 'assigned',
                                        'completed', 'delivered' => 'completed',
                                        'cancelled' => 'cancelled',
                                        default => 'pending',
                                    };
                                @endphp
                                <tr data-order-id="{{ $order->id }}"
                                    data-product-name="{{ e($order->product_name ?? '') }}"
                                    data-product-type="{{ e($order->product_type ?? '') }}"
                                    data-product-image="{{ asset($order->product_image ?? 'img/default-product.png') }}"
                                    data-gallon-size="{{ e($order->gallon_size ?? '') }}"
                                    data-transaction-id="{{ e($order->transaction_id ?? '') }}"
                                    data-customer-name="{{ e($order->customer_name ?? '') }}"
                                    data-customer-phone="{{ e($order->customer_phone ?? '') }}"
                                    data-delivery-date="{{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('Y-m-d') : '' }}"
                                    data-delivery-time="{{ $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : '' }}"
                                    data-delivery-address="{{ e($order->delivery_address ?? '') }}"
                                    data-amount="{{ $order->amount ?? '' }}"
                                    data-payment-method="{{ e($order->payment_method ?? '') }}"
                                    data-status="{{ e($order->status ?? '') }}"
                                    data-status-key="{{ $statusKey }}"
                                    data-driver-id="{{ $order->driver_id ?? '' }}"
                                    data-driver-name="{{ e($order->driver->name ?? '') }}"
                                    data-driver-phone="{{ e($order->driver->phone ?? '') }}"
                                    data-driver-image-url="{{ $order->driver ? asset($order->driver->image ?? 'img/default-user.png') : '' }}">
                                    <!-- PRODUCT -->
                                    <td>
                                        <div class="cell-flex">
                                            <img src="{{ asset($order->product_image) }}" class="avatar">
                                            <div>
                                                <strong>{{ $order->product_name }}</strong>
                                                <small>{{ $order->product_type }} ({{ $order->gallon_size }})</small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- CUSTOMER -->
                                    <td>
                                        <div class="cell-flex">
                                            <img src="{{ asset($order->customer_image) }}" class="avatar">
                                            <div>
                                                <strong>{{ $order->customer_name }}</strong>
                                                <small>{{ $order->customer_phone }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- TRANSACTION -->
                                    <td>
                                        <strong>#{{ $order->transaction_id }}</strong>
                                        <small>{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d M Y') : '—' }}</small>
                                    </td>

                                    <!-- DELIVERY -->
                                    <td class="delivery-schedule-cell">
                                        <strong>{{ \Carbon\Carbon::parse($order->delivery_date)->format('d M') }},
                                            {{ \Carbon\Carbon::parse($order->delivery_time)->format('h:i A') }}</strong>
                                        <small class="delivery-address">{{ $order->delivery_address }}</small>
                                    </td>

                                    <!-- AMOUNT -->
                                    <td>
                                        <strong>₱{{ number_format($order->amount, 2) }}</strong>
                                        <small>{{ $order->payment_method }}</small>
                                    </td>

                                    <!-- STATUS -->
                                    <td>
                                        <span class="status-badge {{ $statusKey }}">
                                            ● {{ $statusRaw !== '' ? $statusRaw : 'Pending' }}
                                        </span>
                                    </td>

                                    <!-- ACTION -->
                                    <td>
                                        @if ($statusKey === 'walk_in')
                                            <button type="button" class="action-btn edit-order" title="Edit order">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                        @endif
                                        @if ($statusKey === 'assigned')
                                            <button class="action-btn reassign">
                                                <span class="material-symbols-outlined">person_edit</span>
                                            </button>
                                        @elseif($statusKey === 'completed' || $statusKey === 'cancelled')
                                            <button class="action-btn view">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                        @elseif($statusKey !== 'walk_in')
                                            <button class="action-btn assign">
                                                <span class="material-symbols-outlined">person_check</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:30px;">
                                        No orders found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
                <div id="showingText" class="showing-text" style="display:none;"></div>

                <!-- PAGINATION -->
                <div class="pagination-wrapper orders-pagination" id="ordersPagination">
                    <button type="button" class="nav-btn" id="prevBtn" aria-label="Previous page">
                        <span class="material-symbols-outlined">arrow_left_alt</span>
                        Previous
                    </button>
                    <div id="pageNumbers" class="pagination-numbers" role="navigation" aria-label="Page numbers"></div>
                    <button type="button" class="nav-btn" id="nextBtn" aria-label="Next page">
                        Next
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </button>
                </div>

            </div>
        </div>
    @endsection
    <!-- ASSIGN / RE-ASSIGN DRIVER MODAL -->
    <div class="assign-modal" id="assignModal" data-order-id="">
        <div class="assign-card">

            <!-- HEADER -->
            <div class="assign-header">
                <h3 id="assignModalTitle">Assign Driver</h3>
                <button class="close-assign" id="closeAssign" type="button">&times;</button>
            </div>

            <!-- ORDER INFO -->
            <div class="assign-order">
                <div class="order-left">
                    <img id="assignProductImage" src="{{ asset('img/gallon.png') }}" alt="">
                    <div>
                        <strong id="assignProduct">—</strong>
                        <small id="assignSchedule">—</small>
                        <small id="assignTxn">—</small>
                    </div>
                </div>

                <div class="order-right">
                    <strong id="assignCustomer">—</strong>
                    <small id="assignPhone">—</small>
                    <small id="assignAddress">—</small>
                </div>
            </div>

            <!-- CURRENT ASSIGNED DRIVER (for Re-Assign only) -->
            <div class="assign-section" id="assignCurrentDriverSection" style="display: none;">
                <span class="available-badge" style="background: #e3f2fd; color: #1976d2;">Assigned Driver</span>
                <div class="driver-list" style="margin-top: 8px;">
                    <div class="driver-item" style="cursor: default;">
                        <div class="driver-info">
                            <img id="assignCurrentDriverImage" src="{{ asset('img/default-user.png') }}" alt="">
                            <div>
                                <strong id="assignCurrentDriverName">—</strong>
                                <small id="assignCurrentDriverPhone">—</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AVAILABLE DRIVERS -->
            <div class="assign-section">
                <span class="available-badge">Available driver</span>
                <div class="driver-list" id="assignDriverList">
                    <div class="driver-list-loading" id="assignDriverListLoading">Loading drivers…</div>
                    <div class="driver-list-items" id="assignDriverListItems"></div>
                </div>
            </div>

        </div>
    </div>
    <!-- ADD ORDER MODAL -->
    <div class="add-modal" id="addModal">
        <div class="add-card">

            <!-- HEADER -->
            <div class="assign-header">
                <h3>Add Order</h3>
                <button class="close-assign" id="closeAdd">&times;</button>
            </div>

            <!-- FORM -->
            <div class="assign-section">
                <form method="POST" action="{{ route('admin.orders.walkin') }}">
                    @csrf

                    <div style="display:flex; flex-direction:column; gap:12px;">

                        <!-- FLAVOR -->
                        <div class="form-group">
                            <label>Flavor</label>

                            <div class="custom-select" id="flavorSelect">
                                <div class="select-trigger">
                                    <span class="selected-text">Select Flavor</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>

                                <div class="select-options">
                                    @foreach ($flavors as $flavor)
                                        <div class="option" data-value="{{ $flavor->name }}"
                                            data-category="{{ $flavor->category }}">
                                            {{ $flavor->name }}
                                        </div>
                                    @endforeach
                                </div>

                                <input type="hidden" name="product_name" required>
                            </div>
                        </div>

                        <!-- FLAVOR TYPE (AUTO) -->
                        <div class="form-group">
                            <label>Flavor Type</label>

                            <input type="text" class="form-input" id="flavorTypeDisplay"
                                placeholder="Auto-filled" readonly>

                            <input type="hidden" name="product_type" id="flavorTypeInput" required>
                        </div>

                        <!-- GALLON SIZE -->
                        <div class="form-group">
                            <label>Size of Gallon</label>

                            <div class="custom-select">
                                <div class="select-trigger">
                                    <span class="selected-text">Size of Gallon</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>

                                <div class="select-options">
                                    @foreach ($gallons as $gallon)
                                        <div class="option" data-value="{{ $gallon->size }}">
                                            {{ $gallon->size }}
                                        </div>
                                    @endforeach
                                </div>

                                <input type="hidden" name="gallon_size" required>
                            </div>
                        </div>

                        <!-- CUSTOMER -->
                        <input type="text" name="customer_name" placeholder="Customer Name" required
                            class="form-input">
                        <input type="text" name="customer_phone" placeholder="Customer Number" required
                            class="form-input">

                        <!-- DELIVERY -->
                        <input type="date" name="delivery_date" required class="form-input">
                        <input type="time" name="delivery_time" required class="form-input">

                        <!-- ADDRESS -->
                        <textarea name="delivery_address" placeholder="Delivery Address" required class="form-input"></textarea>

                        <!-- AMOUNT -->
                        <input type="number" name="amount" placeholder="Amount" required class="form-input">

                        <!-- PAYMENT METHOD -->
                        <div class="form-group">
                            <label>Payment Method</label>

                            <div class="custom-select">
                                <div class="select-trigger">
                                    <span class="selected-text">Payment Method</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>

                                <div class="select-options">
                                    <div class="option" data-value="Cash of Delivery">Cash of Delivery</div>
                                    <div class="option" data-value="GCash">GCash</div>
                                    <div class="option" data-value="Paymaya">Paymaya</div>
                                </div>

                                <input type="hidden" name="payment_method" required>
                            </div>
                        </div>

                        <!-- STATUS -->
                        <input type="hidden" name="status" value="Walk-in">

                        <button type="submit" class="assign-driver-btn" style="width:100%;">
                            Save Order
                        </button>

                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- EDIT WALK-IN ORDER MODAL -->
    <div class="add-modal" id="editModal">
        <div class="add-card">
            <div class="assign-header">
                <h3>Edit Walk-In Order</h3>
                <button type="button" class="close-assign" id="closeEdit">&times;</button>
            </div>
            <div class="assign-section">
                <form method="POST" id="editOrderForm" action="">
                    @csrf
                    @method('PUT')
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div class="form-group">
                            <label>Flavor</label>
                            <div class="custom-select" id="editFlavorSelect">
                                <div class="select-trigger">
                                    <span class="selected-text" id="editProductNameText">Select Flavor</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>
                                <div class="select-options">
                                    @foreach ($flavors as $flavor)
                                        <div class="option" data-value="{{ $flavor->name }}" data-category="{{ $flavor->category ?? '' }}">{{ $flavor->name }}</div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="product_name" id="editProductName" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Flavor Type</label>
                            <input type="text" class="form-input" id="editFlavorTypeDisplay" placeholder="Auto-filled" readonly>
                            <input type="hidden" name="product_type" id="editProductType" required>
                        </div>
                        <div class="form-group">
                            <label>Size of Gallon</label>
                            <div class="custom-select" id="editGallonSelect">
                                <div class="select-trigger">
                                    <span class="selected-text" id="editGallonSizeText">Size of Gallon</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>
                                <div class="select-options">
                                    @foreach ($gallons as $gallon)
                                        <div class="option" data-value="{{ $gallon->size }}">{{ $gallon->size }}</div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="gallon_size" id="editGallonSize" required>
                            </div>
                        </div>
                        <input type="text" name="customer_name" id="editCustomerName" placeholder="Customer Name" required class="form-input">
                        <input type="text" name="customer_phone" id="editCustomerPhone" placeholder="Customer Number" required class="form-input">
                        <input type="date" name="delivery_date" id="editDeliveryDate" required class="form-input">
                        <input type="time" name="delivery_time" id="editDeliveryTime" required class="form-input">
                        <textarea name="delivery_address" id="editDeliveryAddress" placeholder="Delivery Address" required class="form-input"></textarea>
                        <input type="number" name="amount" id="editAmount" placeholder="Amount" required class="form-input" step="0.01">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <div class="custom-select" id="editPaymentSelect">
                                <div class="select-trigger">
                                    <span class="selected-text" id="editPaymentMethodText">Payment Method</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>
                                <div class="select-options">
                                    <div class="option" data-value="Cash of Delivery">Cash of Delivery</div>
                                    <div class="option" data-value="GCash">GCash</div>
                                    <div class="option" data-value="Paymaya">Paymaya</div>
                                </div>
                                <input type="hidden" name="payment_method" id="editPaymentMethod" required>
                            </div>
                        </div>
                        <button type="submit" class="assign-driver-btn" style="width:100%;">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("click", function(e) {

            // OPEN / CLOSE CUSTOM SELECT
            const trigger = e.target.closest(".select-trigger");
            if (trigger) {
                const select = trigger.closest(".custom-select");
                document.querySelectorAll(".custom-select").forEach(s => {
                    if (s !== select) s.classList.remove("open");
                });
                select.classList.toggle("open");
                return;
            }

            // OPTION SELECTED
            const option = e.target.closest(".option");
            if (option) {
                const select = option.closest(".custom-select");
                const hiddenInput = select.querySelector("input[type='hidden']");
                const text = select.querySelector(".selected-text");

                hiddenInput.value = option.dataset.value;
                text.textContent = option.textContent;
                select.classList.remove("open");

                // AUTO-FILL FLAVOR TYPE
                if (option.dataset.category) {
                    document.getElementById("flavorTypeDisplay").value = option.dataset.category;
                    document.getElementById("flavorTypeInput").value = option.dataset.category;
                }

                return;
            }

            // CLOSE ALL ON OUTSIDE CLICK
            document.querySelectorAll(".custom-select").forEach(s => s.classList.remove("open"));
        });
    </script>


    <script>
        /* ======================
        ADD ORDER MODAL (FIXED)
        ====================== */
        const addModal = document.getElementById("addModal");

        // OPEN (event delegation)
        document.addEventListener("click", function(e) {
            const addBtn = e.target.closest("#addOrderBtn");
            if (!addBtn) return;

            e.preventDefault();
            addModal.classList.add("show");
        });

        // CLOSE BUTTON
        document.addEventListener("click", function(e) {
            const closeBtn = e.target.closest("#closeAdd");
            if (!closeBtn) return;

            addModal.classList.remove("show");
        });

        // CLOSE ON BACKDROP
        addModal.addEventListener("click", function(e) {
            if (e.target === addModal) {
                addModal.classList.remove("show");
            }
        });
    </script>

    <script>
        /* ======================
           EDIT WALK-IN ORDER MODAL
        ====================== */
        const editModal = document.getElementById("editModal");
        const editForm = document.getElementById("editOrderForm");

        document.addEventListener("click", function(e) {
            const editBtn = e.target.closest(".edit-order");
            if (!editBtn) return;

            const row = editBtn.closest("tr");
            if (!row || !row.dataset.orderId) return;

            const id = row.dataset.orderId;
            const baseUrl = "{{ url('admin/orders') }}";
            editForm.action = baseUrl + "/" + id;

            document.getElementById("editProductName").value = row.dataset.productName || '';
            document.getElementById("editProductNameText").textContent = row.dataset.productName || 'Select Flavor';
            document.getElementById("editProductType").value = row.dataset.productType || '';
            document.getElementById("editFlavorTypeDisplay").value = row.dataset.productType || '';

            document.getElementById("editGallonSize").value = row.dataset.gallonSize || '';
            document.getElementById("editGallonSizeText").textContent = row.dataset.gallonSize || 'Size of Gallon';

            document.getElementById("editCustomerName").value = row.dataset.customerName || '';
            document.getElementById("editCustomerPhone").value = row.dataset.customerPhone || '';
            document.getElementById("editDeliveryDate").value = row.dataset.deliveryDate || '';
            document.getElementById("editDeliveryTime").value = row.dataset.deliveryTime || '';
            document.getElementById("editDeliveryAddress").value = row.dataset.deliveryAddress || '';
            document.getElementById("editAmount").value = row.dataset.amount || '';

            document.getElementById("editPaymentMethod").value = row.dataset.paymentMethod || '';
            document.getElementById("editPaymentMethodText").textContent = row.dataset.paymentMethod || 'Payment Method';

            editModal.classList.add("show");
        });

        document.addEventListener("click", function(e) {
            if (e.target.closest("#closeEdit")) {
                editModal.classList.remove("show");
            }
        });
        editModal.addEventListener("click", function(e) {
            if (e.target === editModal) {
                editModal.classList.remove("show");
            }
        });

        // Edit modal: when option selected in flavor/gallon/payment selects, update hidden + display
        document.addEventListener("click", function(e) {
            const option = e.target.closest(".option");
            if (!option) return;
            const select = option.closest(".custom-select");
            if (!select) return;
            const hiddenInput = select.querySelector("input[type='hidden']");
            const textEl = select.querySelector(".selected-text");
            if (hiddenInput && textEl) {
                hiddenInput.value = option.dataset.value || option.textContent.trim();
                textEl.textContent = option.textContent.trim();
                select.classList.remove("open");
                if (option.dataset.category && select.closest("#editOrderForm")) {
                    document.getElementById("editFlavorTypeDisplay").value = option.dataset.category;
                    document.getElementById("editProductType").value = option.dataset.category;
                }
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            let allRows = Array.from(document.querySelectorAll(".orders-data tbody tr[data-order-id]"));
            const ordersTbody = document.getElementById("orders-tbody");
            const searchInput = document.getElementById("searchInput");

            const filterBtn = document.getElementById("filterBtn");
            const filterDropdown = document.getElementById("filterDropdown");
            const filterChecks = filterDropdown.querySelectorAll("input[type='checkbox']");

            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");
            const pageNumbers = document.getElementById("pageNumbers");
            const pagination = document.querySelector(".pagination-wrapper");
            const showingText = document.getElementById("showingText");

            const rowsPerPage = 10;
            let currentPage = 1;
            let filteredRows = [...allRows];

            function normalizeStatusKey(status) {
                const s = String(status || '').trim().toLowerCase();
                if (s === 'walk-in' || s === 'walk_in' || s === 'walk in') return 'walk_in';
                if (s === 'assigned') return 'assigned';
                if (s === 'completed' || s === 'delivered') return 'completed';
                if (s === 'cancelled') return 'cancelled';
                return 'pending';
            }

            /* ======================
               FILTER TOGGLE
            ====================== */
            filterBtn.onclick = () => {
                filterDropdown.style.display =
                    filterDropdown.style.display === "block" ? "none" : "block";
            };

            document.addEventListener("click", e => {
                if (!filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
                    filterDropdown.style.display = "none";
                }
            });

            /* ======================
               SEARCH + FILTER
            ====================== */
            function applyFilters(resetPage = true) {
                const keyword = searchInput.value.toLowerCase().trim();
                const activeStatuses = Array.from(filterChecks)
                    .filter(c => c.checked)
                    .map(c => c.value);

                filteredRows = allRows.filter(row => {
                    const product = row.querySelector("td:nth-child(1)").innerText.toLowerCase();
                    const customer = row.querySelector("td:nth-child(2)").innerText.toLowerCase();

                    const statusClass = row.dataset.statusKey || normalizeStatusKey(row.dataset.status || '');

                    const matchSearch =
                        product.includes(keyword) || customer.includes(keyword);

                    const matchStatus =
                        activeStatuses.length === 0 || activeStatuses.includes(statusClass);

                    return matchSearch && matchStatus;
                });

                if (resetPage) currentPage = 1;
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (totalPages > 0 && currentPage > totalPages) currentPage = totalPages;
                render(currentPage);
            }

            const urlParams = new URLSearchParams(window.location.search);
            const customerKeyword = (urlParams.get("customer") || "").trim();
            if (customerKeyword !== "") {
                searchInput.value = customerKeyword;
            }

            searchInput.addEventListener("input", applyFilters);
            filterChecks.forEach(cb => cb.addEventListener("change", applyFilters));

            /* ======================
               PAGINATION
            ====================== */
            function render(page) {
                const totalRows = filteredRows.length;
                const totalPages = Math.ceil(totalRows / rowsPerPage);

                allRows.forEach(r => r.style.display = "none");

                if (totalRows === 0) {
                    pagination.style.setProperty("display", "none", "important");
                    if (showingText) { showingText.style.display = "block"; showingText.textContent = "No results found"; }
                    return;
                }

                /* When data is not beyond 10: hide pagination, show "Showing X data" only */
                const dataBeyond10 = totalRows > rowsPerPage;
                if (dataBeyond10) {
                    pagination.style.setProperty("display", "flex", "important");
                    if (showingText) showingText.style.display = "none";
                } else {
                    pagination.style.setProperty("display", "none", "important");
                    if (showingText) {
                        showingText.style.display = "block";
                        showingText.textContent = "Showing " + totalRows + " data";
                    }
                }

                filteredRows
                    .slice((page - 1) * rowsPerPage, page * rowsPerPage)
                    .forEach(r => r.style.display = "table-row");

                pageNumbers.innerHTML = "";
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.type = "button";
                    btn.textContent = i;
                    btn.className = "page-num" + (i === page ? " active" : "");
                    btn.setAttribute("aria-label", "Page " + i);
                    btn.setAttribute("aria-current", i === page ? "page" : "false");
                    (function(pageNum) {
                        btn.onclick = function() { currentPage = pageNum; render(pageNum); };
                    })(i);
                    pageNumbers.appendChild(btn);
                }

                prevBtn.disabled = page <= 1;
                nextBtn.disabled = page >= totalPages || totalPages <= 0;
            }

            prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; render(currentPage); } };
            nextBtn.onclick = () => {
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (currentPage < totalPages) { currentPage++; render(currentPage); }
            };

            /* ======================
               REALTIME: build one row from API order
            ====================== */
            function esc(s) {
                if (s == null || s === undefined) return '';
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }
            function escAttr(s) { return esc(s).replace(/"/g, '&quot;'); }

            function buildOrderRow(order) {
                const statusKey = normalizeStatusKey(order.status);
                const statusDisplay = String(order.status || '').trim() || 'Pending';
                const amountFormatted = typeof order.amount === 'number' ? order.amount.toFixed(2) : (parseFloat(order.amount) || 0).toFixed(2);
                let actionHtml = '';
                if (statusKey === 'walk_in') {
                    actionHtml = '<button type="button" class="action-btn edit-order" title="Edit order"><span class="material-symbols-outlined">edit</span></button>';
                } else if (statusKey === 'assigned') {
                    actionHtml = '<button class="action-btn reassign"><span class="material-symbols-outlined">person_edit</span></button>';
                } else if (statusKey === 'completed' || statusKey === 'cancelled') {
                    actionHtml = '<button class="action-btn view"><span class="material-symbols-outlined">visibility</span></button>';
                } else if (statusKey !== 'walk_in') {
                    actionHtml = '<button class="action-btn assign"><span class="material-symbols-outlined">person_check</span></button>';
                }
                return '<tr data-order-id="' + escAttr(String(order.id)) + '"' +
                    ' data-product-name="' + escAttr(order.product_name) + '"' +
                    ' data-product-type="' + escAttr(order.product_type) + '"' +
                    ' data-product-image="' + escAttr(order.product_image_url) + '"' +
                    ' data-gallon-size="' + escAttr(order.gallon_size) + '"' +
                    ' data-transaction-id="' + escAttr(order.transaction_id) + '"' +
                    ' data-customer-name="' + escAttr(order.customer_name) + '"' +
                    ' data-customer-phone="' + escAttr(order.customer_phone) + '"' +
                    ' data-delivery-date="' + escAttr(order.delivery_date) + '"' +
                    ' data-delivery-time="' + escAttr(order.delivery_time) + '"' +
                    ' data-delivery-address="' + escAttr(order.delivery_address) + '"' +
                    ' data-amount="' + escAttr(String(order.amount)) + '"' +
                    ' data-payment-method="' + escAttr(order.payment_method) + '"' +
                    ' data-status="' + escAttr(order.status) + '"' +
                    ' data-status-key="' + escAttr(statusKey) + '"' +
                    ' data-driver-id="' + escAttr(order.driver_id || '') + '"' +
                    ' data-driver-name="' + escAttr(order.driver_name || '') + '"' +
                    ' data-driver-phone="' + escAttr(order.driver_phone || '') + '"' +
                    ' data-driver-image-url="' + escAttr(order.driver_image_url || '') + '">' +
                    '<td><div class="cell-flex"><img src="' + escAttr(order.product_image_url) + '" class="avatar"><div><strong>' + esc(order.product_name) + '</strong><small>' + esc(order.product_type) + ' (' + esc(order.gallon_size) + ')</small></div></div></td>' +
                    '<td><div class="cell-flex"><img src="' + escAttr(order.customer_image_url) + '" class="avatar"><div><strong>' + esc(order.customer_name) + '</strong><small>' + esc(order.customer_phone) + '</small></div></div></td>' +
                    '<td><strong>#' + esc(order.transaction_id) + '</strong><small>' + esc(order.created_at_formatted) + '</small></td>' +
                    '<td class="delivery-schedule-cell"><strong>' + esc(order.delivery_date_formatted) + ', ' + esc(order.delivery_time_formatted) + '</strong><small class="delivery-address">' + esc(order.delivery_address) + '</small></td>' +
                    '<td><strong>₱' + esc(amountFormatted.replace(/\B(?=(\d{3})+(?!\d))/g, ',')) + '</strong><small>' + esc(order.payment_method) + '</small></td>' +
                    '<td><span class="status-badge ' + escAttr(statusKey) + '">● ' + esc(statusDisplay) + '</span></td>' +
                    '<td>' + actionHtml + '</td></tr>';
            }

            async function fetchAndRefreshOrders() {
                try {
                    const res = await fetch('{{ route("admin.orders.list") }}', { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!res.ok) return;
                    const data = await res.json();
                    const orders = data.orders || [];
                    if (orders.length === 0) {
                        ordersTbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:30px;">No orders found</td></tr>';
                    } else {
                        ordersTbody.innerHTML = orders.map(buildOrderRow).join('');
                    }
                    allRows = Array.from(document.querySelectorAll('.orders-data tbody tr[data-order-id]'));
                    applyFilters(false);
                } catch (e) { /* ignore network errors */ }
            }
            window.fetchAndRefreshOrders = fetchAndRefreshOrders;

            const POLL_INTERVAL_MS = 5000;
            setInterval(fetchAndRefreshOrders, POLL_INTERVAL_MS);

            render(currentPage);
        });
    </script>
    <script>
        /* ======================
        ASSIGN / RE-ASSIGN MODAL
        ====================== */
        const assignModal = document.getElementById("assignModal");
        const closeAssign = document.getElementById("closeAssign");
        const assignModalTitle = document.getElementById("assignModalTitle");
        const assignProductImage = document.getElementById("assignProductImage");
        const assignProduct = document.getElementById("assignProduct");
        const assignSchedule = document.getElementById("assignSchedule");
        const assignTxn = document.getElementById("assignTxn");
        const assignCustomer = document.getElementById("assignCustomer");
        const assignPhone = document.getElementById("assignPhone");
        const assignAddress = document.getElementById("assignAddress");
        const assignCurrentDriverSection = document.getElementById("assignCurrentDriverSection");
        const assignCurrentDriverImage = document.getElementById("assignCurrentDriverImage");
        const assignCurrentDriverName = document.getElementById("assignCurrentDriverName");
        const assignCurrentDriverPhone = document.getElementById("assignCurrentDriverPhone");
        const assignDriverListLoading = document.getElementById("assignDriverListLoading");
        const assignDriverListItems = document.getElementById("assignDriverListItems");

        const assignDriverUrl = "{{ route('admin.orders.drivers') }}";
        const assignOrderUrlBase = "{{ url('admin/orders') }}";

        function formatSchedule(dateStr, timeStr) {
            if (!dateStr || !timeStr) return '—';
            try {
                const d = new Date(dateStr + 'T' + timeStr);
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const day = d.getDate();
                const month = months[d.getMonth()];
                let h = d.getHours();
                const m = d.getMinutes();
                const ampm = h >= 12 ? 'pm' : 'am';
                h = h % 12 || 12;
                return day + ' ' + month + ', ' + (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ' ' + ampm;
            } catch (e) { return '—'; }
        }

        function openAssignModal(row, isReassign) {
            const orderId = row.dataset.orderId;
            if (!orderId) return;

            assignModal.dataset.orderId = orderId;
            assignProductImage.src = row.dataset.productImage || '';
            assignProduct.textContent = row.dataset.productName || '—';
            assignSchedule.textContent = formatSchedule(row.dataset.deliveryDate, row.dataset.deliveryTime);
            assignTxn.textContent = '#' + (row.dataset.transactionId || '—');
            assignCustomer.textContent = row.dataset.customerName || '—';
            assignPhone.textContent = row.dataset.customerPhone || '—';
            assignAddress.textContent = row.dataset.deliveryAddress || '—';

            if (isReassign) {
                assignModalTitle.textContent = 'Re-Assign Driver';
                assignCurrentDriverSection.style.display = 'block';
                assignCurrentDriverImage.src = row.dataset.driverImageUrl || "{{ asset('img/default-user.png') }}";
                assignCurrentDriverName.textContent = row.dataset.driverName || '—';
                assignCurrentDriverPhone.textContent = row.dataset.driverPhone || '—';
            } else {
                assignModalTitle.textContent = 'Assign Driver';
                assignCurrentDriverSection.style.display = 'none';
            }

            assignDriverListLoading.style.display = 'block';
            assignDriverListItems.innerHTML = '';

            fetch(assignDriverUrl, { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    assignDriverListLoading.style.display = 'none';
                    const drivers = data.drivers || [];
                    const currentDriverId = (row.dataset.driverId || '').toString();
                    const buttonLabel = isReassign ? 'Re-Assign' : 'Assign';
                    drivers.forEach(function(d) {
                        const item = document.createElement('div');
                        item.className = 'driver-item';
                        item.innerHTML =
                            '<div class="driver-info">' +
                            '<img src="' + (d.image_url || '') + '" alt="">' +
                            '<div><strong>' + (d.name || '') + '</strong><small>' + (d.phone || '') + '</small></div>' +
                            '</div>' +
                            '<button type="button" class="assign-driver-btn" data-driver-id="' + (d.id || '') + '">' + buttonLabel + '</button>';
                        assignDriverListItems.appendChild(item);
                    });
                    if (drivers.length === 0) {
                        assignDriverListItems.innerHTML = '<p style="padding:12px; color:#666;">No drivers available.</p>';
                    }
                })
                .catch(function() {
                    assignDriverListLoading.style.display = 'none';
                    assignDriverListItems.innerHTML = '<p style="padding:12px; color:#c00;">Failed to load drivers.</p>';
                });

            assignModal.classList.add('show');
        }

        document.addEventListener("click", function(e) {
            const assignBtn = e.target.closest(".action-btn.assign");
            const reassignBtn = e.target.closest(".action-btn.reassign");
            const row = (assignBtn || reassignBtn) ? (assignBtn || reassignBtn).closest("tr") : null;
            if (assignBtn && row) {
                e.preventDefault();
                openAssignModal(row, false);
                return;
            }
            if (reassignBtn && row) {
                e.preventDefault();
                openAssignModal(row, true);
                return;
            }

            const assignDriverBtn = e.target.closest(".assign-driver-btn[data-driver-id]");
            if (assignDriverBtn && assignModal.classList.contains("show")) {
                e.preventDefault();
                const orderId = assignModal.dataset.orderId;
                const driverId = assignDriverBtn.dataset.driverId;
                if (!orderId || !driverId) return;
                assignDriverBtn.disabled = true;

                const assignPostUrl = assignOrderUrlBase + '/' + orderId + '/assign';
                var csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                if (!csrfToken) csrfToken = '{{ csrf_token() }}';
                fetch(assignPostUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ driver_id: parseInt(driverId, 10) })
                })
                .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
                .then(function(result) {
                    if (result.ok && result.data.success) {
                        assignModal.classList.remove('show');
                        if (typeof fetchAndRefreshOrders === 'function') fetchAndRefreshOrders();
                    } else {
                        assignDriverBtn.disabled = false;
                        alert(result.data.message || 'Failed to assign driver.');
                    }
                })
                .catch(function() {
                    assignDriverBtn.disabled = false;
                    alert('Failed to assign driver.');
                });
            }
        });

        closeAssign.addEventListener("click", function() {
            assignModal.classList.remove("show");
        });

        assignModal.addEventListener("click", function(e) {
            if (e.target === assignModal) assignModal.classList.remove("show");
        });
    </script>

</body>

</html>
