<?php
include("db.php");
session_start();

// Fetch Users
$query = "SELECT id, name, email FROM users";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Users List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background for contrast */
            font-family: 'Arial', sans-serif; /* Clean font */
        }
        .main-content {
            margin-left: 250px; /* Space for sidebar */
            padding: 20px; /* Padding for content */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <nav>
            <a href="admin.php" class="nav-link">Dashboard</a>
            <a href="admincat.php" class="nav-link">Categories</a>
            <a href="productmang.php" class="nav-link">Products</a>
            <a href="users.php" class="nav-link active">Users</a> <!-- Link to Users List -->
            <a href="adminorders.php" class="nav-link">Orders</a>
            <a href="adminsettings.php" class="nav-link">Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Users List</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html> 