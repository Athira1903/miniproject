<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content=" Woodpecker - Your one-stop shop for premium furniture. Free shipping across India!">
    <meta name="author" content="Woodpecker">
    <title>Woodpecker - Furniture Store</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Bootstrap CSS for grid system -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Announcement Bar -->
    <div class="announcement-bar" aria-label="Announcement: Free shipping in India">
        Free shipping in India
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <!-- Search and Currency -->
            <div class="left-header">
                <div class="search-box">
                    <label for="search-input" class="visually-hidden">Search</label>
                    <input id="search-input" type="search" placeholder="Search" aria-label="Search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </div>
                <div class="currency-selector">
                    <label for="currency-select" class="visually-hidden">Select currency</label>
                    <select id="currency-select" aria-label="Currency selector">
                        <option value="INR">🇮🇳 INR ₹</option>
                        <option value="USD">🇺🇸 USD $</option>
                    </select>
                </div>
            </div>

            <!-- Logo -->
            <div class="logo">
                <a href="/">
                    <img src="logo.png" alt="Woodpecker Logo">
                </a>
            </div>

            <!-- User Actions -->
            <div class="right-header">
                <div class="user-actions">
                    <a href="login.html" aria-label="Account">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="wishlist.php" class="wishlist-icon" aria-label="Wishlist">
                        <i class="fas fa-heart"></i>
                        <span class="counter">0</span>
                    </a>
                    <a href="cart.php" class="cart-icon" aria-label="Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="counter">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>FurniShiaa</title>
        <style>
            /* Previous styles remain */
            
            /* Updated Navigation Styles */
            .nav-menu {
                display: flex;
                justify-content: center;
                gap: 2rem;
                padding: 1rem;
                border-bottom: 1px solid #eee;
                position: relative;
            }
    
            .nav-item {
                position: relative;
                padding: 0.5rem 0;
            }
    
            .nav-link {
                text-decoration: none;
                color: #333;
                text-transform: uppercase;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
    
            .nav-link:after {
                content: '▼';
                font-size: 0.7rem;
            }
    
            .dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                background: white;
                min-width: 200px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 100;
            }
    
            .nav-item:hover .dropdown {
                opacity: 1;
                visibility: visible;
            }
    
            .dropdown-item {
                display: block;
                padding: 0.8rem 1rem;
                color: #666;
                text-decoration: none;
                transition: background 0.3s;
            }
    
            .dropdown-item:hover {
                background: #f5f5f5;
                color: #000;
            }
    
            /* Simple nav items without dropdown */
            .nav-item.simple .nav-link:after {
                display: none;
            }
        </style>
    </head>
    <body>
        <!-- Previous header content -->
    
        <nav class="nav-menu">
            <div class="nav-item simple">
                <a href="#" class="nav-link">ALL FURNITURE</a>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">LIVING ROOM</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Sofas</a>
                    <a href="#" class="dropdown-item">Coffee Tables</a>
                    <a href="#" class="dropdown-item">TV Units</a>
                    <a href="#" class="dropdown-item">Chairs</a>
                    <a href="#" class="dropdown-item">Side Tables</a>
                </div>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">BEDROOM</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Beds</a>
                    <a href="#" class="dropdown-item">Wardrobes</a>
                    <a href="#" class="dropdown-item">Mattresses</a>
                    <a href="#" class="dropdown-item">Side Tables</a>
                    <a href="#" class="dropdown-item">Dressing Tables</a>
                </div>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">DINING & KITCHEN</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Dining Tables</a>
                    <a href="#" class="dropdown-item">Dining Chairs</a>
                    <a href="#" class="dropdown-item">Dining Sets</a>
                    <a href="#" class="dropdown-item">Kitchen Storage</a>
                </div>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">KIDS ROOM</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Kids Beds</a>
                    <a href="#" class="dropdown-item">Study Tables</a>
                    <a href="#" class="dropdown-item">Storage</a>
                    <a href="#" class="dropdown-item">Decor</a>
                </div>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">STORAGE</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Cabinets</a>
                    <a href="#" class="dropdown-item">Bookshelves</a>
                    <a href="#" class="dropdown-item">Shoe Racks</a>
                    <a href="#" class="dropdown-item">Wall Shelves</a>
                </div>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">DECOR</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Wall Art</a>
                    <a href="#" class="dropdown-item">Mirrors</a>
                    <a href="#" class="dropdown-item">Vases</a>
                    <a href="#" class="dropdown-item">Lighting</a>
                </div>
            </div>
    
            <div class="nav-item">
                <a href="#" class="nav-link">STUDY & OFFICE</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-item">Study Tables</a>
                    <a href="#" class="dropdown-item">Office Chairs</a>
                    <a href="#" class="dropdown-item">Bookshelves</a>
                    <a href="#" class="dropdown-item">File Cabinets</a>
                </div>
            </div>
        </nav>
    
        <!-- Rest of the previous content -->
    
        <script>
            // Add hover delay to prevent accidental dropdown triggering
            const navItems = document.querySelectorAll('.nav-item');
            let timeout;
    
            navItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                });
    
                item.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => {
                        const dropdown = item.querySelector('.dropdown');
                        if (dropdown) {
                            dropdown.style.opacity = '0';
                            dropdown.style.visibility = 'hidden';
                        }
                    }, 200);
                });
            });
        </script>
    </body>
    </html>
    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Hero Slider Section -->
        <div class="hero-slider" aria-label="Image slider">
            <div class="slide active" style="background-image: url('3.jpg');" aria-label="Home Makeovers">
                <div class="slide-content">
                    <h2>Home Makeovers Made Easy</h2>
                    <p>Find the Perfect Pieces to Reflect Your Style</p>
                    <button class="shop-now-btn" type="button" aria-label="Shop Now">Shop Now</button>
                </div>
            </div>
            <div class="slide" style="background-image: url('4.png');" aria-label="Modern Comfort Designs">
                <div class="slide-content">
                    <h2>Modern Comfort Designs</h2>
                    <p>Transform Your Space with Elegant Furniture</p>
                    <button class="shop-now-btn" type="button" aria-label="Explore Now">Explore Now</button>
                </div>
            </div>
            <div class="slide" style="background-image: url('6.jpg');" aria-label="Luxury Meets Functionality">
                <div class="slide-content">
                    <h2>Luxury Meets Functionality</h2>
                    <p>Discover Our Premium Collection</p>
                    <button class="shop-now-btn" type="button" aria-label="View Collection">View Collection</button>
                </div>
            </div>
            <button class="slider-arrow prev" type="button" aria-label="Previous Slide">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button class="slider-arrow next" type="button" aria-label="Next Slide">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
            <div class="slider-nav"></div>
        </div>

        <!-- Collections Section -->
        <section class="collections">
            <div class="container">
                <h2 class="section-title text-center">OUR COLLECTIONS</h2>
                
                <!-- First Row -->
                <div class="row mb-5">
                    <!-- Wooden Beds -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/wooden-beds">
                                <img src="17.jpg" alt="Wooden Beds Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">WOODEN BEDS</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Upholstered Beds -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/upholstered-beds">
                                <img src="16.webp" alt="Upholstered Beds Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">UPHOLSTERED BEDS</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Study Tables -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/study-tables">
                                <img src="19.webp" alt="Study Tables Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">STUDY TABLES</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Sofas -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/sofas">
                                <img src="6.jpg" alt="Sofas Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">SOFAS</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Coffee Tables -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/coffee-tables">
                                <img src="4.png" alt="Coffee Tables Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">COFFEE TABLES</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Dining Tables -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/dining-tables">
                                <img src="3.jpg" alt="Dining Tables Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">DINING TABLES</h3>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row -->
                <div class="row">
                    <!-- Bookshelves -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/bookshelves">
                                <img src="12.webp" alt="Bookshelves Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">BOOKSHELVES</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Wall Mirrors -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/wall-mirrors">
                                <img src="11.webp" alt="Wall Mirrors Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">WALL MIRRORS</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Bar Stools -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/bar-stools">
                                <img src="19.webp" alt="Bar Stools Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">BAR STOOLS</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Canvas -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/canvas">
                                <img src="18.webp" alt="Canvas Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">CANVAS</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Bedside Tables -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/bedside-tables">
                                <img src="17.jpg" alt="Bedside Tables Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">BEDSIDE TABLES</h3>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Side & End Tables -->
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="collection-item">
                            <a href="/collections/side-end-tables">
                                <img src="16.webp" alt="Side & End Tables Collection" class="img-fluid">
                                <h3 class="collection-title text-center mt-3">SIDE & END TABLES</h3>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Add Bootstrap JS and its dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

    <style>
    .collections {
        padding: 60px 0;
        background-color: #fff;
    }

    .section-title {
        font-size: 2.5rem;
        margin-bottom: 40px;
        letter-spacing: 2px;
    }

    .collection-item {
        transition: transform 0.3s ease;
        margin-bottom: 30px;
    }

    .collection-item:hover {
        transform: translateY(-5px);
    }

    .collection-item img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }

    .collection-title {
        font-size: 1.2rem;
        font-weight: 500;
        margin-top: 15px;
        letter-spacing: 1px;
    }

    .collection-item a {
        text-decoration: none;
        color: #333;
    }

    @media (max-width: 768px) {
        .collection-item img {
            height: 250px;
        }
        
        .section-title {
            font-size: 2rem;
        }
    }
    </style>
