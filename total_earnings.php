<?php
session_start();
include("db.php");

// Check if user is logged in and is a seller
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Get seller information from database
$email = $_SESSION['email'];
$query = "SELECT * FROM users WHERE email = '$email' AND role = 'seller'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: login.html");
    exit();
}

$seller = mysqli_fetch_assoc($result);

// Fetch total amounts
$totals_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(total_price) as total_amount,
    SUM(CASE WHEN status = 'paid' THEN total_price ELSE 0 END) as paid_amount,
    SUM(CASE WHEN status != 'paid' THEN total_price ELSE 0 END) as pending_amount
    FROM orders";
$totals_result = mysqli_query($conn, $totals_query);
$totals = mysqli_fetch_assoc($totals_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Earnings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Earnings Overview</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Orders</h5>
                                        <h3 class="card-text"><?php echo number_format($totals['total_orders']); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Amount</h5>
                                        <h3 class="card-text"><?php echo number_format($totals['total_amount'], 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Paid Amount</h5>
                                        <h3 class="card-text"><?php echo number_format($totals['paid_amount'], 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Pending Amount</h5>
                                        <h3 class="card-text"><?php echo number_format($totals['pending_amount'], 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 