<?php
session_start();
include("db.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user email from session
$email = $_SESSION['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get all form data with proper escaping
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    
    // Basic validation
    if (empty($name) || empty($phone)) {
        $_SESSION['error'] = "Name and phone are required fields";
        header("Location: profile.php");
        exit();
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: profile.php");
        exit();
    }
    
    // Initialize profile_pic variable
    $profile_pic = $_SESSION['profile_pic'] ?? null;
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get file info
        $file_name = $_FILES['profile_pic']['name'];
        $file_tmp = $_FILES['profile_pic']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_extensions)) {
            // Generate unique filename
            $new_file_name = uniqid('profile_') . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $profile_pic = $new_file_name;
            } else {
                $_SESSION['error'] = "Failed to upload profile picture";
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed";
            header("Location: profile.php");
            exit();
        }
    }
    
    // Update user data
    $sql = "UPDATE users SET 
            name = ?,
            phone = ?,
            address = ?,
            dob = ?,
            state = ?,
            pincode = ?,
            city = ?,
            profile_pic = COALESCE(?, profile_pic)
            WHERE email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", 
        $name, 
        $phone, 
        $address, 
        $dob, 
        $state, 
        $pincode,
        $city,
        $profile_pic,
        $email
    );

    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['name'] = $name;
        $_SESSION['phone'] = $phone;
        $_SESSION['address'] = $address;
        $_SESSION['dob'] = $dob;
        $_SESSION['state'] = $state;
        $_SESSION['pincode'] = $pincode;
        $_SESSION['city'] = $city;

        if ($profile_pic) {
            $_SESSION['profile_pic'] = $profile_pic;
        }

        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    header("Location: profile.php");
    exit();
}

// If not POST request, redirect back to profile
header("Location: profile.php");
exit();