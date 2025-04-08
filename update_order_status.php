<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['seller_id']) || !isset($_GET['id']) || !isset($_GET['status'])) {
    header('Location: seller_orders.php');
    exit();
}

$order_id = $_GET['id'];
$status = $_GET['status'];
$seller_id = $_SESSION['seller_id'];

// Verify the order belongs to this seller's products
$verify_query = "SELECT os.id 
                 FROM order_seller os 
                 JOIN seller_product sp ON os.product_id = sp.id 
                 WHERE os.id = ? AND sp.seller_id = ?";

$stmt = mysqli_prepare($conn, $verify_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $update_query = "UPDATE order_seller SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    mysqli_stmt_execute($stmt);
    
    $_SESSION['success'] = "Order status updated successfully!";
} else {
    $_SESSION['error'] = "You don't have permission to update this order.";
}

header('Location: seller_orders.php');
exit();
?>