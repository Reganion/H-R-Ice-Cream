@extends('admin.layout.layout')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    @section('title', 'Gallon Management')
    <link rel="stylesheet" href="{{ asset('css/Admin/gallon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/app.css') }}">
</head>

<body>
    @section('content')
        @include('admin.partials.alert')
        <div class="content-area">

            <!-- PAGE HEADER -->
            <div class="gallon-header">
                <h2>Gallon list</h2>

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

                            {{-- <div class="filter-section">
                                <label>
                                    <input type="checkbox" data-filter="ingredients">
                                    Ingredients
                                </label>
                                <label>
                                    <input type="checkbox" data-filter="flavor">
                                    Flavor
                                </label>
                            </div> --}}
                        </div>
                    </div>


                    <button class="btn-add">
                        <span class="material-symbols-outlined">add</span>
                        Add Gallon
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-card">

                <!-- HEADER -->
                <div class="table-header gallon">
                    <div class="col check">
                        <input type="checkbox">
                        <span class="material-symbols-outlined">delete</span>
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">specific_gravity</span> Gallon Size
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">sort</span> Quantity
                    </div>
                    <div class="col">
                        <span class="material-symbols-outlined">sell</span> Add-on Price
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
                                <col style="width:18%">
                                <col style="width:20%">
                                <col style="width:20%">
                                <col style="width:20%">
                            </colgroup>

                            <tbody>
                                @foreach ($gallons as $gallon)
                                    <tr data-size="{{ strtolower($gallon->size) }}">

                                        <td><input type="checkbox"></td>

                                        <td class="flavor-col">
                                            <img src="{{ $gallon->image ? asset($gallon->image) : asset('img/gallon.png') }}"
                                                alt="Gallon {{ $gallon->size }}" loading="lazy">
                                            <strong>{{ $gallon->size }}</strong>
                                        </td>


                                        <td>{{ $gallon->quantity }}</td>

                                        <td>₱{{ number_format($gallon->addon_price, 2) }}</td>

                                        <td>
                                            <span class="status {{ $gallon->status }}">
                                                <span class="dot"></span>
                                                {{ $gallon->status === 'available' ? 'Available' : 'Out of Stock' }}
                                            </span>
                                        </td>

                                        <td class="action">
                                            <button class="btn-edit" data-id="{{ $gallon->id }}"
                                                data-size="{{ $gallon->size }}" data-qty="{{ $gallon->quantity }}"
                                                data-price="{{ $gallon->addon_price }}"
                                                data-image="{{ $gallon->image ? asset($gallon->image) : '' }}">
                                                <span class="material-symbols-outlined">edit</span> Edit
                                            </button>


                                            <button class="btn-delete" data-id="{{ $gallon->id }}">
                                                <span class="material-symbols-outlined">delete</span> Delete
                                            </button>

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

    <!-- ========================
        EDIT GALLON MODAL
    ======================== -->
    <div class="modal-overlay" id="editFlavorModal">
        <div class="modal-card edit-modal-card">

            <form id="editGallonForm" method="POST" enctype="multipart/form-data">
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

                        <input type="file" id="editFlavorImage" name="image" hidden>

                        <div class="upload-actions">
                            <label class="upload-label">Upload Image</label>
                            <button type="button" class="btn-upload"
                                onclick="document.getElementById('editFlavorImage').click()">
                                Upload
                            </button>
                        </div>
                    </div>

                    <!-- Gallon Size + Quantity -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gallon Size</label>
                            <input type="text" id="editGallonSize" name="size" required>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" id="editQuantity" name="quantity" required>
                        </div>
                    </div>

                    <!-- Add-on Price -->
                    <div class="form-group">
                        <label>Add-on Price</label>
                        <input type="number" id="editAddonPrice" name="addon_price" required>
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
    ADD GALLON MODAL
