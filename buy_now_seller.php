<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if product_id is provided
if (!isset($_GET['product_id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['product_id'];
$user_id = $_SESSION['user_id'];

// Fetch product details
$product_query = "SELECT sp.*, u.name as seller_name 
                 FROM seller_product sp 
                 LEFT JOIN users u ON sp.user_id = u.id 
                 WHERE sp.id = ?";
$stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Check if product exists and is in stock
if (!$product || $product['stock'] <= 0) {
    $_SESSION['error'] = "Product is not available or out of stock.";
    header('Location: index.php');
    exit();
}

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'];
    $delivery_address = $_POST['delivery_address'];
    $phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    
    // Validate quantity
    if ($quantity <= 0 || $quantity > $product['stock']) {
        $_SESSION['error'] = "Invalid quantity selected.";
        header("Location: buy_now_seller.php?product_id=$product_id");
        exit();
    }

    // Calculate total amount
    $total_amount = $product['price'] * $quantity;

    // Store form data in session to prevent resubmission
    $_SESSION['order_data'] = [
        'quantity' => $quantity,
        'delivery_address' => $delivery_address,
        'phone' => $phone,
        'payment_method' => $payment_method,
        'product_id' => $product_id,
        'total_amount' => $total_amount,
        'customer_name' => $customer_name,
        'email' => $email,
        'city' => $city,
        'postal_code' => $postal_code
    ];

    // Redirect to process_order_seller.php
    header("Location: process_order_seller.php");
    exit();
}

// Clear any stored order data when loading the page normally
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset($_SESSION['order_data']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Woodpecker</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .product-container {
            padding: 2rem 0;
        }
        
        .product-image {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .product-title {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 1.8rem;
            color: #28a745;
        }
        
        .stock-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        
        .section-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .feature-icon {
            margin-right: 0.5rem;
            color: #28a745;
        }
        
        .feature-item {
            margin-bottom: 0.5rem;
        }
        
        .btn-buy-now {
            background-color: #28a745;
            color: white;
            padding: 0.75rem 2rem;
        }
        
        .btn-buy-now:hover {
            background-color: #218838;
            color: white;
        }
        
        .quantity-input {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav aria-label="breadcrumb" class="mt-4">
            
        </nav>

        <div class="product-container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="position-sticky" style="top: 2rem;">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid product-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-lg-6">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                    
                    <div class="d-flex align-items-center mb-4">
                        <h2 class="product-price mb-0">₹<?php echo number_format($product['price'], 2); ?></h2>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="stock-badge ms-3 badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> In Stock
                            </span>
                        <?php else: ?>
                            <span class="stock-badge ms-3 badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <p class="text-muted">
                            <i class="fas fa-box feature-icon"></i> <?php echo $product['stock']; ?> units available
                        </p>
                    <?php endif; ?>

                    <div class="product-description">
                        <h5 class="section-title"><i class="fas fa-info-circle feature-icon"></i>Product Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div class="mb-4">
                        <h5 class="section-title mb-3">Seller Information</h5>
                        <div class="feature-item">
                            <i class="fas fa-user feature-icon"></i>
                            <span class="feature-text">Sold by: <?php echo htmlspecialchars($product['seller_name']); ?></span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-truck feature-icon"></i>
                            <span class="feature-text">Free shipping on orders over ₹1000</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-undo feature-icon"></i>
                            <span class="feature-text">30-day hassle-free return policy</span>
                        </div>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <div class="mt-4">
                            <div class="mb-4">
                                <label for="quantity" class="form-label fw-bold">Quantity:</label>
                                <div class="input-group" style="max-width: 150px;">
                                    <button type="button" class="btn btn-outline-secondary" id="decrease-qty">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center quantity-input" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="button" class="btn btn-outline-secondary" id="increase-qty">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex mt-4">
                                <button type="button" id="buy-now-btn" class="btn btn-buy-now btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i> Buy Now
                                </button>
                            </div>
                        </div>

                        <!-- User Details Form (initially hidden) -->
                        <div id="user-details-form" class="mt-5" style="display: none;">
                            <h3 class="mb-4">Enter Your Details</h3>
                            <form action="process_order_seller.php" method="post" id="orderForm">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['title']); ?>">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" id="form-quantity" name="quantity" value="1">
                                <input type="hidden" name="total_price" value="<?php echo $product['price']; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="customer_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="cod">Cash on Delivery</option>
                                            <option value="upi">UPI</option>
                                            <option value="card">Credit/Debit Card</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Delivery Address</label>
                                    <textarea class="form-control" id="address" name="delivery_address" rows="3" required></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                    </div>
                                </div>
                                
                                <input type="submit" class="btn btn-buy-now btn-lg w-100 mt-3" value="Place Order">
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function payWithRazorpay() {
        // Get the quantity from the input field
        var quantity = document.getElementById('quantity').value;
        
        // Calculate total amount based on quantity
        var unitPrice = <?php echo $product['price']; ?>;
        var totalAmount = unitPrice * quantity * 100; // Convert to paise for Razorpay
        
        var options = {
            "key": "rzp_test_VibklLPN7XR1MG", 
            "amount": totalAmount,
            "currency": "INR",
            "name": "Woodpecker",
            "description": "Purchase of <?php echo addslashes($product['title']); ?> (Qty: " + quantity + ")",
            "image": "your-logo-url.png", // Replace with your logo URL
            "handler": function (response) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_order_seller.php';
                
                // Add all necessary order fields
                var fields = {
                    'payment_id': response.razorpay_payment_id,
                    'product_id': '<?php echo $product_id; ?>',
                    'quantity': quantity,
                    'payment_method': 'razorpay',
                    'customer_name': document.getElementById('name').value,
                    'product_name': '<?php echo addslashes($product['title']); ?>',
                    'delivery_address': document.getElementById('address').value,
                    'city': document.getElementById('city').value,
                    'postal_code': document.getElementById('postal_code').value,
                    'total_price': (unitPrice * quantity),
                    'status': 'paid',
                    'email': document.getElementById('email').value,
                    'phone': document.getElementById('phone').value
                };
                
                // Create hidden inputs for all fields
                for (var key in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            },
            "prefill": {
                "name": document.getElementById('name').value || "",
                "email": document.getElementById('email').value || "",
                "contact": document.getElementById('phone').value || ""
            },
            "theme": {
                "color": "#28a745"
            }
        };
        
        var rzp = new Razorpay(options);
        rzp.open();
    }

    // Quantity increment/decrement functionality
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.querySelector('input[name="quantity"]');
        const minusBtn = document.querySelector('.fa-minus').parentElement;
        const plusBtn = document.querySelector('.fa-plus').parentElement;
        
        minusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            const maxValue = parseInt(quantityInput.getAttribute('max'));
            if (currentValue < maxValue) {
                quantityInput.value = currentValue + 1;
            }
        });
    });

    document.getElementById('increase-qty').addEventListener('click', function() {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value, 10);
        value = isNaN(value) ? 1 : value;
        value++;
        if (value > <?php echo $product['stock']; ?>) {
            value = <?php echo $product['stock']; ?>;
        }
        input.value = value;
        document.getElementById('form-quantity').value = value;
    });
    
    document.getElementById('decrease-qty').addEventListener('click', function() {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value, 10);
        value = isNaN(value) ? 1 : value;
        value--;
        if (value < 1) {
            value = 1;
        }
        input.value = value;
        document.getElementById('form-quantity').value = value;
    });
    
    document.getElementById('buy-now-btn').addEventListener('click', function() {
        console.log("Place order button clicked");
        var paymentMethod = document.getElementById('payment_method').value;
        console.log("Payment method:", paymentMethod);
        
        // Update the quantity in the form
        document.getElementById('form-quantity').value = document.getElementById('quantity').value;
        
        // Show the user details form
        document.getElementById('user-details-form').style.display = 'block';
        
        // Scroll to the form
        document.getElementById('user-details-form').scrollIntoView({
            behavior: 'smooth'
        });
    });
    
    // Add event listener for the form submission
    document.querySelector('#user-details-form form').addEventListener('submit', function(e) {
        // Check if payment method is UPI or card
        var paymentMethod = document.getElementById('payment_method').value;
        if (paymentMethod === 'upi' || paymentMethod === 'card') {
            e.preventDefault(); // Prevent default form submission
            payWithRazorpay(); // Open Razorpay payment
        }
        // For COD, let the form submit normally
    });
    </script>
</body>
</html> 
