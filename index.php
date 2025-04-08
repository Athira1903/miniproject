<?php
session_start();

// Add database connection at the top of index.php
$host = "localhost";
$username = "root";
$password = ""; // Make sure this is correct (often empty by default)
$database = "woodpecker"; // Make sure this database exists

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your existing login processing code
if(isset($_POST['email']) && isset($_POST['password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    
    if($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            // User is now logged in, you can redirect or continue showing the index page
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}

// Check if user is logged in and fetch name from database
if (isset($_SESSION['email'])) {  // Changed from user_email to email to match login session
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

// Add these queries near the top of the file where other database queries are
$categories_for_products_query = "SELECT * FROM categories ORDER BY category_name LIMIT 3";
$categories_for_products_result = mysqli_query($conn, $categories_for_products_query);

function getWishlistCount($conn) {
    if (!isset($_SESSION['user_id'])) return 0;
    
    // First check if we have the count in session
    if (!isset($_SESSION['wishlist_count'])) {
        // If not in session, get it from database
        $user_id = $_SESSION['user_id'];
        $query = "SELECT COUNT(product_id) as count 
                  FROM wishlist 
                  WHERE user_id = ?";
                  
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        // Store the count in session
        $_SESSION['wishlist_count'] = $row['count'];
    }
    
    return $_SESSION['wishlist_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Woodpecker - Your one-stop shop for premium furniture. Free shipping across India!">
    <meta name="author" content="Woodpecker">
    <title>Woodpecker - Premium Furniture</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5D4037;
            --secondary-color: #A1887F;
            --accent-color: #D7CCC8;
            --text-color: #3E2723;
            --light-text: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Announcement Bar */
        .announcement-bar {
            background-color: var(--primary-color);
            color: var(--light-text);
            text-align: center;
            padding: 8px 0;
            font-size: 14px;
        }

        /* Header */
        .header {
            padding: 15px 0;
            border-bottom: 1px solid var(--accent-color);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            text-align: center;
        }

        .logo img {
            height: 90px;
        }

        /* Simplified Search */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 36px 8px 12px;
            border: 1px solid var(--accent-color);
            border-radius: 20px;
            outline: none;
        }

        .search-box i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        /* User Actions */
        .user-actions {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: flex-end;
        }

        .action-icon {
            color: var(--text-color);
            text-decoration: none;
            position: relative;
            font-size: 1.2rem;
        }

        .counter {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary-color);
            color: white;
            font-size: 12px;
            padding: 1px 5px;
            border-radius: 50%;
        }

        /* User dropdown */
        .user-account-container {
            position: relative;
        }

        .user-account {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            color: var(--text-color);
            text-decoration: none;
        }

        .welcome-text {
            margin-left: 5px;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid var(--accent-color);
            border-radius: 4px;
            padding: 10px 0;
            min-width: 120px;
            display: none;
            z-index: 100;
        }

        .user-dropdown a {
            display: block;
            padding: 8px 15px;
            color: var(--text-color);
            text-decoration: none;
        }

        .user-dropdown a:hover {
            background-color: var(--accent-color);
        }

        .dropdown-trigger:hover .user-dropdown {
            display: block;
        }

        /* Navigation */
        .nav-menu {
            display: flex;
            justify-content: center;
            padding: 15px 0;
            gap: 30px;
            flex-wrap: wrap;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: color 0.3s;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link i {
            font-size: 0.7rem;
        }

        /* Dropdown Menu */
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 4px;
            min-width: 250px;
            z-index: 100;
            display: none;
        }

        .nav-item:hover .dropdown {
            display: block;
        }

        .category-item {
            border-bottom: 1px solid var(--accent-color);
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-header {
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .category-header:hover {
            background-color: var(--accent-color);
        }

        .subcategories {
            display: none;
            padding: 5px 0;
            background-color: #f8f9fa;
        }

        .subcategory-item {
            display: block;
            padding: 8px 25px;
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .subcategory-item:hover {
            background-color: var(--accent-color);
        }

        /* Show subcategories when category is hovered or clicked */
        .category-item.active .subcategories {
            display: block;
        }

        .category-item.active .arrow {
            transform: rotate(90deg);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 70vh;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s ease;
            display: flex;
            align-items: center;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-content {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            margin-left: 10%;
        }

        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .hero-text {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .hero-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .hero-btn:hover {
            background-color: var(--secondary-color);
        }

        /* Collections Grid */
        .collections {
            padding: 60px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 600;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title::after {
            content: "";
            position: absolute;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .collection-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            transition: transform 0.3s;
            height: 300px;
            margin-bottom: 30px;
        }

        .collection-item:hover {
            transform: translateY(-5px);
        }

        .collection-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .collection-item:hover .collection-img {
            transform: scale(1.05);
        }

        .collection-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-image: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 20px;
            color: white;
            text-align: center;
        }

        .collection-title {
            font-size: 1.2rem;
            margin: 0;
            letter-spacing: 1px;
        }

        /* Features Section */
        .features {
            background-color: var(--accent-color);
            padding: 50px 0;
        }

        .feature-item {
            text-align: center;
            padding: 15px;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        /* Footer */
        footer {
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: 20px 0;
            text-align: center;
        }

        .social-links {
            margin-bottom: 15px;
        }

        .social-links a {
            color: var(--light-text);
            margin: 0 10px;
            font-size: 1.2rem;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .hero-section {
                height: 50vh;
            }
            
            .hero-content {
                margin-left: 5%;
                padding: 20px;
                max-width: 90%;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .collection-item {
                height: 200px;
            }
            
            .header-container {
                flex-wrap: wrap;
            }
            
            .logo {
                order: 1;
                width: 100%;
                text-align: center;
                margin-bottom: 25px;
            }
            
            .search-container {
                order: 3;
                width: 100%;
                margin-top: 15px;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .user-actions {
                order: 2;
                width: 100%;
                justify-content: center;
            }
        }

        .about-us {
            background-color: #f8f9fa;
        }

        .about-content {
            color: #333;
        }

        .feature-item {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .feature-item i {
            font-size: 1.2rem;
        }

        .about-us img {
            max-height: 500px;
            object-fit: cover;
            width: 100%;
        }

        @media (max-width: 768px) {
            .about-content {
                margin-top: 2rem;
                padding-left: 0 !important;
            }
        }

        .product-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }

        .product-popup.show {
            display: block;
        }

        .product-popup-content {
            position: relative;
            background-color: #fff;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            max-width: 1000px;
            border-radius: 8px;
        }

        .close-popup {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }

        .product-popup-image {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .category-text {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5C4033;
            margin-bottom: 20px;
        }

        .description {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .product-details {
            margin-bottom: 20px;
        }

        .product-details ul {
            list-style: none;
            padding: 0;
        }

        .product-details li {
            margin-bottom: 10px;
            color: #666;
        }

        .stock-info {
            margin-bottom: 20px;
        }

        .in-stock {
            color: #28a745;
        }

        .out-of-stock {
            color: #dc3545;
        }

        .collection-item {
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .product-popup-content {
                margin: 20px auto;
                padding: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
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
    <header class="header">
        <div class="container">
            <div class="header-container">
                <div class="logo">
                    <a href="index.php">
                        <img src="logo.png" alt="Woodpecker" onerror="this.src='https://via.placeholder.com/150x50?text=Woodpecker'">
                    </a>
                </div>
                
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" placeholder="Search products...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>  

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
                    <a href="wishlist.php" class="me-3 position-relative">
                        <i class="fas fa-heart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" id="wishlistCount">
                            <?php echo getWishlistCount($conn); ?>
                        </span>
                    </a>
                    <a href="cartdisplay.php" class="position-relative">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" id="cartCount">
                                <?php
                                if (isset($_SESSION['user_id'])) {
                                    $cart_user_id = $_SESSION['user_id'];
                                    $cart_count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
                                    $cart_count_stmt = mysqli_prepare($conn, $cart_count_query);
                                    mysqli_stmt_bind_param($cart_count_stmt, "i", $cart_user_id);
                                    mysqli_stmt_execute($cart_count_stmt);
                                    $cart_count_result = mysqli_stmt_get_result($cart_count_stmt);
                                    $cart_total = mysqli_fetch_assoc($cart_count_result);
                                    echo $cart_total['total'] ?? '0';
                                } else {
                                    echo '0';
                                }
                                ?>
                            </span>
                        </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
  <!-- Navigation -->
<nav class="nav-menu">
    <!-- <div class="nav-item">
        <a href="#" class="nav-link">
            Category <i class="fas fa-chevron-down"></i>
        </a>
        <div class="dropdown">
            <?php if (mysqli_num_rows($categories_result) > 0): ?>
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
                            
                            if ($subcategories_result && mysqli_num_rows($subcategories_result) > 0):
                                while ($subcategory = mysqli_fetch_assoc($subcategories_result)): ?>
                                    <a href="armchair.php?category_id=<?php echo $category['category_id']; ?>&subcategory_id=<?php echo $subcategory['subcategory_id']; ?>" class="subcategory-item">
                                        <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                                    </a>
                                <?php endwhile;
                            else: ?>
                                <a href="#" class="subcategory-item">No subcategories found</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="category-item">
                    <div class="category-header">No categories found</div>
                </div>  
            <?php endif; ?>
        </div>
    </div> -->

    <div class="nav-item">
        <a href="armchair.php" class="nav-link">New Arrivals</a>
    </div>
    
    <div class="nav-item">
        <a href="#about-us" class="nav-link">About Us</a>
    </div>
</nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-slide active" style="background-image: url('3.jpg');">
            <div class="hero-content">
                <h1 class="hero-title">Home Makeovers Made Easy</h1>
                <p class="hero-text">Find the perfect pieces to reflect your style and transform your space.</p>
                <a href="armchair.php" class="hero-btn">Shop Now</a>
            </div>
        </div>
        <div class="hero-slide" style="background-image: url('4.png');">
            <div class="hero-content">
                <h1 class="hero-title">Modern Comfort Designs</h1>
                <p class="hero-text">Transform your space with elegant furniture that combines style and comfort.</p>
                <a href="armchair.php" class="hero-btn">Explore Now</a>
            </div>
        </div>
        <div class="hero-slide" style="background-image: url('6.jpg');">
            <div class="hero-content">
                <h1 class="hero-title">Luxury Meets Functionality</h1>
                <p class="hero-text">Discover our premium collection of furniture designed for modern living.</p>
                <a href="armchair.php" class="hero-btn">View Collection</a>
            </div>
        </div>
    </section>
        <!-- About Us Section -->
        <section class="about-us py-5" id="about-us">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="19.webp" alt="About Woodpecker" class="img-fluid rounded shadow-lg">
                </div>
                <div class="col-md-6">
                    <div class="about-content ps-md-5">
                        <h2 class="mb-4">About Woodpecker</h2>
                        <p class="lead mb-4">Welcome to Woodpecker - Where Quality Meets Comfort</p>
                        <p class="mb-4">At Woodpecker, we believe that furniture is more than just functional pieces - it's about creating spaces that inspire and comfort. Since our establishment, we've been crafting premium furniture that combines traditional woodworking techniques with modern design sensibilities.</p>
                        <div class="features mb-4">
                            <div class="row g-4">
                                <div class="col-6">
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span class="ms-2">Premium Quality</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="feature-item">
                                        <i class="fas fa-truck text-success"></i>
                                        <span class="ms-2">Fast Delivery</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="feature-item">
                                        <i class="fas fa-tools text-success"></i>
                                        <span class="ms-2">Expert Craftsmanship</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="feature-item">
                                        <i class="fas fa-medal text-success"></i>
                                        <span class="ms-2">Best Materials</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mb-4">Our commitment to excellence ensures that each piece of furniture we create is not just beautiful but built to last. We source the finest materials and employ skilled craftsmen to bring your furniture dreams to life.</p>
                       
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section class="collections">
        <div class="container">
            <h2 class="section-title">OUR COLLECTIONS</h2>
            
            <div class="row">
                <?php if ($categories_for_products_result && mysqli_num_rows($categories_for_products_result) > 0): ?>
                    <?php while ($category = mysqli_fetch_assoc($categories_for_products_result)): 
                        // Fetch products for each category through subcategories
                        $products_query = "SELECT p.*, s.subcategory_name, c.category_name 
                                         FROM products p 
                                         JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
                                         JOIN categories c ON s.category_id = c.category_id 
                                         WHERE s.category_id = " . $category['category_id'] . " 
                                         LIMIT 2";
                        $products_result = mysqli_query($conn, $products_query);
                    ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <h3 class="category-title mb-3"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                            <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                                <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                    <div class="collection-item mb-3" onclick="showProductDetails(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                             class="collection-img"
                                             onerror="this.src='placeholder.jpg'">
                                        <div class="collection-overlay">
                                            <h4 class="collection-title"><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                            <p class="collection-price">₹<?php echo number_format($product['price'], 2); ?></p>
                                            <span class="admin-assured">
                                                <i class="fas fa-check-circle text-primary" style="font-size: 1.2em; vertical-align: middle; margin-right: 4px;"></i>
                                                <span style="color: #007bff; font-weight: 500; font-size: 0.9em;">Admin Assured</span>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                               
                            <?php else: ?>
                                <p>No products available in this category.</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No categories available at the moment.</p>
                    </div>
                <?php endif; ?>
            <!-- Seller Products Section -->
            <?php
            // Fetch seller products
            $seller_products_query = "SELECT sp.id, sp.title, sp.description, sp.price, sp.image,
                                           sp.subcategory, sp.width, sp.height, sp.depth,
                                           sp.measurement_unit, sp.stock, sp.category,
                                           sp.created_at, sp.user_id, u.name as seller_name
                                    FROM seller_product sp
                                    LEFT JOIN users u ON sp.user_id = u.id"; // Show all products with seller names
            $seller_products_result = mysqli_query($conn, $seller_products_query);
            
            if ($seller_products_result && mysqli_num_rows($seller_products_result) > 0): ?>
                <div class="col-12">
                </div>
                <?php while ($product = mysqli_fetch_assoc($seller_products_result)): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="collection-item mb-3" onclick="showProductDetails(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="collection-img"
                                 onerror="this.src='placeholder.jpg'">
                            <div class="collection-overlay">
                                <h4 class="collection-title"><?php echo htmlspecialchars($product['title']); ?></h4>
                                <p class="collection-price">₹<?php echo number_format($product['price'], 2); ?></p>
                                <span class="admin-assured">
                                    <span style="color: #007bff; font-weight: 500; font-size: 0.9em;">Seller: <?php echo htmlspecialchars($product['seller_name']); ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No seller products available at the moment.</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Free Shipping</h4>
                        <p>All across India</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>2 Year Warranty</h4>
                        <p>Guaranteed quality</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h4>Easy Returns</h4>
                        <p>30-day return policy</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p>Customer care available</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-pinterest"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
            <p>&copy; 2025 Woodpecker. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hero Slider
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.hero-slide');
            let currentSlide = 0;
            
            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }
            
            // Auto-change slides every 5 seconds
            setInterval(nextSlide, 5000);
            
            // Category dropdown interaction
            const categoryHeaders = document.querySelectorAll('.category-header');
            
            categoryHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const categoryItem = this.parentElement;
                    
                    // Toggle active class
                    if (categoryItem.classList.contains('active')) {
                        categoryItem.classList.remove('active');
                    } else {
                        // Remove active class from all items
                        document.querySelectorAll('.category-item').forEach(item => {
                            item.classList.remove('active');
                        });
                        
                        // Add active class to clicked item
                        categoryItem.classList.add('active');
                    }
                });
            });
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const section = document.querySelector(this.getAttribute('href'));
                if (section) {
                    section.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Close popup when clicking the close button or outside the popup
            document.querySelector('.close-popup').addEventListener('click', function() {
                hideProductPopup();
            });

            document.querySelector('.product-popup').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideProductPopup();
                }
            });
        });

        function addToCart(event, form) {
            event.preventDefault();
            
            const productId = form.querySelector('[name="product_id"]').value;
            const quantity = form.querySelector('[name="quantity"]').value || 1;
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    add_to_cart: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count if element exists
                    const cartCount = document.getElementById('cartCount');
                    if (cartCount) {
                        cartCount.textContent = data.cartCount;
                    }
                    
                    // Close modal if it exists
                    const modal = document.getElementById('productModal');
                    if (modal) {
                        const bootstrapModal = bootstrap.Modal.getInstance(modal);
                        if (bootstrapModal) {
                            bootstrapModal.hide();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
            
            return false;
        }

        function showProductDetails(product) {
            // Update popup content
            document.querySelector('.product-popup-image').src = product.image_url;
            document.querySelector('.product-popup-image').alt = product.product_name;
            document.getElementById('popupProductName').textContent = product.product_name;
            document.getElementById('popupPrice').textContent = '₹' + parseFloat(product.price).toLocaleString();
            document.getElementById('popupDescription').textContent = product.product_description;
            document.getElementById('popupProductId').value = product.product_id;
            
            // Update category text
            document.getElementById('popupCategory').textContent = `${product.category_name} / ${product.subcategory_name}`;
            
            // Update product details
            let detailsList = '';
            if (product.width) detailsList += `<li>Width: ${product.width} ${product.measurement_unit}</li>`;
            if (product.height) detailsList += `<li>Height: ${product.height} ${product.measurement_unit}</li>`;
            if (product.depth) detailsList += `<li>Depth: ${product.depth} ${product.measurement_unit}</li>`;
            if (product.wood_type) detailsList += `<li>Wood Type: ${product.wood_type}</li>`;
            document.getElementById('popupDetails').innerHTML = detailsList;
            
            // Update stock info
            const stockInfo = product.stock_quantity > 0 
                ? `<p class="in-stock">In Stock (${product.stock_quantity} available)</p>`
                : '<p class="out-of-stock">Out of Stock</p>';
            document.getElementById('popupStock').innerHTML = stockInfo;
            
            // Show popup
            document.getElementById('productPopup').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideProductPopup() {
            document.getElementById('productPopup').classList.remove('show');
            document.body.style.overflow = '';
        }

        function toggleWishlist(productId) {
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: productId,
                    toggle_wishlist: 1
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // If we're on the wishlist page, reload to show changes
                    if (window.location.pathname.includes('wishlist.php')) {
                        window.location.reload();
                    }
                    
                    // Update button text if it exists
                    const wishlistBtn = document.querySelector(`button[onclick*="toggleWishlist(${productId}"]`);
                    if (wishlistBtn) {
                        wishlistBtn.innerHTML = data.added ? 
                            '<i class="fas fa-heart"></i> Remove from Wishlist' : 
                            '<i class="far fa-heart"></i> Add to Wishlist';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>

    <!-- Add this HTML before closing body tag -->
    <div class="product-popup" id="productPopup">
        <div class="product-popup-content">
            <span class="close-popup">&times;</span>
            <div class="row">
                <div class="col-md-6">
                    <img src="" alt="" class="img-fluid product-popup-image">
                </div>
                <div class="col-md-6">
                    <h2 id="popupProductName"></h2>
                    <p class="category-text" id="popupCategory"></p>
                    <p class="price" id="popupPrice"></p>
                    <p class="description" id="popupDescription"></p>
                    
                    <div class="product-details">
                        <h4>Product Details</h4>
                        <ul id="popupDetails"></ul>
                    </div>
                    
                    <div class="stock-info" id="popupStock"></div>
                    
                    <div class="action-buttons">
                        <form onsubmit="return addToCart(event, this)" class="d-flex gap-2 w-100">
                            <input type="hidden" name="product_id" id="popupProductId">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary flex-grow-1">Add to Cart</button>
                            <button type="button" class="btn btn-outline-primary flex-grow-1" onclick="toggleWishlist(document.getElementById('popupProductId').value)">Add to Wishlist</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
</html>