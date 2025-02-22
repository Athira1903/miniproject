<?php
include("db.php");
session_start();
     // Start the session if not already started
     if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in and fetch name from database
    if (isset($_SESSION['email'])) {  // Changed from user_email to email to match login session
        // Create database connection using existing db.php
        include("db.php");  // Using the existing database connection
        
        // Prepare and execute query to fetch user's name
        $email = mysqli_real_escape_string($conn, $_SESSION['email']);
        $query = "SELECT name FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $welcome_text = '<span class="welcome-text">Welcome, ' . htmlspecialchars($row['name']) . '</span>';
        }
    }

    // Fetch Categories with ordering
    $categories_query = "SELECT * FROM categories ORDER BY category_name";
    $categories_result = mysqli_query($conn, $categories_query);
?>
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
    <style>
        /* Announcement Bar */
        .announcement-bar {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 8px 0;
            font-size: 14px;
        }

        /* Header Styles */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .left-header, .right-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 32px 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
        }

        .search-box i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .logo img {
            height: 50px;
        }

        .user-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .user-account, .wishlist-icon, .cart-icon {
            color: #333;
            text-decoration: none;
            position: relative;
        }

        .counter {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #000;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 50%;
        }

        /* Navigation Styles */
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
            min-width: 250px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            border-radius: 4px;
        }

        .nav-item:hover .dropdown {
            display: block;
        }

        .category-item {
            position: relative;
        }

        .category-header {
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
            transition: background-color 0.3s;
        }

        .category-header:hover {
            background-color: #f5f5f5;
        }

        .arrow {
            font-size: 12px;
            transition: transform 0.3s;
        }

        .subcategories {
            position: absolute;
            left: 100%;
            top: 0;
            background: white;
            min-width: 200px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            display: none;
            border-radius: 4px;
        }

        .subcategory-item {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .subcategory-item:hover {
            background-color: #f5f5f5;
        }

        .category-item:hover .subcategories {
            display: block;
        }

        .category-item:hover .arrow {
            transform: rotate(90deg);
        }

        /* User Account Dropdown */
        .user-account-container {
            position: relative;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 150px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
        }

        .user-account.dropdown-trigger:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
        }

        .welcome-text {
            font-size: 14px;
            color: #333;
            margin-left: 8px;
        }

        @media (max-width: 768px) {
            .welcome-text {
                display: none;
            }
            
            .search-box input {
                width: 150px;
            }
            
            .header-container {
                padding: 10px;
            }
            
            .nav-menu {
                gap: 1rem;
            }
        }
    </style>
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
                    <div class="user-account-container">
                        <?php if (isset($_SESSION['email'])): ?>
                            <div class="user-account dropdown-trigger">
                                <i class="fas fa-user"></i>
                                <?php echo isset($welcome_text) ? $welcome_text : ''; ?>
                                <div class="user-dropdown">
                                    <a href="profile.php">Profile</a>
                                    <a href="logout.php">Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.html" aria-label="Account" class="user-account">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                    </div>
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

    <!-- Navigation -->
    <nav class="nav-menu">
        <div class="nav-item">
            <a href="#" class="nav-link">Category</a>
            <div class="dropdown">
                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                    <div class="category-item">
                        <div class="category-header" data-category="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                            <span class="arrow">▶</span>
                        </div>
                        <div class="subcategories" id="subcategory-<?php echo $category['category_id']; ?>">
                            <?php
                            $subcategories_query = "SELECT * FROM subcategories WHERE category_id = " . $category['category_id'] . " ORDER BY subcategory_name";
                            $subcategories_result = mysqli_query($conn, $subcategories_query);
                            
                            while ($subcategory = mysqli_fetch_assoc($subcategories_result)): ?>
                                <a href="armchair.php" class="subcategory-item">
                                    <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Additional Navigation Items -->
        <div class="nav-item">
            <a href="#" class="nav-link">NEW ARRIVALS</a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">BEST SELLERS</a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">DEALS</a>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover delay to prevent accidental triggering
        const categoryItems = document.querySelectorAll('.category-item');
        let timeout;

        categoryItems.forEach(item => {
            const header = item.querySelector('.category-header');
            const subcategories = item.querySelector('.subcategories');

            item.addEventListener('mouseenter', () => {
                clearTimeout(timeout);
                // Hide all other subcategory menus
                document.querySelectorAll('.subcategories').forEach(menu => {
                    if (menu !== subcategories) {
                        menu.style.display = 'none';
                    }
                });
                subcategories.style.display = 'block';
            });

            item.addEventListener('mouseleave', () => {
                timeout = setTimeout(() => {
                    subcategories.style.display = 'none';
                }, 200);
            });
        });
    });
    </script>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Hero Slider Section -->
        <div class="hero-slider" aria-label="Image slider">
            <div class="slide active" style="background-image: url('3.jpg');" aria-label="Home Makeovers">
                <div class="slide-content">
                    <h2>Home Makeovers Made Easy</h2>
                    <p>Find the Perfect Pieces to Reflect Your Style</p>
                    <a href="login.html">
                        <button class="shop-now-btn" type="button" aria-label="Shop Now">Shop Now</button>
                    </a>
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
