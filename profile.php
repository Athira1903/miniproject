<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Use session variables or hardcoded values instead of database query
$email = $_SESSION['email'];
$name = $_SESSION['name'] ?? 'John Doe'; // Default or from session
$phone = $_SESSION['phone'] ?? '123-456-7890';
$profile_pic = $_SESSION['profile_pic'] ?? null;
$address = $_SESSION['address'] ?? '123 Main Street';
$dob = $_SESSION['dob'] ?? '1990-01-01';
$state = $_SESSION['state'] ?? '';
$pincode = $_SESSION['pincode'] ?? '';

// Fetch user data from database
$row = []; // Assuming $row is fetched from a database query

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --background-color: #f5f5f5;
            --card-color: #ffffff;
            --text-color: #333333;
            --border-radius: 15px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1600px;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            padding: 30px;
        }

        .profile-sidebar {
            background: var(--card-color);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, #4CAF50, #2196F3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            color: white;
            text-transform: uppercase;
            overflow: hidden;
            position: relative;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .profile-role {
            color: #666;
            margin-bottom: 20px;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .main-content {
            background: var(--card-color);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 24px;
            margin-bottom: 30px;
            color: var(--text-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .edit-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .info-item:hover {
            transform: translateY(-5px);
        }

        .info-label {
            font-size: 16px;
            color: #666;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 20px;
            color: var(--text-color);
            font-weight: 500;
        }

        .edit-form {
            display: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .save-btn, .cancel-btn {
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .save-btn {
            background: var(--primary-color);
            color: white;
        }

        .cancel-btn {
            background: #dc3545;
            color: white;
        }

        .save-btn:hover, .cancel-btn:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #b71c1c;
            border: 1px solid #b71c1c;
        }

        .profile-pic-upload {
            margin-bottom: 20px;
        }

        .profile-pic-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 10px auto;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .profile-pic-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            margin-top: 20px;
            transition: transform 0.2s, background-color 0.2s;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            background-color: #3d8b40;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-sidebar">
            <div class="profile-image">
                <?php if (!empty($profile_pic)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                <?php else: ?>
                    <?php echo substr($name, 0, 1); ?>
                <?php endif; ?>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($name); ?></h2>
            <p class="profile-role">Premium Member</p>
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-value">5</div>
                    <div class="stat-label">Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">2</div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>
            <a href="index.php" class="back-btn">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>

        <div class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div id="display-info">
                <div class="section-title">
                    Profile Information
                    <button class="edit-btn" onclick="showEditForm()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($name); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo !empty($dob) ? htmlspecialchars($dob) : 'Not specified'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo !empty($address) ? htmlspecialchars($address) : 'Not specified'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">State</div>
                        <div class="info-value"><?php echo !empty($state) ? htmlspecialchars($state) : 'Not specified'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pincode</div>
                        <div class="info-value"><?php echo !empty($pincode) ? htmlspecialchars($pincode) : 'Not specified'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?php echo date('F Y'); ?></div>
                    </div>
                </div>
            </div>

            <div id="edit-form" class="edit-form">
                <div class="section-title">Edit Profile</div>
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="profile-pic-upload">
                        <label>Profile Picture</label>
                        <div class="profile-pic-preview">
                            <?php if (!empty($profile_pic)): ?>
                                <img id="preview-img" src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Preview">
                            <?php else: ?>
                                <img id="preview-img" src="#" alt="Profile Preview" style="display: none;">
                                <span id="preview-placeholder"><?php echo substr($name, 0, 1); ?></span>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <select id="state" name="state">
                            <option value="">Select State</option>
                            <?php
                            $states = [
                                "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat", 
                                "Haryana", "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", 
                                "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", 
                                "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", 
                                "Uttarakhand", "West Bengal", "Andaman and Nicobar Islands", "Chandigarh", 
                                "Dadra and Nagar Haveli and Daman and Diu", "Delhi", "Jammu and Kashmir", 
                                "Ladakh", "Lakshadweep", "Puducherry"
                            ];
                            foreach ($states as $stateOption) {
                                $selected = ($state == $stateOption) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($stateOption) . "\" $selected>" . htmlspecialchars($stateOption) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pincode">Pincode</label>
                        <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($pincode); ?>">
                    </div>
                    <div class="button-group">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <button type="button" class="cancel-btn" onclick="hideEditForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showEditForm() {
            document.getElementById('display-info').style.display = 'none';
            document.getElementById('edit-form').style.display = 'block';
        }

        function hideEditForm() {
            document.getElementById('display-info').style.display = 'block';
            document.getElementById('edit-form').style.display = 'none';
        }

        function previewImage(input) {
            const preview = document.getElementById('preview-img');
            const placeholder = document.getElementById('preview-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
                if (placeholder) {
                    placeholder.style.display = 'block';
                }
            }
        }
    </script>
</body>
</html>