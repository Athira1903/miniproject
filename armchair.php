<?php
include("db.php");
session_start();

// Pagination settings
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build the base query
$query = "SELECT p.*, s.subcategory_name, c.category_name 
          FROM products p
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id
          JOIN categories c ON s.category_id = c.category_id";

// Handle filters
$where_conditions = [];
$params = [];
$types = '';

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "c.category_id = ?";
    $params[] = (int)$_GET['category'];
    $types .= 'i';
}

if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $where_conditions[] = "s.subcategory_id = ?";
    $params[] = (int)$_GET['subcategory'];
    $types .= 'i';
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where_conditions[] = "p.price >= ?";
    $params[] = (float)$_GET['min_price'];
    $types .= 'd';
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_conditions[] = "p.price <= ?";
    $params[] = (float)$_GET['max_price'];
    $types .= 'd';
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.product_description LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY p.created_at ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Add pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= 'ii';

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    die("❌ Error: " . mysqli_error($conn));
}

// Get total number of products for pagination
$count_query = "SELECT COUNT(*) as total FROM products p";
if (!empty($where_conditions)) {
    $count_query = "SELECT COUNT(*) as total 
                    FROM products p
                    JOIN subcategories s ON p.subcategory_id = s.subcategory_id
                    JOIN categories c ON s.category_id = c.category_id";
    $count_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$count_stmt = mysqli_prepare($conn, $count_query);
if ($count_stmt) {
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);

    if (!empty($params)) {
        mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    }

    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_products = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_products / $items_per_page);
} else {
    die("❌ Error: " . mysqli_error($conn));
}

