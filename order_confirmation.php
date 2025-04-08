<?php
session_start();
require_once 'db.php'; // Update this path if needed

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['order_id'];

// Modified query to avoid using the customers table
// This assumes product info is stored directly in the orders table
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found");
}

// Get order details from session if available
$order_details = isset($_SESSION['order_details']) ? $_SESSION['order_details'] : null;

// Get the customer name from order data (if available)
$customer_name = isset($order['customer_name']) ? $order['customer_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - FURNI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .confirmation-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
            padding: 40px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .confirmation-title {
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        .order-success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .btn-home {
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            margin-top: 30px;
        }
        .btn-home:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.2);
        }
        .me-3 {
            margin-right: 1rem;
        }
        @media print {
            .btn-home, .btn-primary, .navbar, footer {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Simple navbar instead of including a file -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">FURNI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container confirmation-container text-center">
        <i class="fas fa-check-circle order-success-icon"></i>
        <h1 class="confirmation-title">Order Placed Successfully!</h1>
        <p class="lead">Thank you<?php echo !empty($customer_name) ? ', ' . htmlspecialchars($customer_name) : ''; ?> for your order. We've received your order and will begin processing it soon.</p>
        
        <div class="order-details">
            <h3 class="mb-4">Order Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Order ID:</span>
                <span><?php echo $order_id; ?></span>
            </div>
            
            <?php if(!empty($customer_name)): ?>
            <div class="detail-row">
                <span class="detail-label">Customer Name:</span>
                <span><?php echo htmlspecialchars($customer_name); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="detail-row">
                <span class="detail-label">Product:</span>
                <span><?php echo isset($order['product_name']) ? htmlspecialchars($order['product_name']) : 'N/A'; ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Quantity:</span>
                <span><?php echo $order['quantity']; ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span>₹<?php echo number_format($order['total_price'], 2); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span>
                    <?php 
                    switch($order['payment_method']) {
                        case 'cod':
                            echo 'Cash on Delivery';
                            break;
                        case 'upi':
                            echo 'UPI';
                            break;
                        case 'card':
                            echo 'Credit/Debit Card';
                            break;
                        default:
                            echo $order['payment_method'];
                    }
                    ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Delivery Address:</span>
                <span><?php echo htmlspecialchars($order['delivery_address'] . ', ' . $order['city'] . ' - ' . $order['postal_code']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Order Date:</span>
                <span><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="badge bg-warning"><?php echo $order['status']; ?></span>
            </div>
        </div>
        
        <div class="mt-4">
            <button onclick="printBill()" class="btn btn-primary me-3">
                <i class="fas fa-print"></i> Print Bill
            </button>
            <a href="index.php" class="btn btn-home">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Simple footer instead of including a file -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> FURNI. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function printBill() {
        // Create a new window for printing
        let printContent = `
            <div class="bill-print" style="padding: 20px; font-family: Arial, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">FURNI</h2>
                    <p style="margin: 5px 0;">Order Invoice</p>
                    <hr style="margin: 15px 0;">
                </div>

                <div style="margin-bottom: 20px;">
                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                </div>

                <?php if(!empty($customer_name)): ?>
                <div style="margin-bottom: 20px;">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
                </div>
                <?php endif; ?>

                <div style="margin-bottom: 20px;">
                    <p><strong>Product:</strong> <?php echo isset($order['product_name']) ? htmlspecialchars($order['product_name']) : 'N/A'; ?></p>
                    <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                    <p><strong>Price per unit:</strong> ₹<?php echo number_format($order['total_price']/$order['quantity'], 2); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <p><strong>Delivery Address:</strong></p>
                    <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                    <p><?php echo htmlspecialchars($order['city']); ?> - <?php echo htmlspecialchars($order['postal_code']); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <p><strong>Payment Method:</strong> 
                        <?php 
                        switch($order['payment_method']) {
                            case 'cod':
                                echo 'Cash on Delivery';
                                break;
                            case 'upi':
                                echo 'UPI';
                                break;
                            case 'card':
                                echo 'Credit/Debit Card';
                                break;
                            default:
                                echo $order['payment_method'];
                        }
                        ?>
                    </p>
                </div>

                <hr style="margin: 15px 0;">

                <div style="text-align: right; margin-top: 20px;">
                    <h3 style="margin: 0;">Total Amount: ₹<?php echo number_format($order['total_price'], 2); ?></h3>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <p>Thank you for shopping with us!</p>
                </div>
            </div>
        `;

        let printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Order Invoice - #<?php echo $order_id; ?></title>
                </head>
                <body>
                    ${printContent}
                    <script>
                        window.onload = function() {
                            window.print();
                            window.onafterprint = function() {
                                window.close();
                            }
                        }
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
    </script>
</body>
</html>