<?php
include("db.php");
session_start();

// Fetch all users
$query = "SELECT id, name, email FROM users";
$result = mysqli_query($conn, $query);

// Check for query errors
if (!$result) {
    echo "Error: " . mysqli_error($conn);
}

// Add this after the first query at the top of the file
$seller_query = "SELECT id, name, email,password  FROM users WHERE role='seller'";
$seller_result = mysqli_query($conn, $seller_query);

if (!$seller_result) {
    echo "Error: " . mysqli_error($conn);
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
        /* Updated Color Scheme */
        :root {
            --primary-color: #007bff; /* Blue */
            --secondary-color: #6c757d; /* Gray */
            --accent-color: #28a745; /* Green */
            --danger-color: #dc3545; /* Red */
            --light-bg: #f8f9fa; /* Light Gray */
            --dark-bg: #343a40; /* Dark Gray */
            --text-color: #212529; /* Dark Text */
            --sidebar-width: 250px;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: #0a2246; /* Gradient */
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
            background: #0a2246; /* Darker Blue */
            transform: translateY(-2px);
        }

        .welcome-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Responsive Design */
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
                <a href="#" class="nav-link" id="dashboardLink">Dashboard</a>
                <a href="users" class="nav-link" id="manageUsersLink">product</a>
                <a href="admincat.php" class="nav-link" id="manageUsersLink">category</a>
                <a href="#" class="nav-link">Orders</a>
                <a href="#" class="nav-link">Reports</a>
                <a href="#" class="nav-link">Settings</a>
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <button class="btn btn-light">Logout</button>
            </div>

            <div class="container mt-4">
                <div class="welcome-section">
                    <h2>Welcome Admin</h2>
                    <p>This is your dashboard. Use the sidebar to navigate.</p>
                    <button class="btn btn-light" id="manageUsersLink">
                        Manage Users
                    </button>
                </div>

                <button class="btn btn-light" id="manageUsersLink">
                    Manage Users
                </button>
                    
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                   
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) { 
                                ?>
                                    <tr>
                                        
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td>
                                            <button class="btn btn-success btn-sm">Edit</button>
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No users found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        const dashboardLink = document.getElementById('dashboardLink');
        const manageUsersLink = document.getElementById('manageUsersLink');
        const dashboardContent = document.getElementById('dashboardContent');
        const usersContent = document.getElementById('usersContent');
        const userTableContainer = document.getElementById('userTableContainer');

        dashboardLink.addEventListener('click', function(e) {
            e.preventDefault();
            dashboardContent.classList.remove('hidden');
            usersContent.classList.add('hidden');
        });

        manageUsersLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Toggle the visibility of the user table
            if (userTableContainer.style.display === "none" || userTableContainer.style.display === "") {
                userTableContainer.style.display = "block";
            } else {
                userTableContainer.style.display = "none";
            }
        });

        // Show dashboard content by default
        dashboardContent.classList.remove('hidden');
        usersContent.classList.add('hidden');
    </script>
</body>
</html>