<?php
header('Content-Type: application/json');
include("db.php");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email'])) {
    $name = mysqli_real_escape_string($conn, $data['name']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $uid = mysqli_real_escape_string($conn, $data['uid']);
    $photo = mysqli_real_escape_string($conn, $data['photo']);

    // Check if user already exists
    $check_query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['email'] = $email;
        echo json_encode(["success" => true, "message" => "User logged in"]);
    } else {
        // Register new user
        $query = "INSERT INTO users (name, email, password, google_uid, profile_pic) VALUES ('$name', '$email', '', '$uid', '$photo')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['email'] = $email;
            echo json_encode(["success" => true, "message" => "User registered and logged in"]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error"]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
