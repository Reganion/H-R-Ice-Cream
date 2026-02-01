<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img/Mask group.png') }}">
    <title>Quinjay Ice Cream - Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <div class="left">
        <img src="{{ asset('img/signup.png') }}" alt="Ice Cream Image">
        <div class="logo-left">
            <a href="{{ route('customer.home') }}">
                <img src="{{ asset('img/logoleft.png') }}" alt="Quinjay Logo">
            </a>
        </div>
    </div>

    <div class="right">
        <div class="container">
            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="Quinjay Logo">
            </div>

            <div class="title">Create your Account</div>

            @if (session('success'))
                <div style="margin-bottom:1rem;padding:0.75rem;background:#d4edda;color:#155724;border-radius:6px;">{{ session('success') }}</div>
            @endif

            <!-- START FORM -->
            <form action="{{ route('customer.register.submit') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="input-group {{ $errors->has('firstname') ? 'has-error' : '' }}" style="flex:1">
                        <label for="firstName">First Name</label>
                        <input id="firstName" name="firstname" type="text" placeholder="First Name" value="{{ old('firstname') }}">
                        @error('firstname')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group {{ $errors->has('lastname') ? 'has-error' : '' }}" style="flex:1">
                        <label for="lastName">Last Name</label>
                        <input id="lastName" name="lastname" type="text" placeholder="Last Name" value="{{ old('lastname') }}">
                        @error('lastname')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="input-group {{ $errors->has('email') ? 'has-error' : '' }}">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" placeholder="Email Address" value="{{ old('email') }}">
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group {{ $errors->has('password') ? 'has-error' : '' }}">
                    <label for="password">Create Password</label>
                    <input id="password" name="password" type="password" placeholder="Create Password">
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    <label for="confirmPassword">Re-enter Password</label>
                    <input id="confirmPassword" name="password_confirmation" type="password" placeholder="Re-enter Password">
                    @error('password_confirmation')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="signup-btn">Sign Up</button>
            </form>
            <!-- END FORM -->

            <div class="divider"><span>Or, Sign Up with</span></div>

            <a href="{{ route('customer.login.google') }}" class="google-btn" style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;color:inherit;">
                <img src="{{ asset('img/google.png') }}" alt="Google Logo">
                Sign Up with Google
            </a>

            <div class="login">
                Already have an account? 
                <a href="{{ route('customer.login') }}">Login</a>
            </div>

        </div>
    </div>

</body>
</html>
