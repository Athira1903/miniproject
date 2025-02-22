<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set timezone explicitly

include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_GET['token'];

    // Basic validation
    if (empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Fetch token details for debugging
        $stmt = $conn->prepare("SELECT reset_token, token_expiry FROM users WHERE reset_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $token_expiry = new DateTime($row['token_expiry']);
            $current_time = new DateTime();

            // Check if the token is still valid
            if ($current_time > $token_expiry) {
                $error = "Invalid or expired token.";
            } else {
                // Token is valid, update password
                $new_password = password_hash($password, PASSWORD_BCRYPT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
                $update_stmt->bind_param("ss", $new_password, $token);
                $update_stmt->execute();

                $success = "Password has been reset successfully.";
            }
        } else {
            $error = "Invalid or expired token.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Woodpecker</title>
    <style>
        /* Full-page background with an image and overlay */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('5.jpg') no-repeat center center/cover;
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dark overlay to improve readability */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Adjust transparency as needed */
            z-index: 0;
        }

        /* Centering container with glassmorphism */
        .reset-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        /* Glass effect card */
        .reset-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            color: white;
        }

        /* Title */
        .reset-card h2 {
            font-size: 26px;
            margin-bottom: 15px;
        }

        /* Input fields */
        .reset-card input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.3);
            color: white;
            outline: none;
        }

        /* Placeholder styling */
        .reset-card input::placeholder {
            color: white;
            opacity: 0.8;
        }

        /* Button styling */
        .reset-card button {
            width: 100%;
            padding: 12px;
            background: #ff471a;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            color: white;
            transition: 0.3s ease-in-out;
        }

        .reset-card button:hover {
            background:rgb(14, 4, 2);
        }

        /* Links */
        .reset-card p {
            margin: 15px 0;
            font-size: 14px;
        }

        .reset-card a {
            color: white;
            font-weight: bold;
            text-decoration: none;
        }

        .reset-card a:hover {
            text-decoration: underline;
        }

        /* Messages */
        .error {
            color:rgb(42, 3, 3);
            margin-bottom: 10px;
        }

        .success {
            color: #33cc33;
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .reset-container {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <h2>Reset Your Password</h2>

            <?php if (!empty($error)) { ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php } ?>

            <?php if (!empty($success)) { ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php } ?>

            <form action="" method="POST">
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Reset Password</button>
            </form>

            <p>Remembered your password? <a href="login.php">Log In</a></p>
        </div>
    </div>
</body>
</html>
