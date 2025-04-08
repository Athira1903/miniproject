<?php
include("db.php");
session_start();


// Check if product_id is provided
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    header("Location: armchair.php");
    exit();
}

$product_id = (int)$_GET['product_id'];

// Fetch product details
$query = "SELECT p.*, s.subcategory_name, c.category_name 
          FROM products p
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id
          JOIN categories c ON s.category_id = c.category_id
          WHERE p.product_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: armchair.php");
    exit();
}

$product = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Woodpecker</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .product-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
            padding: 40px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .product-image {
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .product-image:hover {
            transform: scale(1.02);
        }
        .product-title {
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 20px;
            font-size: 2.2rem;
            line-height: 1.2;
        }
        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
        }
        .stock-badge {
            font-size: 0.9rem;
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .product-description {
            line-height: 1.8;
            color: #4a5568;
            margin: 25px 0;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            font-size: 1.05rem;
        }
        .btn-buy-now {
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }
        .btn-buy-now:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
        }
        .btn-add-cart {
            background-color: #343a40;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }
        .btn-add-cart:hover {
            background-color: #23272b;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 58, 64, 0.2);
        }
        .quantity-input {
            max-width: 120px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-weight: 600;
        }
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s;
        }
        .breadcrumb-item a:hover {
            color: #28a745;
        }
        .breadcrumb-item.active {
            color: #343a40;
            font-weight: 600;
        }
        .feature-icon {
            color: #28a745;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        .feature-item {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .feature-text {
            font-weight: 500;
        }
        .input-group-text {
            background-color: #f8f9fa;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .input-group-text:hover {
            background-color: #e9ecef;
        }
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1a2a3a;
        }
        .notify-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav aria-label="breadcrumb" class="mt-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php?category_id=<?php echo htmlspecialchars($products['category_id']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li class="breadcrumb-item"><a href="products.php?subcategory_id=<?php echo htmlspecialchars($products['subcategory_id']); ?>"><?php echo htmlspecialchars($product['subcategory_name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['product_name']); ?></li>
            </ol>
        </nav>

        <div class="product-container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="position-sticky" style="top: 2rem;">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid product-image" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-lg-6">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    
                    <div class="d-flex align-items-center mb-4">
                        <h2 class="product-price mb-0">₹<?php echo number_format($product['price'], 2); ?></h2>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="stock-badge ms-3 badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> In Stock
                            </span>
                        <?php else: ?>
                            <span class="stock-badge ms-3 badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <p class="text-muted">
                            <i class="fas fa-box feature-icon"></i> <?php echo $product['stock_quantity']; ?> units available
                        </p>
                    <?php endif; ?>

                    <div class="product-description">
                        <h5 class="section-title"><i class="fas fa-info-circle feature-icon"></i>Product Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($product['product_description'])); ?></p>
                    </div>

                    <div class="mb-4">
                        <h5 class="section-title mb-3">Product Features</h5>
                        <div class="feature-item">
                            <i class="fas fa-truck feature-icon"></i>
                            <span class="feature-text">Free shipping on orders over ₹1000</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-undo feature-icon"></i>
                            <span class="feature-text">30-day hassle-free return policy</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt feature-icon"></i>
                            <span class="feature-text">2-year manufacturer warranty</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle feature-icon"></i>
                            <span class="feature-text">Quality assured products</span>
                        </div>
                    </div>

                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="mt-4">
                            <div class="mb-4">
                                <label for="quantity" class="form-label fw-bold">Quantity:</label>
                                <div class="input-group" style="max-width: 150px;">
                                    <button type="button" class="btn btn-outline-secondary" id="decrease-qty">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center quantity-input" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                    <button type="button" class="btn btn-outline-secondary" id="increase-qty">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex mt-4">
                                <button type="button" id="buy-now-btn" class="btn btn-buy-now btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i> Buy Now
                                </button>
                                <button type="button" class="btn btn-add-cart btn-lg" onclick="addToCart(<?php echo $product_id; ?>)">
                                    <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                </button>
                            </div>
                        </div>

                        <!-- User Details Form (initially hidden) -->
                        <div id="user-details-form" class="mt-5" style="display: none;">
                            <h3 class="mb-4">Enter Your Details</h3>
                            <form action="process_order.php" method="post">
                              
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
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
                                
                                <button type="submit" class="btn btn-buy-now btn-lg w-100 mt-3">
                                    <i class="fas fa-check-circle me-2"></i> Place Order
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-times-circle me-2"></i> Out of Stock
                            </button>
                            <p class="mt-3 text-muted">
                                <i class="fas fa-info-circle me-2"></i> We're working on restocking this item. Please check back later or sign up for notifications.
                            </p>
                            <div class="input-group mt-3">
                                <input type="email" class="form-control" placeholder="Your email address">
                                <button class="btn btn-outline-secondary" type="button">Notify Me</button>
                            </div>
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
            "description": "Purchase of <?php echo addslashes($product['product_name']); ?> (Qty: " + quantity + ")",
            "image": "your-logo-url.png", // Replace with your logo URL
            "handler": function (response) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_order.php';
                
                // Add all necessary order fields
                var fields = {
                    'payment_id': response.razorpay_payment_id,
                    'product_id': '<?php echo $product_id; ?>',
                    'quantity': quantity,
                    'payment_method': 'razorpay',
                     'name': document.getElementById('name').value,  // Customer's name
                    'product_name': '<?php echo addslashes($product['product_name']); ?>', // Product name from database
                    'delivery_address': document.getElementById('address').value,
                    'city': document.getElementById('city').value,
                    'postal_code': document.getElementById('postal_code').value,
                    'total_price': (unitPrice * quantity),
                    'status': 'paid'
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
        if (value > <?php echo $product['stock_quantity']; ?>) {
            value = <?php echo $product['stock_quantity']; ?>;
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