======================== -->
    <div class="modal-overlay" id="addFlavorModal">
        <div class="modal-card">

            <form id="addGallonForm" action="{{ route('admin.gallons.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h3>Add New Gallon</h3>
                    <button type="button" class="modal-close" id="closeAddModal">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="modal-body">

                    <!-- Upload -->
                    <div class="upload-box">
                        <div class="upload-preview" id="imagePreview">
                            <span class="material-symbols-outlined">add_photo_alternate</span>
                        </div>

                        <input type="file" id="flavorImage" name="image" hidden>

                        <div class="upload-actions">
                            <label class="upload-label">Upload Photo</label>
                            <button type="button" class="btn-upload"
                                onclick="document.getElementById('flavorImage').click()">
                                Upload
                            </button>
                        </div>
                    </div>

                    <!-- Gallon Size + Quantity -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gallon Size</label>
                            <input type="text" name="size" placeholder="Enter Size" autocomplete="off"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" placeholder="Enter Quantity" min="0"
                                required>
                        </div>
                    </div>

                    <!-- Add-on Price -->
                    <div class="form-group">
                        <label>Add-on Price</label>
                        <input type="number" name="addon_price" placeholder="Enter Price" min="0"
                            step="0.01" required>
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
        <form class="delete-modal" method="POST" id="deleteForm">
            @csrf
            @method('DELETE')

            <h3 class="delete-title">
                Are you sure you want to remove
                <span id="deleteFlavorName">this flavor</span>?
            </h3>

            <p class="delete-text">
                If you remove this product you will not have a chance to undo it.
            </p>

            <div class="delete-actions">
                <button type="button" class="btn-delete-cancel" id="cancelDelete">
                    No, Cancel
                </button>

                <button type="submit" class="btn-delete-confirm">
                    Yes, I'm sure
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            function openModal(modal) {
                modal.classList.add("show");
            }

            /* =====================
               UTILITIES
            ===================== */
            function closeModalSmooth(modal) {
                const card = modal.querySelector(".modal-card");
                card.style.transform = "translateX(120%)";
                card.style.opacity = "0";

                setTimeout(() => {
                    modal.classList.remove("show");
                    card.style.transform = "";
                    card.style.opacity = "";
                }, 450);
            }

            function resetAddModal() {
                document.querySelectorAll("#addFlavorModal input[type='text']").forEach(i => i.value = "");
                document.getElementById("imagePreview").innerHTML =
                    `<span class="material-symbols-outlined">add_photo_alternate</span>`;
                document.getElementById("flavorImage").value = "";
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

            document.addEventListener("click", () => filterDropdown.classList.remove("show"));
            filterDropdown.onclick = e => e.stopPropagation();

            filterCheckboxes.forEach(cb => {
                cb.addEventListener("change", () => {

                    // single status
                    if (["available", "out"].includes(cb.dataset.filter)) {
                        filterCheckboxes.forEach(o => {
                            if (o !== cb && ["available", "out"].includes(o.dataset
                                    .filter)) {
                                o.checked = false;
                            }
                        });
                    }

                    // single type
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
                    if (selectedStatus && !text.includes(
                            selectedStatus.dataset.filter === "out" ? "out of stock" : "available"
                        )) return false;
                    if (selectedType && !text.includes(selectedType.dataset.filter)) return false;

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

                if (totalPages <= 1) {
                    prevBtn.style.display = nextBtn.style.display = pageNumbers.style.display = "none";
                    pageInfo.style.display = "block";
                    pageInfo.textContent = filteredRows.length ?
                        `Showing ${filteredRows.length} data` :
                        "No results found";
                    return;
                }

                prevBtn.disabled = page === 1;
                nextBtn.disabled = page === totalPages;
                pageInfo.style.display = "none";

                pageNumbers.innerHTML = "";
                for (let i = 1; i <= totalPages; i++) {
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
            searchInput.addEventListener("input", applyFilters);

            showPage(currentPage);
            /* =====================
               ADD MODAL (FIXED & SAFE)
            ===================== */

            const addModal = document.getElementById("addFlavorModal");
            if (!addModal) return; // safety guard

            const addForm = document.getElementById("addGallonForm");
            const addBtn = document.querySelector(".btn-add");

            // open modal
            if (addBtn) {
                addBtn.onclick = () => {
                    resetAddModal();
                    addModal.classList.add("show");
                };
            }

            // close buttons
            document.getElementById("closeAddModal")?.addEventListener("click", () =>
                closeModalSmooth(addModal)
            );

            document.getElementById("cancelAddModal")?.addEventListener("click", () => {
                resetAddModal();
                closeModalSmooth(addModal);
            });

            // click outside modal
            addModal.addEventListener("click", e => {
                if (e.target === addModal) closeModalSmooth(addModal);
            });

            // reset modal
            function resetAddModal() {
                if (addForm) addForm.reset();

                document.getElementById("imagePreview").innerHTML =
                    `<span class="material-symbols-outlined">add_photo_alternate</span>`;

                document.getElementById("flavorImage").value = "";
            }

            // image preview
            document.getElementById("flavorImage")?.addEventListener("change", function() {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = () => {
                    document.getElementById("imagePreview").innerHTML =
                        `<img src="${reader.result}">`;
                };
                reader.readAsDataURL(file);
            });

            /* =====================
               EDIT MODAL (FIXED)
            ===================== */

            const editModal = document.getElementById("editFlavorModal");
            const editForm = document.getElementById("editGallonForm");

            // open edit modal
            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {

                    const id = btn.dataset.id;
                    const size = btn.dataset.size;
                    const qty = btn.dataset.qty;
                    const price = btn.dataset.price;
                    const image = btn.dataset.image;

                    document.getElementById("editGallonSize").value = size;
                    document.getElementById("editQuantity").value = qty;
                    document.getElementById("editAddonPrice").value = price;

                    // reset file input
                    document.getElementById("editFlavorImage").value = "";

                    // preview image
                    document.getElementById("editImagePreview").innerHTML = image ?
                        `<img src="${image}">` :
                        `<span class="material-symbols-outlined">add_photo_alternate</span>`;

                    // set update URL
                    editForm.action = `/admin/gallons/${id}`;

                    openModal(editModal);
                });
            });

            // close buttons
            document.getElementById("closeEditModal")?.addEventListener("click", () =>
                closeModalSmooth(editModal)
            );

            document.getElementById("cancelEditModal")?.addEventListener("click", () =>
                closeModalSmooth(editModal)
            );

            // click outside modal
            editModal.addEventListener("click", e => {
                if (e.target === editModal) closeModalSmooth(editModal);
            });

            // image preview on change
            document.getElementById("editFlavorImage")?.addEventListener("change", function() {
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
               DELETE MODAL
            ===================== */
            const deleteModal = document.getElementById("deleteConfirmModal");
            const deleteName = document.getElementById("deleteFlavorName");
            const deleteForm = document.getElementById("deleteForm");
            const cancelDelete = document.getElementById("cancelDelete");

            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => {
                    const row = btn.closest("tr");
                    const id = btn.dataset.id;

                    // set name
                    deleteName.textContent = row.querySelector(".flavor-col strong").textContent;

                    // set form action (Laravel route)
                    deleteForm.action = `/admin/gallons/${id}`;

                    deleteModal.classList.add("show");
                });
            });

            cancelDelete.onclick = () => {
                deleteModal.classList.remove("show");
            };

            deleteModal.onclick = e => {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove("show");
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
