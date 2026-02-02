<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=14">
    <title>Verify code – Change Password</title>
    <style>
        .account-page { padding: 24px 16px; max-width: 400px; margin: 0 auto; }
        .account-back { display: inline-flex; align-items: center; gap: 6px; color: var(--dash-muted); text-decoration: none; font-weight: 500; margin-bottom: 16px; }
        .account-back:hover { color: var(--dash-text); }
        .change-pw-title { font-size: 20px; font-weight: 700; color: var(--dash-text); margin-bottom: 8px; }
        .change-pw-subtitle { font-size: 14px; color: var(--dash-muted); margin-bottom: 24px; }
        .change-pw-subtitle strong { color: var(--dash-text); }
        .otp-inputs { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        .otp-inputs input { width: 56px; height: 56px; text-align: center; font-size: 1.5rem; font-weight: 600; border: 1px solid var(--dash-border); border-radius: 12px; background: var(--dash-pill); color: var(--dash-text); }
        .otp-inputs input:focus { outline: none; border-color: var(--dash-primary); background: var(--dash-bg); }
        .otp-inputs input.error { border-color: #dc2626; }
        .btn-primary { display: block; width: 100%; padding: 14px; text-align: center; font-size: 16px; font-weight: 600; color: #fff; border: none; border-radius: 12px; background: var(--dash-primary); cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: var(--dash-primary-hover, #c40018); }
        .resend-row { text-align: center; font-size: 14px; color: var(--dash-muted); margin-top: 16px; }
        .resend-row button.resend-link { background: none; border: none; padding: 0; font: inherit; font-size: 14px; color: var(--dash-primary); font-weight: 500; cursor: pointer; }
        .resend-row button.resend-link:hover { text-decoration: underline; }
        .resend-row button.resend-link.disabled { color: #9ca3af; cursor: not-allowed; pointer-events: none; }
        .resend-row .timer { font-weight: 600; color: var(--dash-text); }
        .alert { padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .error-text { text-align: center; margin-bottom: 12px; color: #dc2626; font-size: 14px; }
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
            <a href="{{ route('customer.my-account') }}" class="header-profile avatar-wrap" aria-label="My Account">
                <img src="{{ asset('img/default-user.png') }}" alt="Profile" class="avatar" />
            </a>
        </header>

        <main class="dashboard-main account-page">
            <a href="{{ route('customer.change-password') }}" class="account-back">
                <span class="material-symbols-outlined" style="font-size:20px">arrow_back</span> Back
            </a>

            <h1 class="change-pw-title">Verify code</h1>
            <p class="change-pw-subtitle">Enter the 4-digit code sent to <strong>{{ $email ?? '' }}</strong></p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <form id="verify-form" action="{{ route('customer.change-password.verify-otp.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="otp" id="otp-combined" value="{{ old('otp') }}">

                <div class="otp-inputs {{ $errors->has('otp') ? 'has-error' : '' }}">
                    <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" data-idx="0" aria-label="Digit 1">
                    <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" data-idx="1" aria-label="Digit 2">
                    <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" data-idx="2" aria-label="Digit 3">
                    <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" data-idx="3" aria-label="Digit 4">
                </div>
                @error('otp')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-primary">Verify</button>
            </form>

            <div class="resend-row">
                Didn't receive code?
                <form id="resend-form" action="{{ route('customer.change-password.resend-otp') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" id="resend-btn" class="resend-link disabled" disabled>Resend now</button>
                </form>
                <span class="timer" id="timer"> 5:00</span>
            </div>
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
        (function() {
            var inputs = document.querySelectorAll('.otp-inputs input');
            var combined = document.getElementById('otp-combined');
            var form = document.getElementById('verify-form');

            function updateCombined() {
                var val = '';
                inputs.forEach(function(inp) { val += inp.value; });
                combined.value = val;
            }

            inputs.forEach(function(input, i) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 1);
                    updateCombined();
                    if (this.value && i < inputs.length - 1) inputs[i + 1].focus();
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && i > 0) inputs[i - 1].focus();
                });
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 4);
                    for (var j = 0; j < paste.length && j < inputs.length; j++) {
                        inputs[j].value = paste[j];
                        inputs[j].classList.remove('error');
                    }
                    if (paste.length > 0) inputs[Math.min(paste.length, inputs.length - 1)].focus();
                    updateCombined();
                });
            });

            var oldOtp = "{{ old('otp') }}";
            if (oldOtp && oldOtp.length <= 4) {
                for (var k = 0; k < oldOtp.length && k < inputs.length; k++) {
                    inputs[k].value = oldOtp[k];
                }
                updateCombined();
            }

            form.addEventListener('submit', function() { updateCombined(); });

            var timerEl = document.getElementById('timer');
            var resendBtn = document.getElementById('resend-btn');
            var totalSeconds = 5 * 60;
            var remaining = totalSeconds;

            function formatTime(s) {
                var m = Math.floor(s / 60);
                var sec = s % 60;
                return m + ':' + (sec < 10 ? '0' : '') + sec;
            }

            function tick() {
                remaining--;
                timerEl.textContent = formatTime(remaining);
                if (remaining <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('disabled');
                    timerEl.textContent = '';
                }
            }

            var interval = setInterval(tick, 1000);
            timerEl.textContent = formatTime(remaining);
        })();
    </script>
</body>

</html>
