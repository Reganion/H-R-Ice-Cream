@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Ingredients Management')
    <link rel="stylesheet" href="{{ asset('css/admin/ingredients.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/app.css') }}">


    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

</head>

<body>
    @section('content')
        @include('admin.partials.alert')

        <div class="content-area">

            <!-- PAGE HEADER -->
            <div class="ingredients-header">
                <h2>Ingredients list</h2>

                <div class="header-actions">
                    <div class="search-box">
                        <span class="material-symbols-outlined">search</span>

                        <input type="text" id="searchInput" placeholder="Search by flavor">

                        <button type="button" class="clear-search" id="clearSearch">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="filter-wrapper">
                        <button class="btn-filter" id="filterBtn">
                            <span class="material-symbols-outlined">filter_list</span>
                            Filter
                        </button>

                        <div class="filter-dropdown" id="filterDropdown">
                            <div class="filter-section">
                                <label>
                                    <input type="checkbox" data-filter="available">
                                    Available
                                </label>
                                <label>
                                    <input type="checkbox" data-filter="out">
                                    Out of Stock
                                </label>
                            </div>

                            <hr>

                            <div class="filter-section">
                                <label>
                                    <input type="checkbox" data-filter="ingredients">
                                    Ingredients
                                </label>
                                <label>
                                    <input type="checkbox" data-filter="flavor">
                                    Flavor
                                </label>
                            </div>


                        </div>
                    </div>


                    <button class="btn-add">
                        <span class="material-symbols-outlined">add</span>
                        Add Product
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-card">

                <!-- HEADER -->
                <div class="table-header ingredients">
                    <div class="col check">
                        <input type="checkbox">
                        <span class="material-symbols-outlined">delete</span>
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">grocery</span> Product
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">sort</span> Type
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">format_list_numbered</span> Quantity
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">mitre</span> Unit
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">android_cell_5_bar</span> Status
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">arrow_selector_tool</span> Action
                    </div>
                </div>


                <div class="table-body-container">

                    <div class="table-body-scroll">
                        <table class="flavor-table">
                            <colgroup>
                                <col style="width:55px">
                                <col style="width:18%">
                                <col style="width:14%">
                                <col style="width:14%">
                                <col style="width:15%">
                                <col style="width:18%">
                                <col style="width:20%">
                            </colgroup>



                            <tbody>
                                @foreach ($ingredients as $item)
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td class="flavor-col">
                                            <img src="{{ $item->image ? asset($item->image) : asset('img/default.png') }}">
                                            <strong>{{ $item->name }}</strong>
                                        </td>


                                        <td>{{ $item->type }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>
                                            @php
                                                $isOut = $item->quantity == 0;
                                            @endphp

                                            <span class="status {{ $isOut ? 'out' : 'available' }}">
                                                <span class="dot"></span>
                                                {{ $isOut ? 'Out of Stock' : 'Available' }}
                                            </span>
                                        </td>


                                        <td class="action">
                                            <button class="btn-edit" data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}" data-type="{{ $item->type }}"
                                                data-quantity="{{ $item->quantity }}" data-unit="{{ $item->unit }}"
                                                data-image="{{ $item->image }}">
                                                Edit
                                            </button>


                                            <form action="/admin/ingredients/{{ $item->id }}" method="POST"
                                                class="delete-form" style="display:inline">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="btn-delete" data-name="{{ $item->name }}">
                                                    Delete
                                                </button>
                                            </form>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>


                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        <button class="nav-btn" id="prevBtn">
                            <span class="material-symbols-outlined">arrow_left_alt</span>
                            Previous
                        </button>

                        <div id="pageNumbers"></div>
                        <div id="paginationInfo" class="page-num"></div>


                        <button class="nav-btn" id="nextBtn">
                            Next
                            <span class="material-symbols-outlined">arrow_right_alt</span>
                        </button>
                    </div>


                </div>

            </div>

        </div>

    @endsection
    <div class="modal-overlay" id="editFlavorModal">
        <div class="modal-card edit-modal-card">

            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h3>Edit Product</h3>
                    <button type="button" class="modal-close" id="closeEditModal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="modal-body">

                    <!-- Upload -->
                    <div class="upload-box">
                        <div class="upload-preview" id="editImagePreview">
                            <span class="material-symbols-outlined">add_photo_alternate</span>
                        </div>

                        <input type="file" name="image" id="editFlavorImage" hidden>

                        <div class="upload-actions">
                            <label class="upload-label">Upload Image</label>
                            <button type="button" class="btn-upload"
                                onclick="document.getElementById('editFlavorImage').click()">
                                Upload
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" id="editFlavorName" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Product Type</label>

                            <div class="custom-select" id="editCategorySelect">
                                <div class="select-trigger">
                                    <span class="selected">Product Type</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>

                                <div class="select-options">
                                    <div class="option">Ingredients</div>
                                    <div class="option">Flavor</div>
                                </div>
                            </div>

                            <input type="hidden" name="type" id="editCategoryValue" required>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" id="editPrice" min="0" required>
                        </div>
                    </div>

                    <div class="form-group" style="width:49%;">
                        <label>Unit</label>

                        <div class="custom-select" id="editUnitSelect">
                            <div class="select-trigger">
                                <span class="selected">Select Unit</span>
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>

                            <div class="select-options">
                                <div class="option">Gram</div>
                                <div class="option">Kilogram</div>
                                <div class="option">Milliliter</div>
                                <div class="option">Liter</div>
                            </div>
                        </div>

                        <input type="hidden" name="unit" id="editUnitValue" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelEditModal">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>

            </form>

        </div>
    </div>

    <!-- ========================
