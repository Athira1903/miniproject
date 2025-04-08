<?php
include("db.php");
session_start();

// Fetch all sellers
$query = "SELECT id, name, email, phone, status FROM users WHERE role='seller'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching sellers: " . mysqli_error($conn));
}

// Handle enable/disable action
if (isset($_POST['toggle_status'])) {
    $seller_id = $_POST['seller_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE users SET status = '$new_status' WHERE id = $seller_id";
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        header("Location: manageseller.php");
        exit();
    } else {
        echo "Error updating seller status: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Management</title>
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

        .table th, .table td {
            vertical-align: middle;
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

        .welcome-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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

        .nav-link.active {
            background: rgba(43, 220, 23, 0.2);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .add-seller-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <nav>
            <a href="adminDash.php" class="nav-link" data-section="dashboard-section">Dashboard</a>
                <a href="productmang.php" class="nav-link">Product Management</a>
                <a href="admincat.php" class="nav-link">Category Management</a>
                <a href="manageseller.php" class="nav-link">Manage Sellers</a>
                <a href="#" class="nav-link" data-section="orders-section">Order Management</a>
                <a href="reports.php" class="nav-link" data-section="reports-section">Reports</a>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Seller Management</h1>
                <button class="btn btn-light" onclick="logout()">Logout</button>
            </div>

            <div class="container mt-4">
                <div class="welcome-section">
                    <h2>Seller Management</h2>
                    <p>Manage all sellers from this dashboard.</p>
                    <button class="btn btn-primary add-seller-btn" onclick="window.location.href='add_seller.php'">
                        <i class="fas fa-plus"></i> Add New Seller  
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) { 
                                            $status = isset($row['status']) ? $row['status'] : 'active';
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $status == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="seller_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo $status == 'active' ? 'inactive' : 'active'; ?>">
                                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $status == 'active' ? 'danger' : 'success'; ?>">
                                                        <?php echo $status == 'active' ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No sellers found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
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