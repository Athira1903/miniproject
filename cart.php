<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');

require_once 'db.php';

$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0
];

try {
    // Check if this is an AJAX request
    if (!isset($_POST['is_ajax']) || $_POST['is_ajax'] != '1') {
        throw new Exception('Invalid request');
    }

    // Validate input
    if (!isset($_POST['add_to_cart']) || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        throw new Exception('Missing required parameters');
    }

    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $is_seller_product = isset($_POST['is_seller_product']) && $_POST['is_seller_product'] == '1';
    $user_id = $_SESSION['user_id'] ?? null;

    if ($quantity < 1) {
        throw new Exception('Invalid quantity');
    }

    if (!$user_id) {
        throw new Exception('User not logged in');
    }

    // Check if it's a seller product or admin product and query appropriate table
    if ($is_seller_product) {
        $query = "SELECT * FROM seller_product WHERE id = ? AND stock >= ?";
    } else {
        $query = "SELECT * FROM products WHERE product_id = ? AND stock_quantity >= ?";
    }

    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ii", $product_id, $quantity);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error executing query: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        throw new Exception('Product not available or insufficient stock');
    }

    // Check if product already exists in cart
    $check_cart_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND is_seller_product = ?";
    $check_stmt = mysqli_prepare($conn, $check_cart_query);
    mysqli_stmt_bind_param($check_stmt, "iii", $user_id, $product_id, $is_seller_product);
    mysqli_stmt_execute($check_stmt);
    $cart_result = mysqli_stmt_get_result($check_stmt);
    $existing_cart = mysqli_fetch_assoc($cart_result);

    if ($existing_cart) {
        // Update quantity if product exists
        $update_query = "UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ii", $quantity, $existing_cart['cart_id']);
        mysqli_stmt_execute($update_stmt);
    } else {
        // Insert new cart item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity, is_seller_product, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "iiii", $user_id, $product_id, $quantity, $is_seller_product);
        mysqli_stmt_execute($insert_stmt);
    }

    // Get total cart count
    $count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, "i", $user_id);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total = mysqli_fetch_assoc($count_result);

    $response['success'] = true;
    $response['message'] = 'Product added to cart successfully';
    $response['cart_count'] = $total['total'] ?? 0;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;