</body>
</html>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Review</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            padding: 40px;
            background-color: #f5f5f5;
            min-height: 100vh;
            align-items: center;
            gap: 40px;
        }

        .review-content {
            flex: 1;
        }

        .wonderful {
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 14px;
            color: #333;
            margin-bottom: 20px;
        }

        .stars {
            color: #000;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .review-text {
            font-size: 24px;
            line-height: 1.4;
            color: #333;
            margin-bottom: 20px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .author {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .navigation {
            display: flex;
            gap: 10px;
        }

        .nav-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background-color: white;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.3s ease;
        }

        .nav-button:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .image-container {
            flex: 1;
        }

        .product-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px;
            }

            .review-text {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="review-content">
            <div class="wonderful">Wonderful !</div>
            <div class="stars">★★★★★</div>
            <div class="review-text">
                "WELL MADE AND PERFECT PLATFORM BED. IT WAS STRAIGHTFORWARD TO PUT TOGETHER AND PERFECT FOR OUR HOME!. THIS IS A SLEEK BED LIKE NONE OTHER."
            </div>
            <div class="author">— Prashant Gupta</div>
            <div class="navigation">
                <button class="nav-button" onclick="previousSlide()">←</button>
                <button class="nav-button" onclick="nextSlide()">→</button>
            </div>
        </div>
        <div class="image-container">
            <img src="15.webp" alt="Platform bed with white frame" class="product-image">
        </div>
    </div>

    <script>
        function previousSlide() {
            // Add functionality for previous slide
            console.log('Previous slide');
        }

        function nextSlide() {
            // Add functionality for next slide
            console.log('Next slide');
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Woodpecker Furniture</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            padding: 2rem;
        }

        .header h1 {
            font-size: 1.5rem;
            color: #333;
        }

        .carousel-container {
            width: 100%;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .carousel {
            display: flex;
            transition: transform 0.5s ease;
        }

        .carousel-item {
            min-width: 100%;
            height: 400px;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 1rem;
            cursor: pointer;
            border: none;
            z-index: 10;
        }

        .prev-btn {
            left: 0;
        }

        .next-btn {
            right: 0;
        }

        .services {
            display: flex;
            justify-content: space-around;
            padding: 2rem;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-item {
            text-align: center;
            width: 250px;
            margin: 1rem;
        }

        .service-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .service-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .service-desc {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }

        .live-chat {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: white;
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FOLLOW US</h1>
        <p>@WOODPECKER_FURNITURE</p>
    </div>

    <div class="carousel-container">
        <div class="carousel">

            <div class="carousel-item">
                <img src="11.webp" alt="Modern furniture display">
            </div>
            <div class="carousel-item">
                <img src="12.webp" alt="Living room setup">
            </div>
            <div class="carousel-item">
                <img src="13.webp" alt="Gaming desk setup">
            </div>
            <div class="carousel-item">
                <img src="14.webp" alt="Luxury sofa">
            </div>
        </div>
        <button class="carousel-btn prev-btn">❮</button>
        <button class="carousel-btn next-btn">❯</button>
    </div>

    <div class="services">
        <div class="service-item">
            <div class="service-icon">🚚</div>
            <div class="service-title">PAN INDIA FREE DELIVERY</div>
            <div class="service-desc">Get free delivery at your doorstep, regardless of the city or state you live in.</div>
        </div>
        <div class="service-item">
            <div class="service-icon">📞</div>
            <div class="service-title">RESPONSIVE SUPPORT</div>
            <div class="service-desc">Have queries, issues with product or need to raise a concern? We are just a click away!</div>
        </div>
       
        <div class="service-item">
            <div class="service-icon">🛡️</div>
            <div class="service-title">EASY EMI OPTIONS</div>
            <div class="service-desc">Love a product something but short on budget? Enjoy our NO COST EMI options on our collections.</div>
        </div>
    </div>

    

    <script>
        const carousel = document.querySelector('.carousel');
        const items = document.querySelectorAll('.carousel-item');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        let currentIndex = 0;

        function updateCarousel() {
            carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + items.length) % items.length;
            updateCarousel();
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % items.length;
            updateCarousel();
        });

        // Auto-slide every 5 seconds
        setInterval(() => {
            currentIndex = (currentIndex + 1) % items.length;
            updateCarousel();
        }, 5000);
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOODPECKER_FURNITURE</title>
    <style>
        /* Previous styles remain the same */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* ... (previous styles) ... */

        /* New Footer Styles */
        footer {
            background: #000;
            color: white;
            padding: 4rem 2rem;
            margin-top: 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }

        .footer-section p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            display: block;
            margin-bottom: 0.8rem;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: #ccc;
        }

        .footer-logo {
            max-width: 200px;
            margin-bottom: 1rem;
        }

        .contact-info {
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            color: white;
            font-size: 1.2rem;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .newsletter-form input {
            padding: 0.8rem;
            background: transparent;
            border: 1px solid white;
            color: white;
        }

        .newsletter-form button {
            padding: 0.8rem;
            background: white;
            color: black;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Previous content remains the same -->

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <img src="/api/placeholder/200/50" alt="Woodpecker" class="footer-logo">
                <p class="contact-info">
                    (+91) 6377701930<br>
                    support@woodpecker.com
                </p>
                <p>We are design and product obsessed.<br>
                Uncompromising in the style, quality and<br>
                performance of every product we create.</p>
            </div>

            <div class="footer-section">
                <h3>Information</h3>
                <a href="#">About us</a>
                <a href="#">Contact us</a>
                <a href="#">Blog</a>
                <a href="#">Terms of Service</a>
                <a href="#">Refund policy</a>
                <a href="#">Track Your Orders</a>
                <a href="#">Affiliate Login</a>
            </div>

            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Get a 5% discount on your first order.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email">
                    <button type="submit">Subscribe</button>
                </form>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">📱</a>
                    <a href="#" aria-label="Twitter">🐦</a>
                    <a href="#" aria-label="Instagram">📸</a>
                    <a href="#" aria-label="YouTube">📺</a>
                    <a href="#" aria-label="TikTok">🎵</a>
                </div>
            </div>
        </div>
    </footer>

    
</body>
</html>
