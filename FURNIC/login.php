<?php
include("db.php");
session_start();

// Get form data
$email = $_POST["email"];
$password = $_POST["password"];

// Store the email in session
$_SESSION["email"] = $email;

// Query to check user credentials and their role (admin check)
$userCheckQuery = "SELECT * FROM users WHERE email = '$email'";

$result = mysqli_query($conn, $userCheckQuery);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Debugging: Check if the user data is correct
    var_dump($user);

    // Verify the password (assuming it's stored as plain text; otherwise, use password_verify)
    if (password_verify($password, $user['password'])){
        // Debugging: Check if role is being checked correctly
        echo "User Role: " . $user['role'] . "<br>";

        // Check user roles and redirect accordingly
        if ($user['role'] == "admin") {
            // Redirect to the admin dashboard
            header("Location: adminDash.php");
            exit();
        } else if ($user['role'] == "seller") {
            // Redirect to the seller dashboard
            header("Location: sellerdashboard.php");
            exit();
        } else {
            // Regular user login, redirect to the regular dashboard
            header("Location: index.php");
            exit();
        }
    } else {
        echo '<script>alert("Email or password is incorrect."); window.location.href = "login.html";</script>';
        exit();
    }
} else {
    echo '<script>alert("Email or password is incorrect."); window.location.href = "login.html";</script>';
    exit();
}

// Close the database connection
mysqli_close($conn);
?>

