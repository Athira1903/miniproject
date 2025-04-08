<?php
session_start();

if (!isset($_SESSION['order_details'])) {
    header('Location: index.php');
    exit();
}

$order_details = $_SESSION['order_details'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Woodpecker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }
        .order-title {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 2rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .detail-value {
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
            text-align: right;
        }
        .status-badge {
            background: #ffc107;
            color: #000;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .btn-custom {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            margin: 0 10px;
        }
        .btn-print {
            background-color: #007bff;
            color: white;
        }
        .btn-home {
            background-color: #28a745;
            color: white;
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="order-card">
                    <h1 class="order-title">Order Details</h1>
                    
                    <div class="detail-row">
                        <span class="detail-label">Order ID:</span>
                        <span class="detail-value"><?php echo $order_details['order_id']; ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Product:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order_details['product_name']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Quantity:</span>
                        <span class="detail-value"><?php echo $order_details['quantity']; ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value">₹<?php echo number_format($order_details['total_amount'], 2); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value"><?php echo $order_details['payment_method'] === 'cod' ? 'Cash on Delivery' : ucfirst($order_details['payment_method']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Delivery Address:</span>
                        <span class="detail-value">
                            <?php 
                            $address = '';
                            if (isset($order_details['delivery_address'])) {
                                $address .= $order_details['delivery_address'];
                                if (isset($order_details['city'])) {
                                    $address .= ', ' . $order_details['city'];
                                }
                                if (isset($order_details['postal_code'])) {
                                    $address .= ' - ' . $order_details['postal_code'];
                                }
                                echo htmlspecialchars($address);
                            } else {
                                echo 'Address not available';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value"><?php echo date('F j, Y, g:i a'); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge">
                                <?php echo $order_details['payment_method'] === 'cod' ? 'Pending' : 'Completed'; ?>
                            </span>
                        </span>
                    </div>

                    <div class="buttons-container">
                        <a href="javascript:window.print()" class="btn btn-custom btn-print">
                            <i class="fas fa-print me-2"></i>PRINT BILL
                        </a>
                        <a href="index.php" class="btn btn-custom btn-home">
                            <i class="fas fa-home me-2"></i>BACK TO HOME
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Clear the order details from session after displaying
unset($_SESSION['order_details']);
?>