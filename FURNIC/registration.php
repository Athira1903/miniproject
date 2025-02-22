<?php
include("db.php");
session_start();

// Define the PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Print the $_POST array
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    if (isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["password"])) {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Check if $pdo is defined
            if (!isset($pdo)) {
                throw new Exception("Database connection not established.");
            }

            $check_query = "SELECT * FROM users WHERE email=:email";
            $stmt = $pdo->prepare($check_query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Email already exists!";
            } else {
                $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $_SESSION['email'] = $email;
                    header("Location: login.html");
                    exit();
                } else {
                    $error = "Registration failed!";
                }
            }
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        } catch (Exception $e) {
            die($e->getMessage());
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Woodpecker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('4.png') no-repeat center center/cover;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 2.5rem;
            border-radius: 14px;
            text-align: center;
            color: white;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 1s ease-in-out;
        }

        h2 {
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 2.5rem 1rem 1rem;
            border: none;
            border-radius: 6px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 1rem;
        }

        .form-group input::placeholder {
            color: transparent;
        }

        .form-group label {
            position: absolute;
            top: 14px;
            left: 14px;
            color: #ddd;
            transition: all 0.3s ease-in-out;
            font-size: 1rem;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            top: -12px;
            left: 10px;
            font-size: 0.9rem;
            color: #5BC0DE;
            background: rgba(0, 0, 0, 0.6);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .form-group .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ddd;
        }

        .error-message {
            color: #ff4444;
            font-size: 12px;
            margin-top: 5px;
            text-align: left;
            display: block;
            padding-left: 5px;
        }

        .form-group.error input {
            border: 1px solid #ff4444;
        }

        .password-toggle {
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ddd;
        }

        button {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 8px;
            background: rgb(239, 86, 26);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease-in-out;
            font-size: 1rem;
        }

        button:hover {
            background: #4aaac7;
        }

        .error {
            color: red;
            margin-bottom: 1rem;
        }

        .login-link {
            margin-top: 1rem;
            font-size: 14px;
        }

        .google-btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            background: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #555;
            transition: 0.3s ease-in-out;
            margin-top: 10px;
            cursor: pointer;
        }

        .google-btn img {
            width: 20px;
            height: 20px;
        }

        .google-btn:hover {
            background-color: #f1f1f1;
        }

        .back-home {
            margin-top: 15px;
        }

        .back-home a {
            display: inline-block;
            padding: 10px 15px;
            background: rgb(243, 43, 33);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-home a:hover {
            background: rgb(238, 75, 16);
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST" action="" id="registerForm" novalidate>
            <div class="form-group">
                <input type="text" id="name" name="name" placeholder="Enter your name" required>
                <label for="name">Name</label>
                <i class="fas fa-user icon"></i>
                <span class="error-message" id="nameError"></span>
            </div>
            
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <label for="email">Email</label>
                <i class="fas fa-envelope icon"></i>
                <span class="error-message" id="emailError"></span>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <label for="password">Password</label>
                <i class="fas fa-lock icon"></i>
                <i class="far fa-eye password-toggle" id="togglePassword"></i>
                <span class="error-message" id="passwordError"></span>
            </div>
            
            <button type="submit">Register</button>
        </form>

        <div class="google-btn" id="googleSignup">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/512px-Google_%22G%22_Logo.svg.png" alt="Google Logo">
            Sign up with Google
        </div>

        <div class="login-link">
            Already have an account? <a href="login.html" style="color: #5BC0DE;">Login</a>
        </div>

        <div class="back-home">
            <a href="index.php">Back to Home</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            nameInput.addEventListener('input', function() {
                const nameError = document.getElementById('nameError');
                if (this.value.trim() === '') {
                    nameError.textContent = 'Name is required';
                    this.parentElement.classList.add('error');
                } else if (!/^[a-zA-Z\s]{2,}$/.test(this.value)) {
                    nameError.textContent = 'Name must contain at least 2 letters and only alphabets';
                    this.parentElement.classList.add('error');
                } else {
                    nameError.textContent = '';
                    this.parentElement.classList.remove('error');
                }
            });

            emailInput.addEventListener('input', function() {
                const emailError = document.getElementById('emailError');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (this.value.trim() === '') {
                    emailError.textContent = 'Email is required';
                    this.parentElement.classList.add('error');
                } else if (!emailPattern.test(this.value)) {
                    emailError.textContent = 'Please enter a valid email address';
                    this.parentElement.classList.add('error');
                } else {
                    emailError.textContent = '';
                    this.parentElement.classList.remove('error');
                }
            });

            passwordInput.addEventListener('input', function() {
                const passwordError = document.getElementById('passwordError');
                const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                
                if (this.value.trim() === '') {
                    passwordError.textContent = 'Password is required';
                    this.parentElement.classList.add('error');
                } else if (!strongPasswordPattern.test(this.value)) {
                    passwordError.textContent = 'Password must have 8+ characters with uppercase, lowercase, number, and special character';
                    this.parentElement.classList.add('error');
                } else {
                    passwordError.textContent = '';
                    this.parentElement.classList.remove('error');
                }
            });

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Trigger validation for all fields
                ['name', 'email', 'password'].forEach(field => {
                    const input = document.getElementById(field);
                    const event = new Event('input');
                    input.dispatchEvent(event);
                    
                    if (document.getElementById(`${field}Error`).textContent) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>