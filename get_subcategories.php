<?php
include("db.php");

if (isset($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    
    $query = "SELECT * FROM subcategories WHERE category_id = ? ORDER BY subcategory_name";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $subcategories = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $subcategories[] = array(
            'subcategory_id' => $row['subcategory_id'],
            'subcategory_name' => htmlspecialchars($row['subcategory_name'])
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode($subcategories);
    exit;
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Category ID is required']);
}

mysqli_close($conn);
?> 