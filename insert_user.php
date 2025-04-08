<?php
session_start();
header('Content-Type: application/json');

// Include database connection
include("db.php");

// Get JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    try {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        
        // Bind parameters
        $stmt->bind_param("ssss", 
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password']
        );

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User registered successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to register user']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data received'
    ]);
}

$conn->close();
?>
