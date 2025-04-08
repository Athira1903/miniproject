<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: user_orders.php');
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch order details from order_seller table
$query = "SELECT os.*, sp.title as product_name, sp.image as product_image 
          FROM order_seller os 
          JOIN seller_product sp ON os.product_id = sp.id 
          WHERE os.id = ? AND os.user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: user_orders.php');
    exit();
}

$order = mysqli_fetch_assoc($result);
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
            font-family: 'Poppins', sans-serif;
        }

        .details-header {
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
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .product-image {
            max-width: 250px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1.2rem 0;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s ease;
        }

        .detail-row:hover {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.2rem 1rem;
        }

        .detail-label {
            color: #666;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .detail-value {
            color: #0a2246;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: right;
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

        .review-section {
            background: #fff;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .review-form {
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .stars {
            font-size: 1.5rem;
        }

        .star-label {
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .star-label:hover {
            transform: scale(1.2);
        }

        .existing-review {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
        }

        .btn-submit-review {
            background-color: #0a2246;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-submit-review:hover {
            background-color: #1a3a6c;
            transform: scale(1.05);
        }

        textarea.form-control {
            border-radius: 15px;
            padding: 15px;
            resize: none;
        }

        textarea.form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(10, 34, 70, 0.25);
            border-color: #0a2246;
        }
    </style>
</head>
<body>
    <div class="details-header">
        <div class="container position-relative">
            <a href="user_orders.php" class="back-button">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
            <h2 class="text-center mb-2"><i class="fas fa-receipt me-2"></i>Order Details</h2>
            <p class="text-center text-light mb-0">Order #<?php echo $order['id']; ?></p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="order-card">
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($order['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                             class="product-image">
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-box me-2"></i>Product</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['product_name']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-hashtag me-2"></i>Order ID</span>
                        <span class="detail-value">#<?php echo $order['id']; ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-cubes me-2"></i>Quantity</span>
                        <span class="detail-value"><?php echo $order['quantity']; ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-rupee-sign me-2"></i>Total Amount</span>
                        <span class="detail-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-credit-card me-2"></i>Payment Method</span>
                        <span class="detail-value"><?php echo ucfirst($order['payment_method']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</span>
                        <span class="detail-value">
                            <?php 
                            echo htmlspecialchars($order['delivery_address']) . ', ' . 
                                 htmlspecialchars($order['city']) . ' - ' . 
                                 htmlspecialchars($order['postal_code']); 
                            ?>
                        </span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-calendar-alt me-2"></i>Order Date</span>
                        <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-info-circle me-2"></i>Status</span>
                        <span class="status-badge <?php echo $order['status'] === 'pending' ? 'status-pending' : 'status-completed'; ?>">
                            <i class="fas <?php echo $order['status'] === 'pending' ? 'fa-clock' : 'fa-check'; ?> me-1"></i>
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Review Section -->
                <div class="review-section">
                    <h4 class="mb-4"><i class="fas fa-star me-2"></i>Product Review</h4>
                    <?php
                    // Check if user has already reviewed this product
                    $review_check_query = "SELECT * FROM product_reviews 
                                        WHERE user_id = ? AND product_id = ? AND order_id = ?";
                    $stmt = mysqli_prepare($conn, $review_check_query);
                    mysqli_stmt_bind_param($stmt, "iii", $user_id, $order['product_id'], $order_id);
                    mysqli_stmt_execute($stmt);
                    $review_result = mysqli_stmt_get_result($stmt);
                    
                    if(mysqli_num_rows($review_result) > 0) {
                        $review = mysqli_fetch_assoc($review_result);
                        ?>
                        <div class="existing-review">
                            <div class="stars mb-3">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-2"><?php echo htmlspecialchars($review['review_text']); ?></p>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                Posted on: <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                            </small>
                        </div>
                    <?php } else { ?>
                        <form id="reviewForm" action="submit_review.php" method="POST" class="review-form">
                            <input type="hidden" name="product_id" value="<?php echo $order['product_id']; ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                            
                            <div class="rating mb-4">
                                <label class="mb-2 d-block">Your Rating:</label>
                                <div class="stars">
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="d-none">
                                        <label for="star<?php echo $i; ?>" class="star-label me-1">
                                            <i class="far fa-star text-warning"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="review" class="form-label">Your Review:</label>
                                <textarea class="form-control" id="review" name="review_text" rows="4" 
                                          placeholder="Share your experience with this product..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-submit-review">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.star-label').forEach(label => {
            label.addEventListener('mouseover', function() {
                this.querySelector('i').classList.replace('far', 'fas');
                let prevSibling = this.parentElement;
                while(prevSibling = prevSibling.previousElementSibling) {
                    if(prevSibling.tagName === 'LABEL') {
                        prevSibling.querySelector('i').classList.replace('far', 'fas');
                    }
                }
            });

            label.addEventListener('mouseout', function() {
                if(!this.previousElementSibling.checked) {
                    this.querySelector('i').classList.replace('fas', 'far');
                    let prevSibling = this.parentElement;
                    while(prevSibling = prevSibling.previousElementSibling) {
                        if(prevSibling.tagName === 'LABEL' && !prevSibling.previousElementSibling.checked) {
                            prevSibling.querySelector('i').classList.replace('fas', 'far');
                        }
                    }
                }
            });

            label.addEventListener('click', function() {
                this.previousElementSibling.checked = true;
                document.querySelectorAll('.star-label i').forEach(star => {
                    star.classList.replace('fas', 'far');
                });
                this.querySelector('i').classList.replace('far', 'fas');
                let prevSibling = this.parentElement;
                while(prevSibling = prevSibling.previousElementSibling) {
                    if(prevSibling.tagName === 'LABEL') {
                        prevSibling.querySelector('i').classList.replace('far', 'fas');
                    }
                }
            });
        });
    </script>
</body>
</html>