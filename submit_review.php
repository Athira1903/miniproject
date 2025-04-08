<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['product_id']) || !isset($_POST['rating']) || !isset($_POST['review_text'])) {
    header('Location: user_orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$order_id = $_POST['order_id'];
$rating = $_POST['rating'];
$review_text = $_POST['review_text'];

try {
    $query = "INSERT INTO product_reviews (user_id, product_id, order_id, rating, review_text, created_at) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiiis", $user_id, $product_id, $order_id, $rating, $review_text);
    mysqli_stmt_execute($stmt);

    $_SESSION['success'] = "Review submitted successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "Error submitting review. Please try again.";
}

header("Location: order_details.php?id=" . $order_id);
exit();
?>
