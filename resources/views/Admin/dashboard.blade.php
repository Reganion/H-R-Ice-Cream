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


    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            /* ⬅ prevents page scroll */
        }

        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow: hidden;
            background: rgb(242, 242, 242);
            border-top-left-radius: 30px;
            margin: 0;
            box-shadow: none;
            position: relative;
            min-height: 0;
        }

        /* SUMMARY CARDS */
        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-box {
            background: white;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .summary-box h4 {
            color: #7b7b7b;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .summary-box h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .summary-box h2 span.icon {
            width: 10px;
            height: 3px;
            border-radius: 2px;
            display: inline-block;
        }

        .summary-box p {
            color: #b0b0b0;
            font-size: 14px;
        }

        /* TAB BUTTON STYLE */
        .order-tabs {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px 110px;
            margin-bottom: 30px;
            border-bottom: none;
            padding-bottom: 0;
        }

        /* Smooth hover animation */
        .tab-btn {
            background: none;
            border: none;
            font-size: 18px;
            padding-bottom: 8px;
            cursor: pointer;
            color: black;
            position: relative;
            transition: color 0.3s ease;
        }

        .tab-btn::after {
            content: "";
            position: absolute;
            left: 20%;
            bottom: 0;
            width: 0;
            height: 2px;
            background: #d23f3f;
            transition: all 0.3s ease;
            transform: translateX(-20%);
        }

        .tab-btn:hover {
            color: #d23f3f;
        }

        .tab-btn:hover::after {
            width: 100%;
        }

  
/* ACTIVE TAB – include the word */
.tab-btn.active {
    color: #d23f3f;
    font-weight: 600;
}


/* Keep underline */
.tab-btn.active::after {
    width: 40%;
    
}



        .date-filter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .date-input-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            /* space between start → to → end */
        }

        .date-input-wrapper input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            cursor: pointer;
        }

        /* ⭐ The display box */
        .date-filter-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #ddd;
            min-width: 270px;
        }

        /* Icon styling */
        .date-filter-box .material-symbols-outlined {
            font-size: 20px;
            color: #5a5a5a;
        }

        /* Displayed formatted date */
        .formatted-date {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }



        .orders-table {
            width: 100%;
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
            padding: 0;
        }

        /* Scrollable area */
        .table-scroll {
            flex: 1;
            min-height: 0;
            overflow-x: auto;
            overflow-y: auto;
            padding-right: 6px;
        }


        .table-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: rgba(136, 136, 136, 0);
            border-radius: 4px;
            transition: background 0.3s;
        }


        .table-scroll:hover::-webkit-scrollbar-thumb {
            background: #888;
        }


        .table-scroll:hover::-webkit-scrollbar-thumb:hover {
            background: #555;
        }


        .table-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(136, 136, 136, 0) #f1f1f1;
        }

        .table-scroll:hover {
            scrollbar-color: #888 #f1f1f1;
        }

        /* table basics */
        .orders-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            min-width: 700px;
        }



        .orders-table thead th {
            padding: 15px 10px;
            font-size: 14px;
            color: #6f6f6f;
            font-weight: 600;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 2;
            background: #ffffff;
        }

        .orders-table thead .material-symbols-outlined {
            font-size: 18px;
            color: #000000b3;
        }

        .th-content {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            line-height: 1;
        }

        .orders-table thead th {
            vertical-align: middle;
        }

        .orders-table thead .material-symbols-outlined {
            font-size: 18px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }


        @media (max-width: 480px) {
            .orders-table thead .material-symbols-outlined {
                display: none;
            }

            .th-content {
                gap: 6px;
            }
        }


        /* row card look */
        .orders-table tbody tr {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .orders-table tbody tr td {
            padding: 15px 10px;
            font-size: 14px;
        }

        .orders-table table tr>*:first-child {
            border-radius: 14px 0 0 14px;
        }

        .orders-table table tr>*:last-child {
            border-radius: 0 14px 14px 0;
        }

        /* 2-LINE TEXT STYLE */
        .td-title {
            font-size: 15px;
            font-weight: 600;
            color: #252525;
        }

        .td-sub {
            font-size: 13px;
            color: #9c9c9c;
            margin-top: 3px;
        }

        /* STATUS DOT COLORS */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .status-delivered {
            background: #34c759;
        }

        .status-pending {
            background: #ffcc00;
        }

        .status-assigned {
            background: #007aff;
        }

        .status-new {
            background: #ff3b30;
        }

        /* PAGINATION – GOOGLE ICON STYLE */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 14px 10px;
        }

        /* Prev / Next buttons */
        .page-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #555;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
        }

        .page-nav .material-symbols-outlined {
            font-size: 20px;
        }

        /* Hover */
        .page-nav:hover:not(:disabled) {
            background: #ffffffb6;
        }

        /* Disabled */
        .page-nav:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* Page numbers */
        .page-numbers {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-numbers button {
            background: none;
            border: none;
            font-size: 14px;
            color: #777;
            cursor: pointer;
            padding: 2px 4px;
            transition: color 0.2s ease;
        }

        .page-numbers button:hover {
            color: #000;
        }

        .page-numbers button.active {
            color: #000;
            font-weight: 600;
        }


        /* RESPONSIVENESS */
        @media (max-width: 1024px) {
            .summary-boxes {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .date-filter {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .date-input-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 5px;
            }

            .date-input-wrapper input[type="date"] {
                width: 100%;
                box-sizing: border-box;
            }

            .date-input-wrapper span {
                text-align: center;
                display: block;
            }

            .date-filter-box {
                justify-content: flex-start;
                width: 100%;
                padding: 8px 10px;
            }

            .formatted-date {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .summary-boxes {
                grid-template-columns: 1fr;
            }

            .order-tabs {
                gap: 10px 20px;
            }

            .tab-btn {
                font-size: 16px;
            }

            .date-filter {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {

            /* Allow the whole content to scroll vertically */
            .content-area {
                display: block;
                overflow-y: auto;
                padding: 10px 5px;
                min-height: auto;
                margin-bottom: 20px;
            }

            /* Make table container block-level so it displays correctly */
            .orders-table {
                display: block;
                width: 100%;
                flex: none;
            }

            /* Enable horizontal scroll for wide tables */
            .table-scroll {
                display: block;
                overflow-x: auto;
                overflow-y: visible;
                min-height: auto;
            }


            .orders-table table {
                width: max-content;
                min-width: 600px;
            }
        }
    </style>
</head>

<body>
    @section('content')
        <div class="content-area">

            <!-- SUMMARY BOXES -->
            <div class="summary-boxes">
                <div class="summary-box">
                    <h4>Total Orders</h4>
                    <h2>500 <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: 400</p>
                </div>

                <div class="summary-box">
                    <h4>Assigned Orders</h4>
                    <h2>120 <span class="icon" style="background:#3b82f6"></span></h2>
                    <p>Last month: 100</p>
                </div>

                <div class="summary-box">
                    <h4>Pending Orders</h4>
                    <h2>35 <span class="icon" style="background:#f59e0b"></span></h2>
                    <p>Last month: 40</p>
                </div>

                <div class="summary-box">
                    <h4>Delivered Orders</h4>
                    <h2>345 <span class="icon" style="background:#22c55e"></span></h2>
                    <p>Last month: 260</p>
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
                            <tr>
                                <td>
                                    <div class="td-title">#438942</div>
                                    <div class="td-sub">12 Dec 2025</div>
                                </td>
                                <td>
                                    <div class="td-title">Jhon Ryan Cuadra</div>
                                    <div class="td-sub">09123456789</div>
                                </td>
                                <td>
                                    <div class="td-title">13 Dec 2025, 2:30 PM</div>
                                    <div class="td-sub">Calamba, Laguna</div>
                                </td>
                                <td>
                                    <div class="td-title">Strawberry</div>
                                    <div class="td-sub">Special (3.5GL)</div>
                                </td>
                                <td>
                                    <div class="td-title">₱300</div>
                                    <div class="td-sub">Gcash</div>
                                </td>
                                <td>
                                    <span class="status-dot status-delivered"></span>
                                    <span class="td-title">Delivered</span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="td-title">#438943</div>
                                    <div class="td-sub">12 Dec 2025</div>
                                </td>
                                <td>
                                    <div class="td-title">Irene Cuadra</div>
                                    <div class="td-sub">09987654321</div>
                                </td>
                                <td>
                                    <div class="td-title">14 Dec 2025, 11:00 AM</div>
                                    <div class="td-sub">San Pablo City</div>
                                </td>
                                <td>
                                    <div class="td-title">Chocolate</div>
                                    <div class="td-sub">Premium (1GL)</div>
                                </td>
                                <td>
                                    <div class="td-title">₱450</div>
                                    <div class="td-sub">Cash</div>
                                </td>
                                <td>
                                    <span class="status-dot status-pending"></span>
                                    <span class="td-title">Pending</span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="td-title">#438944</div>
                                    <div class="td-sub">12 Dec 2025</div>
                                </td>
                                <td>
                                    <div class="td-title">Mark Reyes</div>
                                    <div class="td-sub">09124567890</div>
                                </td>
                                <td>
                                    <div class="td-title">15 Dec 2025, 4:45 PM</div>
                                    <div class="td-sub">Batangas City</div>
                                </td>
                                <td>
                                    <div class="td-title">Mango</div>
                                    <div class="td-sub">Classic (500ml)</div>
                                </td>
                                <td>
                                    <div class="td-title">₱120</div>
                                    <div class="td-sub">Gcash</div>
                                </td>
                                <td>
                                    <span class="status-dot status-assigned"></span>
                                    <span class="td-title">Assigned</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="td-title">#438944</div>
                                    <div class="td-sub">12 Dec 2025</div>
                                </td>
                                <td>
                                    <div class="td-title">Kyle Reganion</div>
                                    <div class="td-sub">09124567890</div>
                                </td>
                                <td>
                                    <div class="td-title">15 Dec 2025, 4:45 PM</div>
                                    <div class="td-sub">Batangas City</div>
                                </td>
                                <td>
                                    <div class="td-title">Mango</div>
                                    <div class="td-sub">Classic (500ml)</div>
                                </td>
                                <td>
                                    <div class="td-title">₱120</div>
                                    <div class="td-sub">Gcash</div>
                                </td>
                                <td>
                                    <span class="status-dot status-new"></span>
                                    <span class="td-title">New</span>
                                </td>
                            </tr>

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
            const rows = Array.from(tbody.querySelectorAll("tr"));
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

            renderPageButtons();
            displayRows(currentPage);
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rows = document.querySelectorAll(".orders-table tbody tr");
            const searchInput = document.querySelector(".search-bar input");
            const tabButtons = document.querySelectorAll(".tab-btn");
            const startDateInput = document.getElementById("startDate");
            const endDateInput = document.getElementById("endDate");

            /* --- INSERT THIS BLOCK HERE (safe zone) --- */

            /* --- FORMAT DATE INSIDE CALENDAR (21 Nov, 2025 format) --- */
            function formatDateReadable(dateString) {
                if (!dateString) return "";
                const d = new Date(dateString);

                const day = d.getDate();
                const month = d.toLocaleString("en-US", {
                    month: "short"
                });
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
                    display.textContent = `${formatDateReadable(start)}`;
                } else if (end) {
                    display.textContent = `${formatDateReadable(end)}`;
                } else {
                    display.textContent = "";
                }
            }

            document.getElementById("startDate").addEventListener("change", updateDateDisplay);
            document.getElementById("endDate").addEventListener("change", updateDateDisplay);

            /* --- END INSERT BLOCK --- */

            function parseDate(text) {
                return new Date(text.replace(/(\d+)(st|nd|rd|th)/, "$1"));
            }

            function applyFilters() {
                const searchText = searchInput.value.toLowerCase().trim();
                const activeTab = document.querySelector(".tab-btn.active").textContent.trim();
                const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
                const endDate = endDateInput.value ? new Date(endDateInput.value) : null;

                rows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    const statusText = row.querySelector("td:last-child .td-title").textContent.trim();
                    const dateText = row.querySelector("td .td-sub").textContent.trim();
                    const transDate = parseDate(dateText);

                    let matchesSearch = rowText.includes(searchText);
                    let matchesTab = (activeTab === "All Orders") || statusText === activeTab.replace(
                        " Orders", "");
                    let matchesDate = true;

                    if (startDate && transDate < startDate) matchesDate = false;
                    if (endDate && transDate > endDate) matchesDate = false;

                    row.style.display = (matchesSearch && matchesTab && matchesDate) ? "" : "none";
                });
            }

            searchInput.addEventListener("keyup", applyFilters);
            startDateInput.addEventListener("change", applyFilters);
            endDateInput.addEventListener("change", applyFilters);

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
