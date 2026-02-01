<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img/Mask group.png') }}">
    <title>H & R Ice Cream - Admin Login</title>

    <!-- Import Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .auth-tabs { display: flex; gap: 0; margin-bottom: 24px; border-bottom: 2px solid #e5e7eb; }
        .auth-tab { flex: 1; padding: 12px; text-align: center; font-size: 15px; font-weight: 600; color: #6b7280; cursor: pointer; border: none; background: transparent; }
        .auth-tab.active { color: #d90000; border-bottom: 2px solid #d90000; margin-bottom: -2px; }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .upload-box { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
        .upload-preview { width: 64px; height: 64px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; overflow: hidden; }
        .upload-preview img { width: 100%; height: 100%; object-fit: cover; }
        .btn-upload { padding: 8px 16px; border-radius: 8px; border: none; background: #d90000; color: #fff; font-size: 13px; font-weight: 500; cursor: pointer; }
        .btn-upload:hover { opacity: 0.9; }
        .register-form .input-group label { position: static; transform: none; background: transparent; font-size: 14px; color: #374151; margin-bottom: 6px; display: block; }
        .register-form .input-group input { padding: 12px 14px; }
        .register-form .input-inner { position: relative; }
        .register-form .input-group-password .password-toggle { right: 10px; }
        .signup-btn { width: 100%; padding: 14px; background: #d90000; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; color: #fff; cursor: pointer; margin: 10px 0 20px; }
        .signup-btn:hover { opacity: 0.95; }
        .switch-auth { text-align: center; font-size: 14px; margin-top: 16px; color: #666; }
        .switch-auth a { color: #d90000; font-weight: 600; text-decoration: none; }
        .switch-auth a:hover { text-decoration: underline; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>

<body>

    <!-- LEFT IMAGE PANEL -->
    <div class="left">
        <img src="{{ asset('img/signins.png') }}" alt="Ice Cream Image">
        <div class="logo-left">
            <a href="{{ route('landing') }}">
                <img src="{{ asset('img/logoleft.png') }}" alt="Quinjay Logo">
            </a>
        </div>
    </div>

    <!-- RIGHT LOGIN FORM -->
    <div class="right">
        <div class="container">

            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="Quinjay Logo">
            </div>

            @if(session('success'))
                <div style="margin-bottom:1rem;padding:0.75rem;background:#d4edda;color:#155724;border-radius:8px;font-size:14px;">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div style="margin-bottom:1rem;padding:0.75rem;background:#f8d7da;color:#721c24;border-radius:8px;font-size:14px;">{{ session('error') }}</div>
            @endif

            <div class="auth-tabs">
                <button type="button" class="auth-tab active" data-tab="login">Sign In</button>
                <button type="button" class="auth-tab" data-tab="register">Create Account</button>
            </div>

            <!-- LOGIN PANEL -->
            <div id="panel-login" class="auth-panel active">
                <div class="title desktop-title">Admin Sign In</div>
                <div class="title mobile-title">Login to Admin</div>

                <form id="login-form" action="{{ route('admin.login.submit') }}" method="POST">
                    @csrf

                    <div class="input-group {{ $errors->has('email') && !old('_register') ? 'has-error' : '' }}">
                        <div class="input-inner">
                            <input type="email" name="email" id="email" placeholder=" " value="{{ old('email') }}">
                            <label for="email">Email Address</label>
                        </div>
                        @if(!old('_register') && $errors->has('email'))
                            <span class="error-text">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="input-group {{ $errors->has('password') && !old('_register') ? 'has-error' : '' }} input-group-password">
                        <div class="input-inner">
                            <input type="password" name="password" id="password" placeholder=" ">
                            <label for="password">Password</label>
                            <button type="button" class="password-toggle" aria-label="Show password" onclick="togglePassword('password')" tabindex="-1">
                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @if(!old('_register') && $errors->has('password'))
                            <span class="error-text">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="forgot-password">
                        <a href="#">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <div class="divider"><span>Or continue with</span></div>
                <a href="{{ route('admin.login.google') }}" class="google-btn" style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;color:inherit;">
                    <img src="{{ asset('img/google.png') }}" alt="Google Logo">
                    Sign in with Google
                </a>
            </div>

            <!-- CREATE ACCOUNT PANEL -->
            <div id="panel-register" class="auth-panel">
                <div class="title">Create Admin Account</div>

                <form action="{{ route('admin.register.submit') }}" method="POST" enctype="multipart/form-data" class="register-form">
                    @csrf
                    <input type="hidden" name="_register" value="1">

                    <div class="upload-box">
                        <div class="upload-preview" id="adminImagePreview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#374151;margin-bottom:6px;display:block;">Profile Picture</label>
                            <input type="file" id="adminImage" name="image" accept="image/*" hidden>
                            <button type="button" class="btn-upload" onclick="document.getElementById('adminImage').click()">Upload</button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" placeholder="First name" value="{{ old('first_name') }}" maxlength="255">
                            @error('first_name')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="input-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" placeholder="Last name" value="{{ old('last_name') }}" maxlength="255">
                            @error('last_name')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="input-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="reg_email">Email Address</label>
                        <input type="email" id="reg_email" name="email" placeholder="Email address" value="{{ old('email') }}" maxlength="255">
                        @error('email')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group {{ $errors->has('password') ? 'has-error' : '' }} input-group-password">
                        <label for="reg_password">Password</label>
                        <div class="input-inner">
                            <input type="password" id="reg_password" name="password" placeholder="Create password">
                            <button type="button" class="password-toggle" aria-label="Show password" onclick="togglePassword('reg_password')" tabindex="-1">
                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('password')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="input-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password">
                        @error('password_confirmation')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="signup-btn">Create Account</button>
                </form>

                <div class="divider"><span>Or continue with</span></div>
                <a href="{{ route('admin.login.google') }}" class="google-btn" style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;color:inherit;">
                    <img src="{{ asset('img/google.png') }}" alt="Google Logo">
                    Sign up with Google
                </a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.auth-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                const target = this.dataset.tab;
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('panel-' + target).classList.add('active');
            });
        });

        @if($errors->any() && old('_register'))
            document.querySelector('[data-tab="register"]').click();
        @endif

        function togglePassword(id) {
            const input = document.getElementById(id);
            const group = input.closest('.input-group-password') || input.closest('.input-group');
            if (!group) return;
            const eye = group.querySelector('.icon-eye');
            const eyeOff = group.querySelector('.icon-eye-off');
            if (input.type === 'password') {
                input.type = 'text';
                if (eye) eye.style.display = 'none';
                if (eyeOff) eyeOff.style.display = 'block';
            } else {
                input.type = 'password';
                if (eye) eye.style.display = 'block';
                if (eyeOff) eyeOff.style.display = 'none';
            }
        }

        document.getElementById('adminImage')?.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('adminImagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">'; };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
            }
        });
    </script>
</body>

</html>
