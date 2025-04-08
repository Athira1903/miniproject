<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woodpecker";
                // Send OTP email using PHPMailer
                require 'PHPMailer/src/Exception.php';
                require 'PHPMailer/src/PHPMailer.php';
                require 'PHPMailer/src/SMTP.php';
                
                use PHPMailer\PHPMailer\PHPMailer;
                use PHPMailer\PHPMailer\Exception;


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

include("db.php");
session_start();

// Check if database variables exist, otherwise set defaults
if (!isset($host) || !isset($username) || !isset($password) || !isset($dbname)) {
    // Try to access variables with different naming conventions that might be in db.php
    $host = $host ?? $db_host ?? $dbhost ?? 'localhost';
    $username = $username ?? $db_username ?? $dbuser ?? $db_user ?? 'root';
    $password = $password ?? $db_password ?? $dbpass ?? $db_pass ?? '';
    $dbname = $dbname ?? $db_name ?? $database ?? 'furni_db';
}

// Replace PDO connection with mysqli connection
$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"] ?? '';
    $email = $_POST["email"] ?? '';
    $phone = $_POST["phone"] ?? '';
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    // Validate phone number format
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $error = "Invalid phone number. Must be 10 digits and start with 6, 7, 8, or 9.";
    }
    // Check if passwords match
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email already exists!";
            } else {
                // Generate random 5 digit OTP
                $otp = rand(10000, 99999);
                $_SESSION['email_otp'] = $otp;
                $_SESSION['otp_time'] = time(); // Store OTP generation time
                $_SESSION['registration_data'] = [
                    'name' => $name,
                    'email' => $email, 
                    'phone' => $phone,
                    'password' => $hashed_password
                ];

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

                    // Email content with professional styling
                    $mail->isHTML(true);
                    $mail->Subject = "Email Verification - Woodpeckers";
                    $mail->Body = "
                        <div style='background-color: #f7f7f7; padding: 20px; font-family: Arial, sans-serif;'>
                            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                                <div style='text-align: center; margin-bottom: 30px;'>
                                    <h2 style='color: rgb(243, 43, 33); margin: 0;'>Woodpeckers</h2>
                                </div>
                                <p style='color: #333; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>Dear User,</p>
                                <p style='color: #333; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>Thank you for registering with Woodpeckers. Please use the following OTP to verify your email address:</p>
                                <div style='background-color: rgb(243, 43, 33); color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0;'>
                                    $otp
                                </div>
                                <p style='color: #666; font-size: 14px; margin-top: 20px;'>This OTP will expire in 5 minutes.</p>
                                <p style='color: #666; font-size: 14px;'>If you didn't request this verification, please ignore this email.</p>
                                <hr style='border: 1px solid #eee; margin: 30px 0;'>
                                <p style='color: #999; font-size: 12px; text-align: center;'>© 2024 Woodpeckers. All rights reserved.</p>
                            </div>
                        </div>
                    ";

                    if($mail->send()) {
                        // Show OTP verification modal
                        ?>
                        <div class="modal" id="otpModal" tabindex="-1" role="dialog" style="display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
                            <div class="modal-dialog" role="document" style="position: relative; width: 90%; max-width: 400px; margin: 50px auto; animation: modalSlideDown 0.3s ease-out;">
                                <div class="modal-content" style="background: linear-gradient(135deg, rgb(243, 43, 33), rgb(238, 75, 16)); border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: none;">
                                    <div class="modal-header" style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; border-radius: 15px 15px 0 0;">
                                        <h5 class="modal-title" style="color: white; font-size: 24px; margin: 0; font-weight: 600;">Email Verification</h5>
                                    </div>
                                    <div class="modal-body" style="padding: 30px;">
                                        <div style="text-align: center; margin-bottom: 25px;">
                                            <i class="fas fa-envelope-open-text" style="font-size: 50px; color: #ffffff; margin-bottom: 15px;"></i>
                                            <p style="color: #ffffff; font-size: 16px; margin: 10px 0;">We've sent an OTP to your email</p>
                                            <p style="color: rgba(255,255,255,0.7); font-size: 14px;"><?php echo substr($email, 0, 3) . '****' . substr($email, strpos($email, '@')); ?></p>
                                        </div>
                                        <div style="display: flex; justify-content: center; margin: 20px 0;">
                                            <input type="text" id="otp" class="form-control" placeholder="Enter OTP" style="width: 200px; padding: 12px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); border-radius: 8px; text-align: center; font-size: 18px; letter-spacing: 5px; color: white;">
                                        </div>
                                        <div id="timer" style="text-align: center; color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 10px;"></div>
                                    </div>
                                    <div class="modal-footer" style="border-top: none; padding: 20px; text-align: center;">
                                        <button type="button" class="btn btn-primary" onclick="verifyOTP()" style="background: #ffffff; border: none; padding: 12px 30px; border-radius: 25px; color: rgb(243, 43, 33); font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                            Verify OTP
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                        @keyframes modalSlideDown {
                            from {
                                transform: translateY(-100px);
                                opacity: 0;
                            }
                            to {
                                transform: translateY(0);
                                opacity: 1;
                            }
                        }
                        </style>

                        <script>
                        // Start timer when modal shows
                        let timeLeft = 300;
                        const timerInterval = setInterval(() => {
                            const minutes = Math.floor(timeLeft / 60);
                            const seconds = timeLeft % 60;
                            document.getElementById('timer').innerHTML = 
                                `Time remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                            
                            if (timeLeft <= 0) {
                                clearInterval(timerInterval);
                                document.getElementById('timer').innerHTML = '<span style="color: #ff6b6b;">OTP Expired</span>';
                            }
                            timeLeft--;
                        }, 1000);

                        // Custom styled alert
                        function showCustomAlert(message) {
                            const alertDiv = document.createElement('div');
                            alertDiv.style.cssText = `
                                position: fixed;
                                top: 20px;
                                left: 50%;
                                transform: translateX(-50%);
                                background: linear-gradient(135deg, rgb(255, 140, 134), rgb(255, 68, 0));
                                color: white;
                                padding: 15px 25px;
                                border-radius: 8px;
                                box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15);
                                z-index: 10000;
                                font-family: Arial, sans-serif;
                                font-size: 16px;
                                animation: slideDown 0.3s ease-out;
                            `;
                            alertDiv.innerHTML = message;
                            document.body.appendChild(alertDiv);

                            // Add animation keyframes
                            const style = document.createElement('style');
                            style.textContent = `
                                @keyframes slideDown {
                                    from {
                                        transform: translate(-50%, -100%);
                                        opacity: 0;
                                    }
                                    to {
                                        transform: translate(-50%, 0);
                                        opacity: 1;
                                    }
                                }
                            `;
                            document.head.appendChild(style);

                            // Remove alert after 3 seconds
                            setTimeout(() => {
                                alertDiv.style.animation = 'slideUp 0.3s ease-out';
                                alertDiv.addEventListener('animationend', () => {
                                    document.body.removeChild(alertDiv);
                                });
                            }, 3000);
                        }

                        function verifyOTP() {
                            const enteredOTP = document.getElementById('otp').value;
                            const currentTime = Math.floor(Date.now() / 1000);
                            
                            if(currentTime - <?php echo $_SESSION['otp_time']; ?> > 300) {
                                showCustomAlert('OTP Expired. Please request a new OTP');
                                return;
                            }

                            if(enteredOTP == <?php echo $otp; ?>) {
                                // Send data to insert_user.php
                                fetch('insert_user.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        name: '<?php echo $name; ?>',
                                        email: '<?php echo $email; ?>',
                                        phone: '<?php echo $phone; ?>',
                                        password: '<?php echo $hashed_password; ?>'
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if(data.success) {
                                        showCustomAlert('Account created successfully!');
                                        setTimeout(() => {
                                            window.location.href = 'login.html';
                                        }, 2000);
                                    } else {
                                        showCustomAlert('Failed to create account');
                                    }
                                })
                                .catch(error => {
                                    showCustomAlert('Error: ' + error.message);
                                });
                            } else {
                                showCustomAlert('Invalid OTP. Please enter the correct OTP');
                            }
                        }
                        </script>
                        <?php
                    } else {
                        throw new Exception("Failed to send OTP email");
                    }
                } catch (Exception $e) {
                    echo "Failed to send OTP email. Error: " . $mail->ErrorInfo;
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                <label for="phone">Phone Number</label>
                <i class="fas fa-phone icon"></i>
                <span class="error-message" id="phoneError"></span>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <label for="password">Password</label>
                <i class="fas fa-lock icon"></i>
                <i class="far fa-eye password-toggle" id="togglePassword"></i>
                <span class="error-message" id="passwordError"></span>
            </div>
            
            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                <label for="confirm_password">Confirm Password</label>
                <i class="fas fa-lock icon"></i>
                <i class="far fa-eye password-toggle" id="toggleConfirmPassword"></i>
                <span class="error-message" id="confirmPasswordError"></span>
            </div>
            
            <button type="submit">Register</button>
        </form>


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
            const phoneInput = document.getElementById('phone');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            nameInput.addEventListener('input', function() {
                const nameError = document.getElementById('nameError');
                if (this.value.trim() === '') {
                    nameError.textContent = 'Name is required';
                    this.parentElement.classList.add('error');
                } else if (this.value.startsWith(' ')) {
                    nameError.textContent = 'Name cannot start with a space';
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
                const value = this.value.trim();
                
                if (value === '') {
                    emailError.textContent = 'Email is required';
                    this.parentElement.classList.add('error');
                } else if (!emailPattern.test(value)) {
                    emailError.textContent = 'Please enter a valid email address';
                    this.parentElement.classList.add('error');
                } else if (value !== value.toLowerCase()) {
                    emailError.textContent = 'Email must be in lowercase';
                    this.parentElement.classList.add('error');
                } else {
                    emailError.textContent = '';
                    this.parentElement.classList.remove('error');
                }
            });

            phoneInput.addEventListener('input', function() {
                const phoneError = document.getElementById('phoneError');
                const phonePattern = /^[6-9][0-9]{9}$/;
                const value = this.value.trim();
                
                if (value === '') {
                    phoneError.textContent = 'Phone number is required';
                    this.parentElement.classList.add('error');
                } else if (value.length !== 10) {
                    phoneError.textContent = 'Phone number must be exactly 10 digits';
                    this.parentElement.classList.add('error');
                } else if (!phonePattern.test(value)) {
                    phoneError.textContent = 'Invalid phone number. Must start with 6, 7, 8, or 9';
                    this.parentElement.classList.add('error');
                } else if (/(\d)\1{7,}/.test(value)) {
                    phoneError.textContent = 'Invalid phone number. Too many repeated digits';
                    this.parentElement.classList.add('error');
                } else {
                    phoneError.textContent = '';
                    this.parentElement.classList.remove('error');
                }

                // Format the phone number as user types
                this.value = value.replace(/[^0-9]/g, '').slice(0, 10);
            });

            // Prevent non-numeric input in phone field
            phoneInput.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
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
                
                // Check if confirm password matches when password changes
                if (confirmPasswordInput.value) {
                    checkPasswordsMatch();
                }
            });
            
            confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
            
            function checkPasswordsMatch() {
                const confirmPasswordError = document.getElementById('confirmPasswordError');
                
                if (confirmPasswordInput.value.trim() === '') {
                    confirmPasswordError.textContent = 'Confirm password is required';
                    confirmPasswordInput.parentElement.classList.add('error');
                } else if (confirmPasswordInput.value !== passwordInput.value) {
                    confirmPasswordError.textContent = 'Passwords do not match';
                    confirmPasswordInput.parentElement.classList.add('error');
                } else {
                    confirmPasswordError.textContent = '';
                    confirmPasswordInput.parentElement.classList.remove('error');
                }
            }

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Trigger validation for all fields
                ['name', 'email', 'phone', 'password', 'confirm_password'].forEach(field => {
                    const input = document.getElementById(field);
                    const event = new Event('input');
                    input.dispatchEvent(event);
                    
                    if (document.getElementById(`${field}Error`) && document.getElementById(`${field}Error`).textContent) {
                        isValid = false;
                    }
                });
                
                // Special check for confirm password
                if (passwordInput.value !== confirmPasswordInput.value) {
                    document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                    confirmPasswordInput.parentElement.classList.add('error');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>