if (isset($_GET['term'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['term']);
    
    $query = "SELECT p.*, s.subcategory_name, c.category_name 
              FROM products p
              JOIN subcategories s ON p.subcategory_id = s.subcategory_id
              JOIN categories c ON s.category_id = c.category_id
              WHERE p.product_name LIKE ? OR p.product_description LIKE ?
              LIMIT 10";
    
    $stmt = mysqli_prepare($conn, $query);
    $search_pattern = "%{$search_term}%";
    mysqli_stmt_bind_param($stmt, "ss", $search_pattern, $search_pattern);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = array(
            'product_id' => $row['product_id'],
            'product_name' => htmlspecialchars($row['product_name']),
            'price' => $row['price'],
            'image_url' => $row['image_url'],
            'category_name' => htmlspecialchars($row['category_name']),
            'subcategory_name' => htmlspecialchars($row['subcategory_name'])
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}

// Add this function at the top of the file after session_start()
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
    <title>Premium Furniture Collection - Woodpecker</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
        }
        
       
        
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-img-top {
            height: 250px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #3a3a3a;
        }
        
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .btn-cart {
            background-color: #212529;
            color: white;
            transition: background-color 0.3s;
        }
        
        .btn-cart:hover {
            background-color: #0d0f10;
            color: white;
        }
        
        .btn-buy {
            background-color: #28a745;
            color: white;
            transition: background-color 0.3s;
        }
        
        .btn-buy:hover {
            background-color: #218838;
            color: white;
        }
        
        .filters {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .pagination .page-item .page-link {
            color: #212529;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #212529;
            border-color: #212529;
        }
        
        .footer {
            background-color: #212529;
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        
        .footer h5 {
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer-links {
            list-style: none;
            padding-left: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        
        .social-icons a:hover {
            color: #adb5bd;
        }

        .top-banner {
            font-size: 14px;
            font-weight: 500;
        }

        .navbar-nav .nav-link {
            color: #333;
            font-weight: 500;
            padding: 1rem 1.5rem;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #5C4033;
        }

        .welcome-text {
            color: #333;
            font-size: 14px;
        }

        .icons a {
            color: #333;
            font-size: 18px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .icons a:hover {
            color: #5C4033;
        }

        .badge {
            font-size: 10px;
            padding: 4px 6px;
        }

        .search-container .form-control:focus {
            box-shadow: none;
            border-color: #5C4033;
        }

        .search-container .btn:hover {
            background-color: #f8f9fa;
        }

        .mega-menu {
            width: 300px;
            padding: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .category-item {
            position: relative;
            margin-bottom: 5px;
        }

        .category-header {
            padding: 8px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .category-header:hover {
            background-color: #f8f9fa;
        }

        .subcategories {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            width: 200px;
            background: white;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
            padding: 10px 0;
        }

        .category-item:hover .subcategories {
            display: block;
        }

        .subcategory-item {
            display: block;
            padding: 8px 15px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .subcategory-item:hover {
            background-color: #f8f9fa;
            color: #5C4033;
        }

        .arrow {
            font-size: 12px;
        }

        .search-container {
            position: relative;
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }

        .search-result-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 5px;
        }

        .search-result-info {
            flex-grow: 1;
        }

        .search-result-name {
            font-weight: 500;
            margin-bottom: 3px;
        }

        .search-result-price {
            color: #5C4033;
            font-weight: 600;
        }

        .no-results {
            padding: 15px;
            text-align: center;
            color: #666;
        }

        .user-actions .dropdown-toggle::after {
            display: none;
        }

        .user-actions .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 0.5rem;
        }

        .user-actions .dropdown-item {
            padding: 0.75rem 1.5rem;
            color: #333;
            transition: all 0.3s ease;
        }

        .user-actions .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #5C4033;
        }

        .user-actions .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid #eee;
        }

        .user-actions .nav-link {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .user-actions .nav-link:hover {
            color: #5C4033;
        }
    </style>
</head>
<body>

<!-- Top Banner -->
<div class="top-banner" style="background-color: #5C4033; color: white; text-align: center; padding: 8px 0;">
    Free shipping in India 
</div>

<!-- Main Header -->
<div class="container-fluid px-4 py-3 bg-white">
    <div class="row align-items-center">
        <!-- Logo -->
        <div class="col-md-2">
            <a href="index.php" class="navbar-brand">
                <img src="logo.png" alt="Woodpeckers" style="height: 50px;">
            </a>
        </div>
        
        <!-- Search Bar -->
        <div class="col-md-6">
            <div class="search-container">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search products..." 
                           style="border-radius: 25px 0 0 25px; border: 1px solid #ddd; padding: 10px 15px;">
                    <button class="btn" type="button" 
                            style="background: white; border: 1px solid #ddd; border-left: none; border-radius: 0 25px 25px 0;">
                        <i class="fas fa-search text-muted"></i>
                    </button>
                </div>
                <div id="searchResults" class="search-results-dropdown"></div>
            </div>
        </div>
        
        <!-- Welcome & Icons -->
        <div class="col-md-4">
            <div class="d-flex justify-content-end align-items-center">
                <div class="user-actions me-3">
                    <?php if (isset($_SESSION['email'])): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <span class="ms-1">My Account</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="user_orders.php"><i class="fas fa-shopping-bag me-2"></i>Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.html" class="nav-link">
                            <i class="fas fa-user"></i>
                            <span class="ms-1">Login</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="icons">
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
                                $user_id = $_SESSION['user_id'];
                                $count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
                                $count_stmt = mysqli_prepare($conn, $count_query);
                                mysqli_stmt_bind_param($count_stmt, "i", $user_id);
                                mysqli_stmt_execute($count_stmt);
                                $count_result = mysqli_stmt_get_result($count_stmt);
                                $total = mysqli_fetch_assoc($count_result);
                                echo $total['total'] ?? '0';
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Menu -->
<nav class="navbar navbar-expand-lg bg-white border-top border-bottom">
    <div class="container-fluid px-4">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Category
                    </a>
                    <div class="dropdown-menu mega-menu">
                        <?php
                        // Get categories
                        $categories_query = "SELECT * FROM categories ORDER BY category_name";
                        $categories_result = mysqli_query($conn, $categories_query);
                        
                        if (mysqli_num_rows($categories_result) > 0):
                            while ($category = mysqli_fetch_assoc($categories_result)): ?>
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
                                                <a href="armchair.php?category=<?php echo $category['category_id']; ?>&subcategory=<?php echo $subcategory['subcategory_id']; ?>" class="subcategory-item">
                                                    <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                                                </a>
                                            <?php endwhile;
                                        else: ?>
                                            <a href="#" class="subcategory-item">No subcategories found</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <div class="category-item">
                                <div class="category-header">No categories found</div>
                            </div>  
                        <?php endif; ?>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="new_arrivals.php">New Arrivals</a>
                </li>

            </ul>
        </div>
    </div>
</nav>




<!-- Main Content -->
<div class="container">
    <!-- Filters -->
    <div class="filters">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php
                    $cat_query = "SELECT * FROM categories ORDER BY category_name";
                    $cat_result = mysqli_query($conn, $cat_query);
                    while ($category = mysqli_fetch_assoc($cat_result)) {
                        $selected = (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : '';
                        echo '<option value="' . $category['category_id'] . '" ' . $selected . '>' . htmlspecialchars($category['category_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="subcategory" class="form-label">Subcategory</label>
                <select class="form-select" id="subcategory" name="subcategory" <?php echo !isset($_GET['category']) ? 'disabled' : ''; ?>>
                    <option value="">All Subcategories</option>
                    <?php
                    if (isset($_GET['category']) && !empty($_GET['category'])) {
                        $category_id = (int)$_GET['category'];
                        $sub_query = "SELECT * FROM subcategories WHERE category_id = ? ORDER BY subcategory_name";
                        $stmt = mysqli_prepare($conn, $sub_query);
                        mysqli_stmt_bind_param($stmt, "i", $category_id);
                        mysqli_stmt_execute($stmt);
                        $sub_result = mysqli_stmt_get_result($stmt);
                        
                        while ($subcategory = mysqli_fetch_assoc($sub_result)) {
                            $selected = (isset($_GET['subcategory']) && $_GET['subcategory'] == $subcategory['subcategory_id']) ? 'selected' : '';
                            echo '<option value="' . $subcategory['subcategory_id'] . '" ' . $selected . '>' . htmlspecialchars($subcategory['subcategory_name']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="min_price" class="form-label">Min Price</label>
                <input type="number" class="form-control" id="min_price" name="min_price" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>" placeholder="₹">
            </div>
            <div class="col-md-2">
                <label for="max_price" class="form-label">Max Price</label>
                <input type="number" class="form-control" id="max_price" name="max_price" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>" placeholder="₹">
            </div>
            <div class="col-md-2">
                <label for="sort" class="form-label">Sort By</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest</option>
                    <option value="price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card product-card h-100">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="stock-badge bg-success">In Stock</span>
                        <?php else: ?>
                            <span class="stock-badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                        
                        <!-- Update the product card's wishlist button HTML -->
                        <button class="btn position-absolute wishlist-btn" 
                                style="right: 10px; top: 45px; background: none; border: none; z-index: 2; padding: 5px;"
                                onclick="toggleWishlist(<?php echo $product['product_id']; ?>, event)">
                            <i class="far fa-heart" style="color: #ff4444; font-size: 1.2rem; filter: drop-shadow(0px 0px 1px white);"></i>
                        </button>
                        
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             data-bs-toggle="modal" 
                             data-bs-target="#productModal<?php echo $product['product_id']; ?>"
                             style="cursor: pointer;">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($product['subcategory_name']); ?></p>
                            <div class="price mb-3">₹<?php echo number_format($product['price'], 2); ?></div>
                            <span class="admin-assured">
                                                <i class="fas fa-check-circle text-primary" style="font-size: 1.2em; vertical-align: middle; margin-right: 4px;"></i>
                                                <span style="color: #007bff; font-weight: 500; font-size: 0.9em;">Admin Assured</span>
                                            </span>
                            
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <!-- Admin product form -->
                                <form class="mt-auto add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <input type="hidden" name="is_seller_product" value="0">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Quantity</span>
                                        <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                        <button type="submit" class="btn btn-cart">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                    </div>
                                </form>

                                <!-- Buy Now Button -->
                                <form action="buy_now.php" method="GET">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" class="btn btn-buy w-100">
                                        <i class="fas fa-bolt me-2"></i>Buy Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Product Modal -->
                <div class="modal fade" id="productModal<?php echo $product['product_id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $product['product_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="productModalLabel<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="mb-3">Product Details</h4>
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <th>Category</th>
                                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Subcategory</th>
                                                    <td><?php echo htmlspecialchars($product['subcategory_name']); ?></td>
                                                </tr>
                                                <?php if(isset($product['wood_type']) && !empty($product['wood_type'])): ?>
                                                <tr>
                                                    <th>Wood Type</th>
                                                    <td><?php echo htmlspecialchars($product['wood_type']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <th>Dimensions</th>
                                                    <td><?php echo htmlspecialchars($product['length']); ?> × <?php echo htmlspecialchars($product['width']); ?> × <?php echo htmlspecialchars($product['height']); ?> cm</td>
                                                </tr>
                                                <tr>
                                                    <th>Price</th>
                                                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Stock</th>
                                                    <td><?php echo htmlspecialchars($product['stock_quantity']); ?> units</td>
                                                </tr>
                                                <?php if(isset($product['created_at'])): ?>
                                                <tr>
                                                    <th>Added on</th>
                                                    <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        
                                        <h4 class="mt-4 mb-2">Description</h4>
                                        <p><?php echo nl2br(htmlspecialchars($product['product_description'])); ?></p>
                                        
                                        <div class="d-grid gap-2 mt-4">
                                            <?php if ($product['stock_quantity'] > 0): ?>
                                                <form class="mt-3 add-to-cart-form">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                    <input type="hidden" name="add_to_cart" value="1">
                                                    <input type="hidden" name="is_seller_product" value="0">
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">Quantity</span>
                                                        <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                                        <button type="submit" class="btn btn-cart">
                                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                                        </button>
                                                    </div>
                                                </form>
                                                
                                                <form action="buy_now.php" method="GET">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                    <button type="submit" class="btn btn-buy w-100">
                                                        <i class="fas fa-bolt me-2"></i>Buy Now
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No products found matching your criteria.
                </div>
                <a href="armchair.php" class="btn btn-dark mt-3">View All Products</a>
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
                            LEFT JOIN users u ON sp.user_id = u.id";
    $seller_products_result = mysqli_query($conn, $seller_products_query);
    
    if ($seller_products_result && mysqli_num_rows($seller_products_result) > 0): ?>
        <div class="col-12">
            <h3 class="section-title mb-4">Seller Products</h3>
        </div>
        <?php while ($product = mysqli_fetch_assoc($seller_products_result)): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="stock-badge bg-success">In Stock</span>
                    <?php else: ?>
                        <span class="stock-badge bg-danger">Out of Stock</span>
                    <?php endif; ?>
                    
                    <button class="btn position-absolute wishlist-btn" 
                            style="right: 10px; top: 45px; background: none; border: none; z-index: 2; padding: 5px;"
                            onclick="toggleWishlist(<?php echo $product['id']; ?>, event)">
                        <i class="far fa-heart" style="color: #ff4444; font-size: 1.2rem; filter: drop-shadow(0px 0px 1px white);"></i>
                    </button>
                    
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                         data-bs-toggle="modal" 
                         data-bs-target="#sellerProductModal<?php echo $product['id']; ?>"
                         style="cursor: pointer; height: 250px; object-fit: cover;">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($product['subcategory']); ?></p>
                        <div class="price mb-3">₹<?php echo number_format($product['price'], 2); ?></div>
                        <div class="seller-badge mb-3">
                            <i class="fas fa-check-circle text-primary" style="font-size: 1.2em; vertical-align: middle; margin-right: 4px;"></i>
                            <span style="color: #007bff; font-weight: 500; font-size: 0.9em;">Seller: <?php echo htmlspecialchars($product['seller_name']); ?></span>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <!-- Seller product form -->
                            <form class="mt-auto add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <input type="hidden" name="is_seller_product" value="1">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Quantity</span>
                                    <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="submit" class="btn btn-cart">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                </div>
                            </form>

                            <!-- Buy Now Button -->
                            <a href="buy_now_seller.php?product_id=<?php echo $product['id']; ?>" class="btn btn-buy w-100">
                                <i class="fas fa-bolt me-2"></i>Buy Now
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Seller Product Modal -->
            <div class="modal fade" id="sellerProductModal<?php echo $product['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-3">Product Details</h4>
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Category</th>
                                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Subcategory</th>
                                                <td><?php echo htmlspecialchars($product['subcategory']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Dimensions</th>
                                                <td><?php echo $product['width'] . 'x' . $product['height'] . 'x' . $product['depth'] . ' ' . $product['measurement_unit']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Price</th>
                                                <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Stock</th>
                                                <td><?php echo htmlspecialchars($product['stock']); ?> units</td>
                                            </tr>
                                            <tr>
                                                <th>Seller</th>
                                                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                            </tr>
                                            <?php if(isset($product['created_at'])): ?>
                                            <tr>
                                                <th>Added on</th>
                                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    
                                    <h4 class="mt-4 mb-2">Description</h4>
                                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <?php if ($product['stock'] > 0): ?>
                                            <form class="mt-3 add-to-cart-form">
                                                <?php
                                                $product_id = $product['id'];
                                                ?>
                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                <input type="hidden" name="add_to_cart" value="1">
                                                <input type="hidden" name="is_seller_product" value="1">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text">Quantity</span>
                                                    <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                                    <button type="submit" class="btn btn-cart">
                                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                                    </button>
                                                </div>
                                            </form>
                                            <a href="buy_now_seller.php?product_id=<?php echo $product_id; ?>" class="btn btn-buy w-100">
                                                <i class="fas fa-bolt me-2"></i>Buy Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="my-5">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price=' . $_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price=' . $_GET['max_price'] : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price=' . $_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price=' . $_GET['max_price'] : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price=' . $_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price=' . $_GET['max_price'] : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
     
        
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="small text-muted mb-0">&copy; 2023 Woodpecker. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="small text-muted mb-0">
                    <img src="images/payment-methods.png" alt="Payment Methods" height="24">
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const categorySelect = document.getElementById('category');
        const subcategorySelect = document.getElementById('subcategory');
        const filterInputs = filterForm.querySelectorAll('select, input');

        // Function to fetch subcategories
        function fetchSubcategories(categoryId) {
            subcategorySelect.disabled = !categoryId;
            subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';

            if (categoryId) {
                fetch(`get_subcategories.php?category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.subcategory_id;
                            option.textContent = subcategory.subcategory_name;
                            subcategorySelect.appendChild(option);
                        });
                        subcategorySelect.disabled = false;
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        // Handle category change
        categorySelect.addEventListener('change', function() {
            fetchSubcategories(this.value);
            applyFilters();
        });

        // Handle other filter changes
        filterInputs.forEach(input => {
            if (input !== categorySelect) { // Skip category select as it's handled above
                input.addEventListener('change', function() {
                    applyFilters();
                });

                // For price inputs, add input event listener with debounce
                if (input.type === 'number') {
                    let timeout;
                    input.addEventListener('input', function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            applyFilters();
                        }, 500); // Wait 500ms after user stops typing
                    });
                }
            }
        });

        function applyFilters() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();

            for (let [key, value] of formData.entries()) {
                if (value) { // Only add parameters that have values
                    params.append(key, value);
                }
            }

            // Preserve the page parameter if it exists
            const currentPage = new URLSearchParams(window.location.search).get('page');
            if (currentPage) {
                params.append('page', currentPage);
            }

            // Redirect to the same page with new filters
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();

            if (searchTerm.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSearchResults(searchTerm);
                }, 300);
            } else {
                searchResults.style.display = 'none';
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.style.display = 'none';
            }
        });

        function fetchSearchResults(searchTerm) {
            fetch(`search_products.php?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(error => console.error('Error:', error));
        }

        function displaySearchResults(results) {
            searchResults.innerHTML = '';
            
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="no-results">No products found</div>';
            } else {
                results.forEach(product => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <img src="${product.image_url}" alt="${product.product_name}">
                        <div class="search-result-info">
                            <div class="search-result-name">${product.product_name}</div>
                            <div class="search-result-price">₹${parseFloat(product.price).toLocaleString()}</div>
                        </div>
                    `;
                    resultItem.addEventListener('click', () => {
                        window.location.href = `armchair.php?product_id=${product.product_id}`;
                    });
                    searchResults.appendChild(resultItem);
                });
            }
            
            searchResults.style.display = 'block';
        }
    });

    function toggleWishlist(productId, event) {
        // Prevent the default button behavior
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Check if user is logged in
        <?php if (!isset($_SESSION['user_id'])): ?>
            window.location.href = 'login.html';
            return;
        <?php endif; ?>

        fetch('toggle_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Get the clicked button's heart icon
                const heartIcon = event.target.closest('button').querySelector('i');
                const wishlistCount = document.getElementById('wishlistCount');
                
                if (data.action === 'added') {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    wishlistCount.textContent = data.count;
                } else {
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                    wishlistCount.textContent = data.count;
                }
            } else {
                alert('Error updating wishlist. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating wishlist. Please try again.');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const addToCartForms = document.querySelectorAll('.add-to-cart-form');
        
        addToCartForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitButton = form.querySelector('button[type="submit"]');
                const quantityInput = form.querySelector('input[name="quantity"]');
                const productId = form.querySelector('input[name="product_id"]').value;
                const isSellerProduct = form.querySelector('input[name="is_seller_product"]').value;
                const quantity = parseInt(quantityInput.value);

                // Validate quantity
                if (quantity < 1) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a valid quantity',
                        icon: 'error',
                        confirmButtonColor: '#5C4033'
                    });
                    return;
                }

                // Show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';

                // Create FormData object
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                formData.append('add_to_cart', '1');
                formData.append('is_ajax', '1');
                formData.append('is_seller_product', isSellerProduct);

                // Send AJAX request
                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart count in header
                        const cartCount = document.getElementById('cartCount');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }

                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: 'Product added to cart successfully!',
                            icon: 'success',
                            confirmButtonColor: '#5C4033',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Failed to add product to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#5C4033'
                    });
                })
                .finally(() => {
                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
                });
            });
        });
    });
</script>
</body>
</html>
