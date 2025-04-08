<?php
include("db.php");
session_start();

// Fetch all users with status field
$query = "SELECT id, name, email FROM users";
$result = mysqli_query($conn, $query);

// Check for query errors
if (!$result) {
    die("Error fetching users: " . mysqli_error($conn));
}

// Fetch all sellers
$seller_query = "SELECT id, name, email FROM users WHERE role='seller'";
$seller_result = mysqli_query($conn, $seller_query);

if (!$seller_result) {
    die("Error fetching sellers: " . mysqli_error($conn));
}

// Handle enable/disable action
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE users SET status = '$new_status' WHERE id = $user_id";
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        // Redirect to refresh the page
        header("Location: adminDash.php");
        exit();
    } else {
        echo "Error updating user status: " . mysqli_error($conn);
    }
}

// Fetch orders data
// Modify the orders query to include status
$orders_query = "SELECT order_id, product_name, order_date, quantity, 
                 total_price, payment_method, delivery_address, city, 
                 postal_code, product_id, status 
                 FROM orders 
                 ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

if (!$orders_result) {
    die("Error fetching orders: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture Management Dashboard</title>
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

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
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

        .table td {
            vertical-align: middle;
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
                <a href="reports.php" class="nav-link">Reports</a>
           
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <button class="btn btn-light" onclick="logout()">Logout</button>
            </div>

            <div class="container mt-4">
                <div id="dashboard-section" class="content-section active">
                    <div class="welcome-section">
                        <h2>Welcome Admin</h2>
                        <p>This is your dashboard. Use the sidebar to navigate.</p>
                    </div>

                </div>

                <div id="orders-section" class="content-section">
                    <div class="welcome-section">
                        <h2>Order Management</h2>
                        <p>View and manage all customer orders.</p>
                    </div>

                    <div class="card">
                    <div class="card-body">
                    <h3>All Orders</h3>
                    <div class="table-responsive">
                     <table class="table table-striped">
                     <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Name</th>
                        <th>Order Date</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Delivery Address</th>
                        <th>City</th>
                        <th>Postal Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($orders_result) > 0) {
                        while ($order = mysqli_fetch_assoc($orders_result)) { 
                    ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch($order['status']) {
                                    case 'Pending':
                                        $statusClass = 'bg-warning';
                                        break;
                                    case 'Processing':
                                        $statusClass = 'bg-info';
                                        break;
                                    case 'Shipped':
                                        $statusClass = 'bg-primary';
                                        break;
                                    case 'Delivered':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'Cancelled':
                                        $statusClass = 'bg-danger';
                                        break;
                                    case 'Paid':
                                        $statusClass = 'bg-success';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                            <td><?php echo htmlspecialchars($order['city']); ?></td>
                            <td><?php echo htmlspecialchars($order['postal_code']); ?></td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No orders found</td></tr>";
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
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            window.location.href = 'login.html';
        }
        
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the section to show
                    const sectionId = this.getAttribute('data-section');
                    
                    // Hide all content sections
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show the selected section
                    document.getElementById(sectionId).classList.add('active');
                    
                    // Update active state in navigation
                    navLinks.forEach(navLink => {
                        navLink.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
            
            // View order details functionality
            document.querySelectorAll('.view-order').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    alert('View details for Order #' + orderId + '\nThis functionality can be implemented to show order details in a modal or redirect to an order details page.');
                    // You can implement this to:
                    // 1. Show a modal with order details
                    // 2. Redirect to order details page: window.location.href = 'order-details.php?id=' + orderId;
                });
            });
        });
    </script>
</body>
</html>