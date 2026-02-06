<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/customer/home.css') }}">
    <title>Quinjay Ice Cream</title>
    <style>
        .service-phones {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('{{ asset('img/background-cheerful.png') }}');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            overflow: hidden;
        }
    </style>
</head>

<body>

    <div id="floatingAlert" class="floating-alert"></div>

    <header>
        <span class="material-symbols-outlined menu-toggle" onclick="toggleMenu()">menu</span>

        <div class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="Quinjay Logo" />
        </div>

        <nav id="nav">
            <a href="#home">HOME</a>
            <a href="#top-flavors">Top Flavors</a>
            <a href="#service">Services</a>
            <a href="{{ route('customer.about') }}">About Us</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="auth">
            
                <a href="{{ route('customer.login') }}">Sign in</a>
                <button onclick="window.location.href='#'">
                    Sign up <span class="material-symbols-outlined card-icon">arrow_right_alt</span>
                </button>
        </div>


    </header>

    <section class="hero" id="home">
        <div class="hero-text">
            <h1>
                <span class="red">Your scoop</span><br>
                <span class="black">is just a click away!</span>
            </h1>
            <p>Enjoy rich, handcrafted flavors made from quality ingredients and delivered with care. One click unlocks
                a
                world of creamy indulgence—crafted to satisfy every sweet moment.</p>
            <div class="btns">
  
                <button class="download-btn">Download App</button>
            </div>
        </div>

        <div class="phones">
            <img src="{{ asset('img/cellphone 1.png') }}" class="phone phone-1" alt="Cellphone 1">
            <img src="{{ asset('img/cellphone 2.png') }}" class="phone phone-2" alt="Cellphone 2">
        </div>

    </section>

    <!-- Top Flavors Section -->
    <section class="flavors" id="top-flavors">
        <h2 class="section-title">Our Top Flavors</h2>

        <div class="flavors-wrapper">
            <button class="slide-btn left" onclick="slideFlavors(-1)">&#10094;</button>

            <div class="flavors-container">
                <!-- Your flavor cards here -->

                @foreach ($flavors as $flavor)
                    <div class="flavor-card">
                        <div class="flavor-img-wrap" role="button" tabindex="0" aria-label="Preview {{ $flavor->name }}">
                            <img src="{{ asset($flavor->image) }}" alt="{{ $flavor->name }}" class="flavor-img" data-full-src="{{ asset($flavor->image) }}" data-caption="{{ $flavor->name }}">
                        </div>
                        <div class="flavor-content">
                            <h3 class="flavor-name">{{ $flavor->name }}</h3>

                            <div class="flavor-rating">
                                <img src="{{ asset('img/star.png') }}" alt="Star">
                                <span class="rating-text">{{ $flavor->rating ?? '0' }} ({{ $flavor->reviews ?? '0' }}
                                    Reviews)</span>
                            </div>

                            <div class="flavor-price">₱{{ number_format($flavor->price, 2) }}</div>
                            <p class="flavor-desc">{{ $flavor->description ?? '' }}</p>

                            <button class="flavor-btn" data-flavor-id="{{ $flavor->id }}">
                                Order now
                            </button>


                        </div>
                    </div>
                @endforeach

            </div>

            <button class="slide-btn right" onclick="slideFlavors(1)">&#10095;</button>
        </div>
    </section>


    <section class="service" id="service">
        <div class="service-phones">
            <img src="{{ asset('img/cheerful.png') }}" alt="Cheerful Frame" />
        </div>

        <div class="service-text">
            <h1>We Provide Best Service for Our Customer</h1>
            <p>Corem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit interdum, ac
                aliquet odio mattis.
                Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>

            <div class="service-features">
                <div class="feature">
                    <div class="icon-bg" style="background-color: #ffe6e6;">
                        <img src="{{ asset('icons/award_star.png') }}" alt="Best Quality" />
                    </div>
                    <p>Best Quality</p>
                </div>
                <div class="feature">
                    <div class="icon-bg" style="background-color: #e6e6ff;">
                        <img src="{{ asset('icons/delivery_truck_speed.png') }}" alt="Home Delivery" />
                    </div>
                    <p>Home Delivery</p>
                </div>
                <div class="feature">
                    <div class="icon-bg" style="background-color: #fff7cc;">
                        <img src="{{ asset('icons/local_taxi.png') }}" alt="Pre Booking" />
                    </div>
                    <p>Pre Booking</p>
                </div>
                <div class="feature">
                    <div class="icon-bg" style="background-color: #e6ffe6;">
                        <img src="{{ asset('icons/shopping_bag.png') }}" alt="Easy to Order" />
                    </div>
                    <p>Easy to Order</p>
                </div>
            </div>

        </div>
    </section>

    <section class="about" id="aboutus">
        <div class="about-container">

            <h2 class="about-title">
                <span>What are our Customers <br>Say About Us</span>

                <div class="arrows">
                    <img src="{{ asset('icons/left.png') }}" class="arrow-btn left" alt="left arrow">
                    <img src="{{ asset('icons/right.png') }}" class="arrow-btn right" alt="right arrow">
                </div>
            </h2>

            <div class="about-boxes-wrapper">
                <div class="about-boxes">


                    @foreach ($feedbacks as $feedback)
                        <div class="testimonial-card">
                            <div class="profile">
                                <img src="{{ asset($feedback->photo) }}" alt="customer photo">
                                <div class="profile-info">
                                    <h3>{{ $feedback->customer_name }}</h3>
                                    <div class="stars">{!! str_repeat('★', $feedback->rating) !!}</div>
                                </div>
                            </div>
                            <p class="testimonial-text">
                                {{ $feedback->testimonial }}
                            </p>
                            <span
                                class="date">{{ \Carbon\Carbon::parse($feedback->feedback_date)->format('d M Y') }}</span>

                        </div>
                    @endforeach

                </div>
            </div>
    </section>


    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="contact-container">
            <!-- Contact Form -->
            <div class="contact-form">
                <h2>Contact Us</h2>
                <p>Indulge in our creamy delights! Reach out to us for any questions or to share your sweet experience..
                </p>

                <form>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" placeholder="Full name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" placeholder="example@gmail.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" placeholder="(+63) 9123456789">
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" placeholder="Your Message"></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Send message</button>
                </form>
            </div>

            <!-- Image -->
            <div class="contact-image">
                <img src="{{ asset('img/Contact.png') }}" alt="Ice Cream Image">
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <div class="footer-logo">
                    <img src="{{ asset('img/logo.png') }}" alt="Quinjay Logo">
                </div>
                <p>Creating moments of joy, one scoop at a time. Premium ice cream crafted with love and the finest
                    ingredients.</p>
                <div class="social-links">
                    <a href="#"><img src="{{ asset('icons/facebook.png') }}" alt="Facebook"
                            width="24"></a>
                    <a href="#"><img src="{{ asset('icons/twitter.png') }}" alt="Twitter"
                            width="24"></a>
                    <a href="#"><img src="{{ asset('icons/instagram.png') }}" alt="Instagram"
                            width="24"></a>

                </div>
            </div>

            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#top-flavors">Top Flavors</a></li>
                    <li><a href="#service">Services</a></li>
                    <li><a href="#aboutus">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Our Services</h3>
                <ul class="footer-links">
                    <li><a href="#">Ice Cream Delivery</a></li>
                    <li><a href="#">Catering Events</a></li>
                    <li><a href="#">Custom Flavors</a></li>
                    <li><a href="#">Gift Cards</a></li>
                    <li><a href="#">Monthly Subscriptions</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Contact Info</h3>
                <ul class="footer-links">
                    <li>123 Sweet Street, Ice Cream City</li>
                    <li>IC 12345</li>
                    <li>(123) 456-7890</li>
                    <li>info@quinjayicecream.com</li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            <p>All copyright &copy; 2025 Reserved</p>
        </div>
    </footer>

    <!-- Image preview lightbox -->
    <div id="imagePreviewModal" class="image-preview-modal" aria-hidden="true">
        <div class="image-preview-overlay"></div>
        <div class="image-preview-content">
            <button type="button" class="image-preview-close" aria-label="Close preview">&times;</button>
            <img src="" alt="" class="image-preview-img">
            <p class="image-preview-caption"></p>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" title="Go to top"><img src="{{ asset('icons/arrow-up.png') }}" alt="Instagram"
            width="24"></button>

    <script>
        function toggleMenu() {
            const nav = document.getElementById('nav');
            nav.classList.toggle('active');
        }


        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', () => {
                const nav = document.getElementById('nav');
                nav.classList.remove('active');
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.flavors-container');
            const leftBtn = document.querySelector('.slide-btn.left');
            const rightBtn = document.querySelector('.slide-btn.right');

            if (!container || !leftBtn || !rightBtn) return;

            const cards = container.querySelectorAll('.flavor-card');
            if (cards.length === 0) return;

            const gap = parseInt(getComputedStyle(cards[0]).marginRight) || 20;
            let cardWidth = cards[0].offsetWidth + gap;

            // Update cardWidth on window resize
            window.addEventListener('resize', () => {
                cardWidth = cards[0].offsetWidth + gap;
                updateArrowState();
            });

            // Scroll one card at a time
            function scrollRight() {
                container.scrollBy({
                    left: cardWidth,
                    behavior: 'smooth'
                });
                setTimeout(updateArrowState, 300);
            }

            function scrollLeft() {
                container.scrollBy({
                    left: -cardWidth,
                    behavior: 'smooth'
                });
                setTimeout(updateArrowState, 300);
            }

            rightBtn.addEventListener('click', scrollRight);
            leftBtn.addEventListener('click', scrollLeft);

            // Update arrow visibility
            function updateArrowState() {
                const maxScroll = container.scrollWidth - container.clientWidth;
                leftBtn.style.display = container.scrollLeft <= 0 ? 'none' : 'block';
                rightBtn.style.display = container.scrollLeft >= maxScroll - 1 ? 'none' : 'block';
            }

            container.addEventListener('scroll', updateArrowState);

            // Optional: drag / swipe support
            let isDown = false,
                startX, scrollLeftPos;

            container.addEventListener('mousedown', e => {
                isDown = true;
                startX = e.pageX - container.offsetLeft;
                scrollLeftPos = container.scrollLeft;
            });
            container.addEventListener('mouseleave', () => {
                isDown = false;
            });
            container.addEventListener('mouseup', () => {
                isDown = false;
            });
            container.addEventListener('mousemove', e => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                container.scrollLeft = scrollLeftPos - (x - startX) * 1.5;
            });

            container.addEventListener('touchstart', e => {
                startX = e.touches[0].pageX - container.offsetLeft;
                scrollLeftPos = container.scrollLeft;
            });
            container.addEventListener('touchmove', e => {
                const x = e.touches[0].pageX - container.offsetLeft;
                container.scrollLeft = scrollLeftPos - (x - startX) * 1.3;
            });

            // Initialize
            updateArrowState();
        });

        // Image preview lightbox
        const modal = document.getElementById('imagePreviewModal');
        const modalImg = modal?.querySelector('.image-preview-img');
        const modalCaption = modal?.querySelector('.image-preview-caption');
        const modalClose = modal?.querySelector('.image-preview-close');
        const modalOverlay = modal?.querySelector('.image-preview-overlay');

        function openImagePreview(src, caption) {
            if (!modal || !modalImg) return;
            modalImg.src = src;
            modalImg.alt = caption;
            modalCaption.textContent = caption;
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeImagePreview() {
            if (!modal) return;
            modal.setAttribute('aria-hidden', 'true');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.flavor-img-wrap').forEach(wrap => {
            wrap.addEventListener('click', (e) => {
                const img = wrap.querySelector('.flavor-img');
                if (img && (img.dataset.fullSrc || img.src)) {
                    e.preventDefault();
                    openImagePreview(img.dataset.fullSrc || img.src, img.dataset.caption || img.alt);
                }
            });
            wrap.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    wrap.click();
                }
            });
        });

        if (modalClose) modalClose.addEventListener('click', closeImagePreview);
        if (modalOverlay) modalOverlay.addEventListener('click', closeImagePreview);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal?.classList.contains('active')) closeImagePreview();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sliderWrapper = document.querySelector('.about-boxes-wrapper');
            const slider = document.querySelector('.about-boxes');
            const leftArrow = document.querySelector('.arrow-btn.left');
            const rightArrow = document.querySelector('.arrow-btn.right');

            if (!sliderWrapper || !slider || !leftArrow || !rightArrow) return;

            const cards = slider.querySelectorAll('.testimonial-card');
            const gap = parseInt(getComputedStyle(cards[0]).marginRight) || 30;
            const cardWidth = cards[0].offsetWidth + gap;
            const autoSlideInterval = 4000; // 4 seconds per slide
            let autoSlide;

            // Update arrow disabled state
            function updateArrowState() {
                const maxScroll = sliderWrapper.scrollWidth - sliderWrapper.clientWidth;
                leftArrow.disabled = sliderWrapper.scrollLeft <= 0;
                rightArrow.disabled = sliderWrapper.scrollLeft >= maxScroll - 1;
                leftArrow.classList.toggle('disabled', leftArrow.disabled);
                rightArrow.classList.toggle('disabled', rightArrow.disabled);
            }

            // Scroll one card at a time
            function scrollRight() {
                sliderWrapper.scrollBy({
                    left: cardWidth,
                    behavior: 'smooth'
                });
                setTimeout(updateArrowState, 300);
            }

            function scrollLeft() {
                sliderWrapper.scrollBy({
                    left: -cardWidth,
                    behavior: 'smooth'
                });
                setTimeout(updateArrowState, 300);
            }

            rightArrow.addEventListener('click', () => {
                scrollRight();
                resetAutoSlide();
            });

            leftArrow.addEventListener('click', () => {
                scrollLeft();
                resetAutoSlide();
            });

            // Drag / swipe support
            let isDown = false,
                startX, scrollLeftPos;

            sliderWrapper.addEventListener('mousedown', e => {
                isDown = true;
                startX = e.pageX - sliderWrapper.offsetLeft;
                scrollLeftPos = sliderWrapper.scrollLeft;
                clearInterval(autoSlide); // pause auto-slide while dragging
            });
            sliderWrapper.addEventListener('mouseleave', () => {
                isDown = false;
                resetAutoSlide();
            });
            sliderWrapper.addEventListener('mouseup', () => {
                isDown = false;
                snapToCard();
                resetAutoSlide();
            });
            sliderWrapper.addEventListener('mousemove', e => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - sliderWrapper.offsetLeft;
                sliderWrapper.scrollLeft = scrollLeftPos - (x - startX) * 1.5;
            });

            sliderWrapper.addEventListener('touchstart', e => {
                startX = e.touches[0].pageX - sliderWrapper.offsetLeft;
                scrollLeftPos = sliderWrapper.scrollLeft;
                clearInterval(autoSlide);
            });
            sliderWrapper.addEventListener('touchmove', e => {
                const x = e.touches[0].pageX - sliderWrapper.offsetLeft;
                sliderWrapper.scrollLeft = scrollLeftPos - (x - startX) * 1.3;
            });
            sliderWrapper.addEventListener('touchend', () => {
                snapToCard();
                resetAutoSlide();
            });

            function snapToCard() {
                const index = Math.round(sliderWrapper.scrollLeft / cardWidth);
                sliderWrapper.scrollLeft = index * cardWidth;
                updateArrowState();
            }

            // Auto-slide
            function startAutoSlide() {
                autoSlide = setInterval(() => {
                    const maxScroll = sliderWrapper.scrollWidth - sliderWrapper.clientWidth;
                    if (sliderWrapper.scrollLeft >= maxScroll - 1) {
                        sliderWrapper.scrollLeft = 0; // loop back to start
                    } else {
                        scrollRight();
                    }
                }, autoSlideInterval);
            }

            function resetAutoSlide() {
                clearInterval(autoSlide);
                startAutoSlide();
            }

            // Initialize
            updateArrowState();
            startAutoSlide();
        });
    </script>


    <script>
        //Get the button
        const scrollToTopBtn = document.getElementById("scrollToTopBtn");

        // Show button after scrolling down 300px
        window.onscroll = function() {
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                scrollToTopBtn.style.display = "block";
            } else {
                scrollToTopBtn.style.display = "none";
            }
        };

        // Scroll to top when clicked
        scrollToTopBtn.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const floatingAlert = document.getElementById('floatingAlert');

            function showFloatingAlert(message, type = 'error') {
                floatingAlert.innerHTML = message;
                if (type === 'success') {
                    floatingAlert.style.backgroundColor = '#e6ffe6';
                    floatingAlert.style.color = '#008000';
                    floatingAlert.style.borderColor = '#008000';
                } else {
                    floatingAlert.style.backgroundColor = '#ffe6e6';
                    floatingAlert.style.color = '#E3001B';
                    floatingAlert.style.borderColor = '#E3001B';
                }

                floatingAlert.classList.add('show');

                setTimeout(() => {
                    floatingAlert.classList.remove('show');
                }, 5000); // auto hide after 5s
            }

            // Sign In Errors
            @if ($errors->has('email') || $errors->has('password'))
                let message = `<ul>
            @if ($errors->has('email'))
                <li>{{ $errors->first('email') }}</li>
            @endif
            @if ($errors->has('password'))
                <li>{{ $errors->first('password') }}</li>
            @endif
        </ul>`;
                showFloatingAlert(message);
            @endif

            // Sign Up Errors
            @if ($errors->any() && !($errors->has('email') || $errors->has('password')))
                let message = `<ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>`;
                showFloatingAlert(message);
            @endif

            // Success message
            @if (session('success'))
                showFloatingAlert("{{ session('success') }}", 'success');
            @endif
        });
    </script>
    <script>
        const sections = document.querySelectorAll("section");
        const navLinks = document.querySelectorAll("nav a");

        window.addEventListener("scroll", () => {
            let current = "";

            sections.forEach(section => {
                const sectionTop = section.offsetTop - 120;
                if (scrollY >= sectionTop) {
                    current = section.getAttribute("id");
                }
            });

            navLinks.forEach(link => {
                link.classList.remove("active");
                if (link.getAttribute("href").includes(current)) {
                    link.classList.add("active");
                }
            });
        });
    </script>
    <script>
        // Smooth scroll with easing (better than default smooth)
        document.querySelectorAll('nav a[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;

                const headerOffset = 90; // adjust for sticky nav height
                const targetPosition = target.offsetTop - headerOffset;

                smoothScroll(targetPosition, 900); // 900ms duration
            });
        });

        function smoothScroll(targetY, duration = 800) {
            const startY = window.pageYOffset;
            const changeY = targetY - startY;
            let startTime = null;

            function animateScroll(currentTime) {
                if (!startTime) startTime = currentTime;
                const time = currentTime - startTime;
                const progress = Math.min(time / duration, 1);

                // Ease-out cubic (smooth & modern)
                const ease = 1 - Math.pow(1 - progress, 3);

                window.scrollTo(0, startY + changeY * ease);

                if (progress < 1) {
                    requestAnimationFrame(animateScroll);
                }
            }

            requestAnimationFrame(animateScroll);
        }
    </script>

</body>

</html>
