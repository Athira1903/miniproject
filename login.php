<?php
session_start();

// Add database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "woodpecker";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['email']) && isset($_POST['password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Use prepared statement for security
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password'])) {
            // Store all user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['dob'] = $user['dob'];
            $_SESSION['state'] = $user['state'];
            $_SESSION['city'] = $user['city'];
            $_SESSION['pincode'] = $user['pincode'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Redirect based on user role
            if($user['role'] == 'admin') {
                header("Location: adminDash.php");
                exit();
            } else if($user['role'] == 'seller') {
                header("Location: sellerdashboard.php");
                exit();
            } else {
                // Regular customer
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid password";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: login.php");
        exit();
    }
    $stmt->close();
}

$conn->close();
?>

