<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=14">
    <title>Edit Profile – Quinjay Ice Cream</title>
    <style>
        .edit-profile-page { padding: 24px 16px; max-width: 400px; margin: 0 auto; }
        .edit-profile-back { display: inline-flex; align-items: center; gap: 6px; color: var(--dash-muted); text-decoration: none; font-weight: 500; margin-bottom: 16px; }
        .edit-profile-back:hover { color: var(--dash-text); }
        .edit-profile-avatar-wrap { position: relative; width: 100px; height: 100px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; }
        .edit-profile-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .edit-profile-avatar-btn { position: absolute; bottom: 0; right: 0; width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #fff; }
        .edit-profile-avatar-btn .material-symbols-outlined { font-size: 18px; }
        .edit-profile-avatar-wrap input[type="file"] { display: none; }
        .edit-profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .edit-profile-field { margin-bottom: 16px; }
        .edit-profile-field.full { grid-column: 1 / -1; }
        .edit-profile-field label { display: block; font-size: 12px; font-weight: 600; color: var(--dash-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.03em; }
        .edit-profile-field input { width: 100%; padding: 12px 14px; background: var(--dash-pill); border: 1px solid var(--dash-border); border-radius: 10px; font-size: 15px; color: var(--dash-text); box-sizing: border-box; }
        .edit-profile-field input:read-only { opacity: 0.8; cursor: not-allowed; }
        .edit-profile-actions { margin-top: 24px; display: flex; gap: 12px; justify-content: stretch; }
        .btn-discard { flex: 1; padding: 12px 20px; font-size: 15px; font-weight: 600; color: var(--dash-primary); background: transparent; border: 2px solid var(--dash-primary); border-radius: 10px; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; transition: background 0.2s, color 0.2s; }
        .btn-discard:hover { background: var(--dash-primary); color: #fff; }
        .btn-save { flex: 1; padding: 12px 20px; font-size: 15px; font-weight: 600; color: #fff; background: #2563eb; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s; }
        .btn-save:hover { background: #1d4ed8; }
        .edit-profile-page .error-text { font-size: 12px; color: #dc2626; margin-top: 4px; }
        .edit-profile-page .has-error input { border-color: #dc2626; }
    </style>
</head>

<body>

    <div class="dashboard">
        <div class="dashboard-content">
        <header class="dashboard-header">
            <a href="{{ route('customer.dashboard') }}" class="header-logo" aria-label="Home">
                <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="header-logo-img" />
            </a>
            <nav class="header-nav" aria-label="Main navigation">
                <a href="{{ route('customer.dashboard') }}" class="header-nav-link">Home</a>
                <a href="{{ route('customer.order.history') }}" class="header-nav-link">Order</a>
                <a href="{{ route('customer.favorite') }}" class="header-nav-link">Favorite</a>
                <a href="{{ route('customer.messages') }}" class="header-nav-link">Messages</a>
            </nav>
            <div class="header-search">
                <span class="material-symbols-outlined search-icon">search</span>
                <input type="search" class="search-input" placeholder="Search here..." aria-label="Search" />
            </div>
            <a href="#" class="cart-link" aria-label="Cart">
                <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                <span class="cart-badge">0</span>
            </a>
            <a href="{{ route('customer.my-account') }}" class="header-profile avatar-wrap" aria-label="My Account">
                <img src="{{ asset($customer->image ?? 'img/default-user.png') }}" alt="Profile" class="avatar" />
            </a>
        </header>

        <main class="dashboard-main edit-profile-page">
            <a href="{{ route('customer.account-information') }}" class="edit-profile-back">
                <span class="material-symbols-outlined" style="font-size:20px">arrow_back</span> Back
            </a>

            @if (session('success'))
                <div style="margin-bottom:1rem;padding:0.75rem;background:#d4edda;color:#155724;border-radius:8px;">{{ session('success') }}</div>
            @endif

            <form action="{{ route('customer.update-profile') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="edit-profile-avatar-wrap">
                    <img id="avatar-preview" src="{{ asset($customer->image ?? 'img/default-user.png') }}" alt="Profile" />
                    <label class="edit-profile-avatar-btn" for="image-upload" title="Change photo">
                        <span class="material-symbols-outlined">add_a_photo</span>
                    </label>
                    <input type="file" name="image" id="image-upload" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>

                <div class="edit-profile-grid">
                    <div class="edit-profile-field {{ $errors->has('firstname') ? 'has-error' : '' }}">
                        <label for="firstname">First Name</label>
                        <input id="firstname" name="firstname" type="text" value="{{ old('firstname', $customer->firstname) }}" required>
                        @error('firstname')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="edit-profile-field {{ $errors->has('lastname') ? 'has-error' : '' }}">
                        <label for="lastname">Last Name</label>
                        <input id="lastname" name="lastname" type="text" value="{{ old('lastname', $customer->lastname) }}" required>
                        @error('lastname')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="edit-profile-field full {{ $errors->has('contact_no') ? 'has-error' : '' }}">
                        <label for="contact_no">Phone Number</label>
                        <input id="contact_no" name="contact_no" type="text" value="{{ old('contact_no', $customer->contact_no) }}" placeholder="e.g. 09123456789">
                        @error('contact_no')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="edit-profile-field full">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" value="{{ $customer->email }}" readonly>
                    </div>
                </div>

                <div class="edit-profile-actions">
                    <a href="{{ route('customer.account-information') }}" class="btn-discard">Discard</a>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </main>
        </div>

        <nav class="bottom-nav" aria-label="Main navigation">
            <a href="{{ route('customer.dashboard') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">home</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="{{ route('customer.order.history') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">shopping_bag</span>
                <span class="nav-label">Order</span>
            </a>
            <a href="{{ route('customer.favorite') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">favorite</span>
                <span class="nav-label">Favorite</span>
            </a>
            <a href="{{ route('customer.messages') }}" class="nav-item">
                <span class="material-symbols-outlined nav-icon">chat_bubble</span>
                <span class="nav-label">Messages</span>
            </a>
        </nav>
    </div>

    <script>
        document.getElementById('image-upload').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) { document.getElementById('avatar-preview').src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>
