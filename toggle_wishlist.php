<?php
session_start();
include("db.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $product_id = (int)$data['product_id'];
    $user_id = $_SESSION['user_id'];

    // Check if product exists in wishlist
    $check_query = "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Remove from wishlist
        $query = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $action = 'removed';
    } else {
        // Add to wishlist
        $query = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $action = 'added';
    }

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    $success = mysqli_stmt_execute($stmt);

    // Get updated count
    $count_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($count_result)['count'];

    // Update session wishlist count
    $_SESSION['wishlist_count'] = $count;

    echo json_encode([
        'success' => true,
        'action' => $action,
        'count' => $count
    ]);

} catch (Exception $e) {
    error_log("Wishlist error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 