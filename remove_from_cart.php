<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if cart_id is provided
if (!isset($_POST['cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'Cart ID not provided']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = $_POST['cart_id'];

// Verify that the cart item belongs to the user
$verify_query = "SELECT cart_id FROM cart WHERE cart_id = ? AND user_id = ?";
$verify_stmt = mysqli_prepare($conn, $verify_query);
mysqli_stmt_bind_param($verify_stmt, "ii", $cart_id, $user_id);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);

if (mysqli_num_rows($verify_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}

// Delete the item from cart
$delete_query = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "ii", $cart_id, $user_id);

if (mysqli_stmt_execute($delete_stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}

mysqli_stmt_close($verify_stmt);
mysqli_stmt_close($delete_stmt);
mysqli_close($conn);
?> 