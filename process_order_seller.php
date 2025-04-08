<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Get form data
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $delivery_address = $_POST['delivery_address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    // Get product details
    $product_query = "SELECT * FROM seller_product WHERE id = ?";
    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Calculate total amount
    $total_amount = $product['price'] * $quantity;

    // Insert order into database
    $order_query = "INSERT INTO order_seller (user_id, product_id, quantity, total_amount, 
                    customer_name, email, phone, delivery_address, city, postal_code, 
                    payment_method, status, order_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $order_query);
    $status = ($payment_method === 'cod') ? 'pending' : 'completed';
    
    mysqli_stmt_bind_param($stmt, "iiidssssssss", 
        $user_id, $product_id, $quantity, $total_amount, 
        $customer_name, $email, $phone, $delivery_address, 
        $city, $postal_code, $payment_method, $status
    );
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

    // Update product stock
    $update_stock = "UPDATE seller_product SET stock = stock - ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_stock);
    mysqli_stmt_bind_param($stmt, "ii", $quantity, $product_id);
    mysqli_stmt_execute($stmt);

    // Commit transaction
    mysqli_commit($conn);

    // Store order details in session for confirmation page
    $_SESSION['order_details'] = [
        'order_id' => $order_id,
        'product_name' => $product['title'],
        'quantity' => $quantity,
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'delivery_address' => $delivery_address,
        'city' => $city,
        'postal_code' => $postal_code,
        'order_date' => date('F j, Y, g:i a'),
        'status' => ($payment_method === 'cod') ? 'Pending' : 'Completed'
    ];

    // Redirect to confirmation page for all orders
    header('Location: order_confirmation_seller.php');
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error processing order: " . $e->getMessage();
    header('Location: buy_now_seller.php?product_id=' . $product_id);
    exit();
}

// Add this new section to handle payment success callback
if (isset($_POST['payment_success']) && isset($_SESSION['pending_order'])) {
    try {
        mysqli_begin_transaction($conn);
        
        $pending_order = $_SESSION['pending_order'];
        
        // Insert the order now that payment is confirmed
        $order_query = "INSERT INTO order_seller (user_id, product_id, quantity, total_amount, 
                        customer_name, email, phone, delivery_address, city, postal_code, 
                        payment_method, status, order_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', NOW())";
        
        $stmt = mysqli_prepare($conn, $order_query);
        mysqli_stmt_bind_param($stmt, "iiidssssssss", 
            $_SESSION['user_id'],
            $pending_order['product_id'],
            $pending_order['quantity'],
            $pending_order['total_amount'],
            $pending_order['customer_name'],
            $pending_order['email'],
            $pending_order['phone'],
            $pending_order['delivery_address'],
            $pending_order['city'],
            $pending_order['postal_code'],
            $pending_order['payment_method']
        );
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        // Update product stock
        $update_stock = "UPDATE seller_product SET stock = stock - ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_stock);
        mysqli_stmt_bind_param($stmt, "ii", $pending_order['quantity'], $pending_order['product_id']);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);

        // Store order details in session for confirmation page
        $_SESSION['order_details'] = [
            'order_id' => $order_id,
            'customer_name' => $pending_order['customer_name'],
            'total_amount' => $pending_order['total_amount'],
            'payment_method' => $pending_order['payment_method'],
            'product_name' => $product['title'],
            'quantity' => $pending_order['quantity']
        ];

        // Clear pending order
        unset($_SESSION['pending_order']);

        // Redirect to confirmation page
        header('Location: order_confirmation_seller.php');
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error processing payment: " . $e->getMessage();
        header('Location: buy_now_seller.php?product_id=' . $pending_order['product_id']);
        exit();
    }
}
?> 