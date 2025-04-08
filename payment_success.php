<?php
include("db.php");
session_start();

// Check if payment ID is set
if (!isset($_GET['payment_id']) || !isset($_SESSION['razorpay_order'])) {
    header("Location: products.php");
    exit();
}

$payment_id = $_GET['payment_id'];
$order_info = $_SESSION['razorpay_order'];
$product_id = $order_info['product_id'];
$amount = $order_info['amount'] / 100; // Convert back from paise to rupees

// In a real application, you would verify the payment with Razorpay API
// For this example, we'll assume the payment is successful

// Create order in database
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Guest user if not logged in
$status = "PAID";
$created_at = date("Y-m-d H:i:s");

// Using tbl_orders instead of orders
$order_query = "INSERT INTO tbl_orders (user_id, payment_id, total_amount, status, created_at) 
                VALUES (?, ?, ?, ?, ?)";
$order_stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($order_stmt, "isdss", $user_id, $payment_id, $amount, $status, $created_at);
mysqli_stmt_execute($order_stmt);
$order_id = mysqli_insert_id($conn);

// Now we need to create the order_items table if it doesn't exist
// Let's create it with a consistent naming convention
if (!$conn->query("DESCRIBE tbl_order_items")) {
    $conn->query("CREATE TABLE tbl_order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT,
        product_id INT,
        quantity INT,
        price DECIMAL(10,2),
        FOREIGN KEY (order_id) REFERENCES tbl_orders(order_id)
    )");
}

// Add order items
$order_item_query = "INSERT INTO tbl_order_items (order_id, product_id, quantity, price) 
                     VALUES (?, ?, 1, ?)";
$item_stmt = mysqli_prepare($conn, $order_item_query);
mysqli_stmt_bind_param($item_stmt, "iid", $order_id, $product_id, $amount);
mysqli_stmt_execute($item_stmt);

// Update product stock
$update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - 1 
                       WHERE product_id = ? AND stock_quantity > 0";
$stock_stmt = mysqli_prepare($conn, $update_stock_query);
mysqli_stmt_bind_param($stock_stmt, "i", $product_id);
mysqli_stmt_execute($stock_stmt);

// Clear the order information from session
unset($_SESSION['razorpay_order']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Success - Woodpecker</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>Payment Successful!</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5>Thank you for your purchase!</h5>
                        <p>Your payment has been successfully processed.</p>
                        <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
                        <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($payment_id); ?></p>
                        <p><strong>Amount:</strong> ₹<?php echo number_format($amount, 2); ?></p>
                        <div class="d-grid gap-2 mt-4">
                            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                            <a href="index.php" class="btn btn-outline-primary">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>