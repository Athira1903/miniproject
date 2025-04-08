<?php
session_start();
require_once 'db.php';

// Check if order ID is provided
if (!isset($_GET['order_id']) || !isset($_GET['method'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['order_id'];
$payment_method = $_GET['method'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found. Please contact support with order ID: " . $order_id);
}

// Get product details based on product name
$product = null;
if (isset($order['product_name'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_name = ?");
    $stmt->bind_param("s", $order['product_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}

// Process payment based on method
$payment_processed = false;
$payment_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This is where you would integrate with a real payment gateway
    // For demonstration, we'll simulate a successful payment
    $payment_processed = true;
    
    if ($payment_processed) {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET status = 'Paid' WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
        
        // Redirect to confirmation page
        header("Location: order_confirmation.php?order_id=$order_id");
        exit;
    } else {
        $payment_error = "Payment processing failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - FURNI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .navbar {
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: #28a745 !important;
        }
        .nav-link {
            font-weight: 500;
            color: #343a40 !important;
            margin: 0 10px;
            transition: color 0.3s;
        }
        .nav-link:hover {
            color: #28a745 !important;
        }
        .payment-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
            padding: 40px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .payment-title {
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .btn-pay {
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            width: 100%;
            margin-top: 20px;
        }
        .btn-pay:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
        }
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .summary-total {
            font-weight: 700;
            font-size: 1.2rem;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
        }
        .product-image {
            max-width: 100px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .product-details {
            margin-bottom: 20px;
        }
        .shipping-info, .payment-info {
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #1a2a3a;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 500;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Simple navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="index.php">FURNI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="armchair.php">Armchairs</a></li>
                            <li><a class="dropdown-item" href="sofa.php">Sofas</a></li>
                            <li><a class="dropdown-item" href="table.php">Tables</a></li>
                            <li><a class="dropdown-item" href="chair.php">Chairs</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user"></i> Account
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container payment-container">
        <h1 class="payment-title">Complete Your Payment</h1>
        
        <?php if ($payment_error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $payment_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-7">
                <div class="order-summary">
                    <h3 class="mb-4">Order Summary</h3>
                    
                    <!-- Product Details -->
                    <div class="product-details">
                        <div class="row">
                            <div class="col-md-3">
                                <?php if ($product && isset($product['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>" class="product-image">
                                <?php else: ?>
                                    <div class="bg-light rounded p-3 text-center">
                                        <i class="fas fa-box text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h5><?php echo htmlspecialchars($order['product_name']); ?></h5>
                                <?php if ($product && isset($product['description'])): ?>
                                    <p class="text-muted small"><?php echo substr(htmlspecialchars($product['description']), 0, 100) . '...'; ?></p>
                                <?php endif; ?>
                                <div class="summary-item">
                                    <span>Unit Price:</span>
                                    <span>₹<?php echo number_format($order['total_price'] / $order['quantity'], 2); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span>Quantity:</span>
                                    <span><?php echo $order['quantity']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="shipping-info">
                        <h5 class="info-title">Shipping Information</h5>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span><?php echo isset($order['name']) ? htmlspecialchars($order['name']) : 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span><?php echo isset($order['delivery_address']) ? htmlspecialchars($order['delivery_address']) : 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">City:</span>
                            <span><?php echo isset($order['city']) ? htmlspecialchars($order['city']) : 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Postal Code:</span>
                            <span><?php echo isset($order['postal_code']) ? htmlspecialchars($order['postal_code']) : 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span><?php echo isset($order['phone']) ? htmlspecialchars($order['phone']) : 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span><?php echo isset($order['email']) ? htmlspecialchars($order['email']) : 'N/A'; ?></span>
                        </div>
                    </div>
                    
                    <!-- Order Information -->
                    <div class="payment-info">
                        <h5 class="info-title">Order Information</h5>
                        <div class="info-item">
                            <span class="info-label">Order ID:</span>
                            <span>#<?php echo $order_id; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Order Date:</span>
                            <span><?php echo isset($order['order_date']) ? date('F j, Y, g:i a', strtotime($order['order_date'])) : 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="badge bg-warning"><?php echo isset($order['status']) ? $order['status'] : 'N/A'; ?></span>
                        </div>
                    </div>
                    
                    <!-- Price Summary -->
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($order['total_price'], 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-item">
                        <span>Tax:</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="summary-item summary-total">
                        <span>Total Amount:</span>
                        <span>₹<?php echo number_format($order['total_price'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5">
                <?php if ($payment_method == 'razorpay'): ?>
                    <!-- Razorpay Payment Form -->
                    <h3 class="mb-4">Online Payment</h3>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i> You will be redirected to Razorpay to complete your payment securely.
                    </div>
                    <form method="post">
                        <button type="submit" class="btn btn-pay">
                            <i class="fas fa-lock me-2"></i> Pay ₹<?php echo number_format($order['total_price'], 2); ?> Securely
                        </button>
                    </form>
                <?php elseif ($payment_method == 'upi'): ?>
                    <!-- UPI Payment Form -->
                    <h3 class="mb-4">UPI Payment</h3>
                    <form method="post">
                        <div class="mb-3">
                            <label for="upi_id" class="form-label">UPI ID</label>
                            <input type="text" class="form-control" id="upi_id" name="upi_id" placeholder="yourname@upi" required>
                        </div>
                        <button type="submit" class="btn btn-pay">Pay ₹<?php echo number_format($order['total_price'], 2); ?></button>
                    </form>
                <?php elseif ($payment_method == 'card'): ?>
                    <!-- Credit/Debit Card Payment Form -->
                    <h3 class="mb-4">Card Payment</h3>
                    <form method="post">
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="card_holder" class="form-label">Card Holder Name</label>
                            <input type="text" class="form-control" id="card_holder" name="card_holder" placeholder="John Doe" required>
                        </div>
                        <button type="submit" class="btn btn-pay">Pay ₹<?php echo number_format($order['total_price'], 2); ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Simple footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> FURNI. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>