<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$query = "SELECT 
            c.cart_id,
            c.quantity,
            c.is_seller_product,
            c.product_id,
            CASE 
                WHEN c.is_seller_product = 1 THEN sp.title
                ELSE p.product_name
            END as product_name,
            CASE 
                WHEN c.is_seller_product = 1 THEN sp.price
                ELSE p.price
            END as price,
            CASE 
                WHEN c.is_seller_product = 1 THEN sp.image
                ELSE p.image_url
            END as image
          FROM cart c
          LEFT JOIN products p ON c.product_id = p.product_id AND c.is_seller_product = 0
          LEFT JOIN seller_product sp ON c.product_id = sp.id AND c.is_seller_product = 1
          WHERE c.user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Calculate total
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
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
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                                <?php echo isset($_SESSION['wishlist_count']) ? $_SESSION['wishlist_count'] : '0'; ?>
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
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg bg-white border-top border-bottom">
        <div class="container-fluid px-4">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="armchair.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="new_arrivals.php">New Arrivals</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cart Content -->
    <div class="container mt-5">
        <h2>Your Shopping Cart</h2>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($result)): 
                            $item_total = $item['price'] * $item['quantity'];
                            $total += $item_total;
                        ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td>₹<?php echo number_format($item_total, 2); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <?php if ($item['is_seller_product'] == 1): ?>
                                            <a href="buy_now_seller.php?product_id=<?php echo $item['product_id']; ?>&cart_id=<?php echo $item['cart_id']; ?>" 
                                               class="btn btn-success buy-now">Buy Now</a>
                                        <?php else: ?>
                                            <a href="buy_now.php?product_id=<?php echo $item['product_id']; ?>&cart_id=<?php echo $item['cart_id']; ?>" 
                                               class="btn btn-success buy-now">Buy Now</a>
                                        <?php endif; ?>
                                        <button class="btn btn-danger remove-item" 
                                                data-cart-id="<?php echo $item['cart_id']; ?>">
                                            Remove
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2">₹<?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="armchair.php">Continue shopping</a>
            </div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Remove item
        $('.remove-item').click(function() {
            const cartId = $(this).data('cart-id');
            
            $.ajax({
                url: 'remove_from_cart.php',
                method: 'POST',
                data: {
                    cart_id: cartId
                },
                success: function(response) {
                    location.reload();
                }
            });
        });

        // Handle Buy Now click
        $('.buy-now').click(function(e) {
            e.preventDefault();
            const cartId = $(this).attr('href').split('cart_id=')[1];
            const buyNowUrl = $(this).attr('href');
            
            // Remove from cart first
            $.ajax({
                url: 'remove_from_cart.php',
                method: 'POST',
                data: {
                    cart_id: cartId
                },
                success: function(response) {
                    // Redirect to buy now page after removing from cart
                    window.location.href = buyNowUrl;
                }
            });
        });
    });
    </script>
</body>
</html>
