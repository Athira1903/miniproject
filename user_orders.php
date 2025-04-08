<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch orders for the current user
$user_id = $_SESSION['user_id'];
$query = "SELECT os.*, sp.title as product_name, sp.image as product_image 
          FROM order_seller os 
          JOIN seller_product sp ON os.product_id = sp.id 
          WHERE os.user_id = ? 
          ORDER BY os.order_date DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Woodpecker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .orders-header {
            background: linear-gradient(135deg, #0a2246 0%, #1a3a6c 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            border-radius: 0 0 50px 50px;
            position: relative;
        }

        .back-button {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: white;
            color: #0a2246;
            transform: translateY(-50%) scale(1.05);
        }

        .order-card {
            background: white;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
        }

        .order-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .order-body {
            padding: 25px;
        }

        .product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .order-card:hover .product-image {
            transform: scale(1.05);
        }

        .status-badge {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 600;
            color: #0a2246;
        }

        .btn-view-details {
            background-color: #0a2246;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-view-details:hover {
            background-color: #1a3a6c;
            transform: scale(1.05);
        }

        .empty-orders {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-orders i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .continue-shopping-btn {
            background-color: #0a2246;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .continue-shopping-btn:hover {
            background-color: #1a3a6c;
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <div class="orders-header">
        <div class="container position-relative">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
            <h2 class="text-center mb-2"><i class="fas fa-shopping-bag me-2"></i>My Orders</h2>
            <p class="text-center text-light mb-0">Track and manage your orders</p>
        </div>
    </div>

    <div class="container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                <div class="order-card">
                    <div class="order-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Order ID: #<?php echo $order['id']; ?></strong>
                            <br>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?>
                            </small>
                        </div>
                        <span class="status-badge <?php echo $order['status'] === 'pending' ? 'status-pending' : 'status-completed'; ?>">
                            <i class="fas <?php echo $order['status'] === 'pending' ? 'fa-clock' : 'fa-check'; ?> me-1"></i>
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="order-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo htmlspecialchars($order['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                                     class="product-image">
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo htmlspecialchars($order['product_name']); ?></h5>
                                <div class="order-details">
                                    <p class="mb-2"><i class="fas fa-box me-2"></i>Quantity: <?php echo $order['quantity']; ?></p>
                                    <p class="mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($order['delivery_address']); ?>, 
                                        <?php echo htmlspecialchars($order['city']); ?> - 
                                        <?php echo htmlspecialchars($order['postal_code']); ?>
                                    </p>
                                    <p class="mb-2"><i class="fas fa-credit-card me-2"></i>Payment: <?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="price mb-3">
                                    ₹<?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-view-details">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p class="text-muted">Looks like you haven't made any orders yet.</p>
                <a href="index.php" class="continue-shopping-btn">
                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 