ADD FLAVOR MODAL
======================== -->
    <div class="modal-overlay" id="addFlavorModal">
        <div class="modal-card">

            <form action="{{ route('admin.ingredients.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h3>Add New Product</h3>
                    <button type="button" class="modal-close" id="closeAddModal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="upload-box">
                        <div class="upload-preview" id="imagePreview">
                            <span class="material-symbols-outlined">add_photo_alternate</span>
                        </div>

                        <!-- ONLY ADD name -->
                        <input type="file" name="image" id="flavorImage" hidden>

                        <div class="upload-actions">
                            <label class="upload-label">Upload Photo</label>
                            <button type="button" class="btn-upload"
                                onclick="document.getElementById('flavorImage').click()">
                                Upload
                            </button>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" placeholder="Enter Product Name" required>
                    </div>

                    <!-- Category + Quantity -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product Type</label>

                            <div class="custom-select" id="categorySelect">
                                <div class="select-trigger">
                                    <span class="selected">Product Type</span>
                                    <span class="material-symbols-outlined">expand_more</span>
                                </div>

                                <div class="select-options">
                                    <div class="option">Ingredients</div>
                                    <div class="option">Flavor</div>
                                </div>
                            </div>

                            <!-- CHANGE name ONLY -->
                            <input type="hidden" name="type" id="categoryValue" required>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" placeholder="Enter Quantity" min="0"
                                required>
                        </div>
                    </div>

                    <!-- UNIT -->
                    <div class="form-group" style="width: 49%;">
                        <label>Unit</label>

                        <div class="custom-select" id="unitSelect">
                            <div class="select-trigger">
                                <span class="selected">Select Unit</span>
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>

                            <div class="select-options">
                                <div class="option">Gram</div>
                                <div class="option">Kilogram</div>
                                <div class="option">Milliliter</div>
                                <div class="option">Liter</div>
                            </div>
                        </div>

                        <!-- ADD name ONLY -->
                        <input type="hidden" name="unit" id="unitValue" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelAddModal">Cancel</button>
                    <button type="submit" class="btn-save">Add New Product</button>
                </div>

            </form>

        </div>
    </div>


    <!-- ========================
