<?php
include("db.php");
session_start();

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Generate random password
    $random_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (name, email, phone, password, role, status) 
              VALUES ('$name', '$email', '$phone', '$hashed_password', 'seller', 'active')";
    
    if (mysqli_query($conn, $query)) {
        // Send password email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'athirabiju185@gmail.com';
            $mail->Password = 'eesj okyk bnxf zttl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('athirabiju185@gmail.com', 'Woodpeckers');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "Your Seller Account Details - Woodpeckers";
            $mail->Body = "
                <div style='background-color: #f7f7f7; padding: 20px; font-family: Arial, sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h2 style='color: rgb(243, 43, 33); margin: 0;'>Woodpeckers</h2>
                        </div>
                        <p style='color: #333; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>Dear $name,</p>
                        <p style='color: #333; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>Your seller account has been created successfully. Here are your login credentials:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                            <p style='margin: 5px 0;'><strong>Email:</strong> $email</p>
                            <p style='margin: 5px 0;'><strong>Password:</strong> $random_password</p>
                        </div>
                        <p style='color: #666; font-size: 14px;'>Please change your password after your first login.</p>
                        <hr style='border: 1px solid #eee; margin: 30px 0;'>
                        <p style='color: #999; font-size: 12px; text-align: center;'>© 2024 Woodpeckers. All rights reserved.</p>
                    </div>
                </div>
            ";

            $mail->send();
            header("Location: manageseller.php");
            exit();
        } catch (Exception $e) {
            $error = "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Seller</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color:rgb(109, 125, 108);
            --accent-color: #28a745;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --text-color: #212529;
            --sidebar-width: 250px;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: #0a2246;
            color: white;
            padding: 20px;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            padding: 20px;
            flex-grow: 1;
        }

        .header {
            background: #0a2246;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn {
            border-radius: 20px;
            transition: background 0.3s, transform 0.3s;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: #0a2246;
            transform: translateY(-2px);
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(10, 34, 70, 0.25);
            border-color: #0a2246;
        }

        .nav-link.active {
            background: rgba(43, 220, 23, 0.2);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            .main-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <nav>
                <a href="adminDash.php" class="nav-link">Dashboard</a>
                <a href="productmang.php" class="nav-link">Product Management</a>
                <a href="admincat.php" class="nav-link">Category Management</a>
                <a href="manageseller.php" class="nav-link active">Seller Management</a>
                <a href="#" class="nav-link">Order Management</a>
                <a href="#" class="nav-link">Reports</a>
                <a href="#" class="nav-link">Settings</a>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Add New Seller</h1>
                <button class="btn btn-light" onclick="logout()">Logout</button>
            </div>

            <div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title mb-4">Seller Information</h3>
                                
                                <?php if (isset($error)) { ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php } ?>

                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-secondary" onclick="window.location.href='manageseller.php'">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Seller
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            window.location.href = 'login.html';
        }
    </script>
</body>
</html> 