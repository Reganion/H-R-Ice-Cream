@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Order Management')
    <link rel="stylesheet" href="{{ asset('css/admin/order.css') }}">
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
                            <label><input type="checkbox" value="new_order"> New Order</label>
                            <label><input type="checkbox" value="pending"> Pending</label>
                            <label><input type="checkbox" value="assigned"> Assigned</label>
                            <label><input type="checkbox" value="delivered"> Delivered</label>
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


                        <tbody>
                            @forelse ($orders as $order)
                                <tr data-order-id="{{ $order->id }}"
                                    data-product-name="{{ e($order->product_name ?? '') }}"
                                    data-product-type="{{ e($order->product_type ?? '') }}"
                                    data-gallon-size="{{ e($order->gallon_size ?? '') }}"
                                    data-customer-name="{{ e($order->customer_name ?? '') }}"
                                    data-customer-phone="{{ e($order->customer_phone ?? '') }}"
                                    data-delivery-date="{{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('Y-m-d') : '' }}"
                                    data-delivery-time="{{ $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : '' }}"
                                    data-delivery-address="{{ e($order->delivery_address ?? '') }}"
                                    data-amount="{{ $order->amount ?? '' }}"
                                    data-payment-method="{{ e($order->payment_method ?? '') }}"
                                    data-status="{{ e($order->status ?? '') }}">
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
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($order->delivery_date)->format('d M') }},
                                            {{ \Carbon\Carbon::parse($order->delivery_time)->format('h:i A') }}</strong>
                                        <small>{{ $order->delivery_address }}</small>
                                    </td>

                                    <!-- AMOUNT -->
                                    <td>
                                        <strong>₱{{ number_format($order->amount, 2) }}</strong>
                                        <small>{{ $order->payment_method }}</small>
                                    </td>

                                    <!-- STATUS -->
                                    <td>
                                        <span class="status-badge {{ $order->status }}">
                                            ● {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>

                                    <!-- ACTION -->
                                    <td>
                                        @if ($order->status === 'walk_in')
                                            <button type="button" class="action-btn edit-order" title="Edit order">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                        @endif
                                        @if ($order->status === 'assigned')
                                            <button class="action-btn reassign">
                                                <span class="material-symbols-outlined">person_edit</span>
                                            </button>
                                        @elseif($order->status === 'delivered')
                                            <button class="action-btn view">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                        @elseif($order->status !== 'walk_in')
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
                <div id="showingText" style="text-align:center; margin:10px 0; color:#555; font-size:14px; display:none;">
                </div>

                <!-- PAGINATION -->
                <div class="pagination-wrapper">
                    <button class="nav-btn" id="prevBtn">
                        <span class="material-symbols-outlined">arrow_left_alt</span>
                        Previous
                    </button>

                    <div id="pageNumbers"></div>

                    <button class="nav-btn" id="nextBtn">
                        Next
                        <span class="material-symbols-outlined">arrow_right_alt</span>
                    </button>
                </div>

            </div>
        </div>
    @endsection
    <!-- ASSIGN DRIVER MODAL -->
    <div class="assign-modal" id="assignModal">
        <div class="assign-card">

            <!-- HEADER -->
            <div class="assign-header">
                <h3>Assign Driver</h3>
                <button class="close-assign" id="closeAssign">&times;</button>
            </div>

            <!-- ORDER INFO -->
            <div class="assign-order">
                <div class="order-left">
                    <img src="{{ asset('flavors/Strawberry.png') }}">
                    <div>
                        <strong id="assignProduct">Strawberry</strong>
                        <small id="assignSchedule">09 Dec, 10:00 am</small>
                        <small id="assignTxn">#12345678</small>
                    </div>
                </div>

                <div class="order-right">
                    <strong id="assignCustomer">Alma Fe Pepania</strong>
                    <small id="assignPhone">09123456789</small>
                    <small id="assignAddress">Palm Tree Village</small>
                </div>
            </div>

            <!-- DRIVER LIST -->
            <div class="assign-section">
                <span class="available-badge">Available driver</span>

                <div class="driver-list">
                    <div class="driver-item">
                        <div class="driver-info">
                            <img src="{{ asset('img/johnlloyd.jpg') }}">
                            <div>
                                <strong>Nathan Rozano</strong>
                                <small>09123456789</small>
                            </div>
                        </div>
                        <button class="assign-driver-btn">Assign</button>
                    </div>

                    <div class="driver-item">
                        <div class="driver-info">
                            <img src="{{ asset('img/jade.jpg') }}">
                            <div>
                                <strong>Ronel Garcia</strong>
                                <small>09123456789</small>
                            </div>
                        </div>
                        <button class="assign-driver-btn">Assign</button>
                    </div>

                    <div class="driver-item">
                        <div class="driver-info">
                            <img src="{{ asset('img/kyle.jpg') }}">
                            <div>
                                <strong>Kyle Reganion</strong>
                                <small>09082731631</small>
                            </div>
                        </div>
                        <button class="assign-driver-btn">Assign</button>
                    </div>
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
                        <input type="hidden" name="status" value="walk_in">

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

            const allRows = Array.from(document.querySelectorAll(".orders-data tbody tr"));
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
            function applyFilters() {
                const keyword = searchInput.value.toLowerCase().trim();
                const activeStatuses = Array.from(filterChecks)
                    .filter(c => c.checked)
                    .map(c => c.value);

                filteredRows = allRows.filter(row => {
                    const product = row.querySelector("td:nth-child(1)").innerText.toLowerCase();
                    const customer = row.querySelector("td:nth-child(2)").innerText.toLowerCase();



                    const statusEl = row.querySelector(".status-badge");
                    const statusClass = [...statusEl.classList].find(c => ["new_order", "pending",
                        "assigned",
                        "delivered", "walk_in"
                    ].includes(c));


                    const matchSearch =
                        product.includes(keyword) || customer.includes(keyword);

                    const matchStatus =
                        activeStatuses.length === 0 || activeStatuses.includes(statusClass);

                    return matchSearch && matchStatus;
                });

                currentPage = 1;
                render(currentPage);
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
                    pagination.style.display = "none";
                    showingText.style.display = "block";
                    showingText.textContent = "No results found";
                    return;
                }

                pagination.style.display = totalRows <= rowsPerPage ? "none" : "block";

                showingText.style.display = "block";
                showingText.textContent =
                    totalRows <= rowsPerPage ?
                    `Showing ${totalRows} data` :
                    `Showing ${(page - 1) * rowsPerPage + 1}–${Math.min(page * rowsPerPage, totalRows)} of ${totalRows}`;

                filteredRows
                    .slice((page - 1) * rowsPerPage, page * rowsPerPage)
                    .forEach(r => r.style.display = "table-row");

                pageNumbers.innerHTML = "";
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.className = "page-num" + (i === page ? " active" : "");
                    btn.onclick = () => {
                        currentPage = i;
                        render(i);
                    };
                    pageNumbers.appendChild(btn);
                }

                prevBtn.disabled = page === 1;
                nextBtn.disabled = page === totalPages;
            }

            prevBtn.onclick = () => currentPage > 1 && render(--currentPage);
            nextBtn.onclick = () => currentPage < Math.ceil(filteredRows.length / rowsPerPage) && render(++
                currentPage);

            render(currentPage);
        });
    </script>
    <script>
        /* ======================
        ASSIGN MODAL FUNCTION
        ====================== */
        const assignModal = document.getElementById("assignModal");
        const closeAssign = document.getElementById("closeAssign");

        // USE EVENT DELEGATION (BEST)
        document.addEventListener("click", function(e) {
            const assignBtn = e.target.closest(".action-btn.assign");
            if (!assignBtn) return;

            e.preventDefault();
            assignModal.classList.add("show");
        });

        // close (X)
        closeAssign.addEventListener("click", () => {
            assignModal.classList.remove("show");
        });

        // close on backdrop click
        assignModal.addEventListener("click", e => {
            if (e.target === assignModal) {
                assignModal.classList.remove("show");
            }
        });
    </script>

</body>

</html>
