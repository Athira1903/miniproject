<?php
include("db.php");

if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    
    $query = "SELECT * FROM tbl_orders WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($order = mysqli_fetch_assoc($result)) {
        echo json_encode($order);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'Invalid request']);
}

mysqli_close($conn);
?> 