<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $total_price = $_POST['total_price'];
        $delivery_address = $_POST['delivery_address'];
        $city = $_POST['city'];
        $postal_code = $_POST['postal_code'];
        $payment_method = $_POST['payment_method'];
        
        // Set payment ID and status based on payment method
        if ($payment_method == 'cod') {
            $payment_id = 'COD-' . time(); // Generate a COD payment ID
            $status = 'Pending'; // For COD, initial status is Pending
        } else {
            // For online payments (UPI/Card)
            $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null;
            $status = $payment_id ? 'Paid' : 'Payment Failed'; // If payment_id exists, mark as Paid
        }

        $order_date = date('Y-m-d H:i:s');

        // Get product name from products table
        $product_query = "SELECT product_name FROM products WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $product_query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $product_result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($product_result);

        // Insert order into database
        $order_query = "INSERT INTO orders 
                      (user_id, product_id, product_name, status, 
                      delivery_address, city, postal_code, quantity, 
                      total_price, payment_method, payment_id, order_date) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $order_query);
        mysqli_stmt_bind_param($stmt, "iisssssidsss", 
                            $_SESSION['user_id'],
                            $product_id, 
                            $product['product_name'],
                            $status,
                            $delivery_address, 
                            $city, 
                            $postal_code, 
                            $quantity,
                            $total_price, 
                            $payment_method, 
                            $payment_id, 
                            $order_date);

        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);
            // Redirect to order confirmation page with order ID
            header("Location: order_confirmation.php?order_id=" . $order_id);
            exit();
        } else {
            throw new Exception("Failed to place order");
        }

    } catch (Exception $e) {
        $_SESSION['order_error'] = "Order failed: " . $e->getMessage();
        header("Location: wishlist.php");
        exit();
    }
} else {
    header("Location: wishlist.php");
    exit();
}
?> 