DELETE CONFIRM MODAL
======================== -->
    <div class="delete-overlay" id="deleteConfirmModal">
        <div class="delete-modal">

            <h3 class="delete-title">
                Are you sure you want to remove
                <span id="deleteFlavorName">this flavor</span>?
            </h3>

            <p class="delete-text">
                If you remove this product you will not have a chance to undo it.
            </p>

            <div class="delete-actions">
                <button class="btn-delete-cancel" id="cancelDelete">
                    No, Cancel
                </button>
                <button class="btn-delete-confirm" id="confirmDelete">
                    Yes, I'm sure
                </button>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            function resetAddFlavorForm() {
                // text & number inputs
                document.querySelectorAll(
                    "#addFlavorModal input[type='text'], #addFlavorModal input[type='number']").forEach(i => i
                    .value = "");

                // image preview
                document.getElementById("imagePreview").innerHTML =
                    `<span class="material-symbols-outlined">add_photo_alternate</span>`;
                document.getElementById("flavorImage").value = "";

                // custom select
                document.querySelector("#categorySelect .selected").textContent = "Select Category";
                document.querySelector("#categorySelect .selected").style.color = "#6b7280";
                document.getElementById("categoryValue").value = "";
            }

            function closeModalSmooth(modal) {
                const card = modal.querySelector(".modal-card");

                // trigger slide-out
                card.style.transform = "translateX(120%)";
                card.style.opacity = "0";

                // wait for animation before hiding overlay
                setTimeout(() => {
                    modal.classList.remove("show");

                    // reset so next open animates correctly
                    card.style.transform = "";
                    card.style.opacity = "";
                }, 450); // MUST match CSS transition time
            }

            /* =====================
            FILTER & SEARCH
            ===================== */
            const filterBtn = document.getElementById("filterBtn");
            const filterDropdown = document.getElementById("filterDropdown");
            const filterCheckboxes = filterDropdown.querySelectorAll("input[type='checkbox']");
            const searchInput = document.getElementById("searchInput");
            const allRows = Array.from(document.querySelectorAll(".flavor-table tbody tr"));

            const pageNumbers = document.getElementById("pageNumbers");
            const pageInfo = document.getElementById("paginationInfo");
            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");

            const rowsPerPage = 10;
            let currentPage = 1;
            let filteredRows = [...allRows];

            filterBtn.onclick = e => {
                e.stopPropagation();
                filterDropdown.classList.toggle("show");
            };

            document.addEventListener("click", () => {
                filterDropdown.classList.remove("show");
            });

            filterDropdown.onclick = e => e.stopPropagation();

            filterCheckboxes.forEach(cb => {
                cb.addEventListener("change", () => {

                    if (["available", "out"].includes(cb.dataset.filter)) {
                        filterCheckboxes.forEach(o => {
                            if (o !== cb && ["available", "out"].includes(o.dataset
                                    .filter)) {
                                o.checked = false;
                            }
                        });
                    }

                    // allow only ONE type filter at a time
                    if (["ingredients", "flavor"].includes(cb.dataset.filter)) {
                        filterCheckboxes.forEach(o => {
                            if (o !== cb && ["ingredients", "flavor"].includes(o.dataset
                                    .filter)) {
                                o.checked = false;
                            }
                        });
                    }


                    applyFilters();
                });
            });

            function applyFilters() {
                const keyword = searchInput.value.toLowerCase();

                const selectedStatus = [...filterCheckboxes].find(cb =>
                    cb.checked && ["available", "out"].includes(cb.dataset.filter)
                );

                const selectedType = [...filterCheckboxes].find(cb =>
                    cb.checked && ["ingredients", "flavor"].includes(cb.dataset.filter)
                );


                filteredRows = allRows.filter(row => {
                    const text = row.innerText.toLowerCase();

                    if (keyword && !text.includes(keyword)) return false;
                    if (selectedStatus && !text.includes(selectedStatus.dataset.filter === "out" ?
                            "out of stock" : "available")) return false;
                    if (selectedType && !text.includes(selectedType.dataset.filter))
                        return false;


                    return true;
                });

                currentPage = 1;
                showPage(currentPage);
            }

            function showPage(page) {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                allRows.forEach(r => r.style.display = "none");
                filteredRows.slice(start, end).forEach(r => r.style.display = "");

                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

                // ALWAYS RESET VISIBILITY FIRST ✅
                prevBtn.style.display = "inline-flex";
                nextBtn.style.display = "inline-flex";
                pageNumbers.style.display = "flex";
                pageInfo.style.display = "none";

                if (totalPages <= 1) {
                    prevBtn.style.display = "none";
                    nextBtn.style.display = "none";
                    pageNumbers.style.display = "none";

                    pageInfo.style.display = "block";
                    pageInfo.textContent = filteredRows.length ?
                        `Showing ${filteredRows.length} data` :
                        "No results found";
                    return;
                }

                prevBtn.disabled = page === 1;
                nextBtn.disabled = page === totalPages;

                renderPagination(totalPages);
            }


            function renderPagination(total) {
                pageNumbers.innerHTML = "";
                for (let i = 1; i <= total; i++) {
                    const btn = document.createElement("button");
                    btn.className = "page-num" + (i === currentPage ? " active" : "");
                    btn.textContent = i;
                    btn.onclick = () => {
                        currentPage = i;
                        showPage(i);
                    };
                    pageNumbers.appendChild(btn);
                }
            }

            prevBtn.onclick = () => currentPage > 1 && showPage(--currentPage);
            nextBtn.onclick = () => currentPage < Math.ceil(filteredRows.length / rowsPerPage) && showPage(++
                currentPage);
            searchInput.addEventListener("input", () => {
                currentPage = 1;
                applyFilters();
            });


            showPage(currentPage);

            /* =====================
            MODAL
            ===================== */
            const modal = document.getElementById("addFlavorModal");

            document.querySelector(".btn-add").onclick = () => {
                modal.classList.add("show");
            };

            document.getElementById("closeAddModal").onclick = () => {
                closeModalSmooth(modal);
            };

            document.getElementById("cancelAddModal").onclick = () => {
                resetAddFlavorForm();
                closeModalSmooth(modal);
            };

            modal.onclick = e => {
                if (e.target === modal) closeModalSmooth(modal);
            };


            /* =====================
            IMAGE PREVIEW
            ===================== */
            document.getElementById("flavorImage").addEventListener("change", function() {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = () => document.getElementById("imagePreview").innerHTML =
                    `<img src="${reader.result}">`;
                reader.readAsDataURL(file);
            });
            /* =====================
            UNIT SELECT (ADD MODAL)
            ===================== */
            const unitSelect = document.getElementById("unitSelect");
            if (unitSelect) {
                const trigger = unitSelect.querySelector(".select-trigger");
                const selected = unitSelect.querySelector(".selected");
                const options = unitSelect.querySelectorAll(".option");
                const hidden = document.getElementById("unitValue");

                trigger.onclick = () => unitSelect.classList.toggle("open");

                options.forEach(opt => {
                    opt.onclick = () => {
                        selected.textContent = opt.textContent;
                        selected.style.color = "#111827";
                        hidden.value = opt.textContent;
                        unitSelect.classList.remove("open");
                    };
                });

                document.addEventListener("click", e => {
                    if (!unitSelect.contains(e.target)) unitSelect.classList.remove("open");
                });
            }

            /* =====================
            UNIT SELECT (EDIT MODAL)
            ===================== */
            const editUnitSelect = document.getElementById("editUnitSelect");
            if (editUnitSelect) {
                const trigger = editUnitSelect.querySelector(".select-trigger");
                const selected = editUnitSelect.querySelector(".selected");
                const options = editUnitSelect.querySelectorAll(".option");
                const hidden = document.getElementById("editUnitValue");

                trigger.onclick = () => editUnitSelect.classList.toggle("open");

                options.forEach(opt => {
                    opt.onclick = () => {
                        selected.textContent = opt.textContent;
                        selected.style.color = "#111827";
                        hidden.value = opt.textContent;
                        editUnitSelect.classList.remove("open");
                    };
                });

                document.addEventListener("click", e => {
                    if (!editUnitSelect.contains(e.target)) editUnitSelect.classList.remove("open");
                });
            }


            /* =====================
            CUSTOM SELECT
            ===================== */
            const select = document.getElementById("categorySelect");
            const trigger = select.querySelector(".select-trigger");
            const selected = select.querySelector(".selected");
            const options = select.querySelectorAll(".option");
            const hiddenInput = document.getElementById("categoryValue");

            trigger.onclick = () => select.classList.toggle("open");

            options.forEach(opt => {
                opt.onclick = () => {
                    selected.textContent = opt.textContent;
                    selected.style.color = "#111827";
                    hiddenInput.value = opt.textContent;
                    select.classList.remove("open");
                };
            });

            document.addEventListener("click", e => {
                if (!select.contains(e.target)) select.classList.remove("open");
            });
            /* =====================
            EDIT MODAL
            ===================== */
            const editModal = document.getElementById("editFlavorModal");
            const closeEdit = document.getElementById("closeEditModal");
            const cancelEdit = document.getElementById("cancelEditModal");

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.dataset.id;

                    editModal.classList.add("show");

                    // set form action
                    document.getElementById("editForm").action =
                        `/admin/ingredients/${id}`;

                    // fill inputs
                    document.getElementById("editFlavorName").value = btn.dataset.name;
                    document.getElementById("editPrice").value = btn.dataset.quantity;
                    document.getElementById("editCategoryValue").value = btn.dataset.type;
                    document.getElementById("editUnitValue").value = btn.dataset.unit;

                    // update custom selects text
                    document.querySelector("#editCategorySelect .selected").textContent = btn
                        .dataset.type;
                    document.querySelector("#editUnitSelect .selected").textContent = btn.dataset
                        .unit;

                    // image preview
                    if (btn.dataset.image) {
                        document.getElementById("editImagePreview").innerHTML =
                            `<img src="/${btn.dataset.image}">`;
                    }
                });
            });


            closeEdit.onclick = cancelEdit.onclick = () => {
                closeModalSmooth(editModal);
            };

            editModal.onclick = e => {
                if (e.target === editModal) closeModalSmooth(editModal);
            };


            /* =====================
            EDIT IMAGE PREVIEW
            ===================== */
            document.getElementById("editFlavorImage").addEventListener("change", function() {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = () => {
                    document.getElementById("editImagePreview").innerHTML =
                        `<img src="${reader.result}">`;
                };
                reader.readAsDataURL(file);
            });

            /* =====================
            EDIT CUSTOM SELECT
            ===================== */
            const editSelect = document.getElementById("editCategorySelect");
            const editTrigger = editSelect.querySelector(".select-trigger");
            const editSelected = editSelect.querySelector(".selected");
            const editOptions = editSelect.querySelectorAll(".option");
            const editHidden = document.getElementById("editCategoryValue");

            editTrigger.onclick = () => editSelect.classList.toggle("open");

            editOptions.forEach(opt => {
                opt.onclick = () => {
                    editSelected.textContent = opt.textContent;
                    editSelected.style.color = "#111827";
                    editHidden.value = opt.textContent;
                    editSelect.classList.remove("open");
                };
            });

            document.addEventListener("click", e => {
                if (!editSelect.contains(e.target)) editSelect.classList.remove("open");
            });

            /* =====================
            DELETE CONFIRM MODAL
            ===================== */
            const deleteModal = document.getElementById("deleteConfirmModal");
            const deleteName = document.getElementById("deleteFlavorName");
            const cancelDelete = document.getElementById("cancelDelete");
            const confirmDelete = document.getElementById("confirmDelete");

            let rowToDelete = null;

            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => {
                    const row = btn.closest("tr");
                    const name = row.querySelector(".flavor-col strong").textContent;

                    rowToDelete = row;
                    deleteName.textContent = name;

                    deleteModal.classList.add("show");
                });
            });

            cancelDelete.onclick = () => {
                deleteModal.classList.remove("show");
                rowToDelete = null;
            };

            deleteModal.onclick = e => {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove("show");
                    rowToDelete = null;
                }
            };

            let deleteForm = null;

            /* Open delete modal */
            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => {
                    deleteForm = btn.closest("form");

                    deleteName.textContent = btn.dataset.name;
                    deleteModal.classList.add("show");
                });
            });

            /* Cancel delete */
            cancelDelete.onclick = () => {
                deleteModal.classList.remove("show");
                deleteForm = null;
            };

            /* Confirm delete */
            confirmDelete.onclick = () => {
                if (deleteForm) {
                    deleteForm.submit(); // ✅ Laravel DELETE happens here
                }
            };

            /* Click outside modal */
            deleteModal.onclick = e => {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove("show");
                    deleteForm = null;
                }
            };
            const clearSearchBtn = document.getElementById("clearSearch");

            searchInput.addEventListener("input", () => {
                clearSearchBtn.style.display = searchInput.value ? "block" : "none";
            });

            clearSearchBtn.addEventListener("click", () => {
                searchInput.value = "";
                clearSearchBtn.style.display = "none";
                applyFilters();
            });

        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const alert = document.getElementById("globalAlert");
            if (!alert) return;

            setTimeout(() => {
                alert.style.opacity = "0";
                alert.style.transform = "translateY(-10px)";
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        });
    </script>


</body>

</html>
