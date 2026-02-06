<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer/landing.css') }}">
    <title>Quinjay Ice Cream</title>
</head>

<body>
    <div class="landing">
        <!-- Red top section: header design + logo (curve image as background behind red) -->
        <section class="landing-top" style="background-image: url('{{ asset('img/background.png') }}');">
            <img src="{{ asset('img/Landing_header.png') }}" alt="" class="landing-header-img" aria-hidden="true">
            <div class="landing-logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Quinjay Ice Cream" class="landing-logo">
            </div>
        </section>

        <!-- Spacer below red section (curve is in red section background) -->
        <div class="landing-wave" aria-hidden="true"></div>

        <!-- White bottom section -->
        <section class="landing-bottom">
            <h1 class="landing-headline">
                <span class="landing-headline-red">Your scoop</span><br>
                <span class="landing-headline-black">is just a click away!</span>
            </h1>
            <p class="landing-tagline">Bringing sweetness straight to your door.</p>
            <div class="landing-btns">
                <a href="{{ route('customer.home') }}" class="landing-btn landing-btn-customer">Customer</a>
                <a href="{{ route('driver.landing') }}" class="landing-btn landing-btn-driver">Driver</a>
            </div>
        </section>
    </div>
</body>

</html>
