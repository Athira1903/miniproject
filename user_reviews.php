<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders with products
$sql = "SELECT o.order_id, o.product_id, o.order_date, p.product_name, p.image_url 
        FROM orders o 
        LEFT JOIN products p ON o.product_id = p.product_id 
        WHERE o.user_id = ? AND o.status = 'delivered'
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .rating input {
            display: none;
        }
        .rating label {
            cursor: pointer;
            font-size: 25px;
            color: #ddd;
            padding: 5px;
        }
        .rating input:checked ~ label {
            color: #ffd700;
        }
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffd700;
        }
        .review-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }
        .product-image {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Your Product Reviews</h2>
        
        <?php while ($order = $orders->fetch_assoc()): ?>
            <div class="review-card">
                <div class="row">
                    <div class="col-md-2">
                        <img src="<?php echo htmlspecialchars($order['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                             class="product-image">
                    </div>
                    <div class="col-md-10">
                        <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                        <p>Ordered on: <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                        
                        <form action="submit_review.php" method="POST" class="review-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="product_id" value="<?php echo $order['product_id']; ?>">
                            
                            <div class="rating mb-3">
                                <input type="radio" name="rating" value="5" id="star5_<?php echo $order['order_id']; ?>">
                                <label for="star5_<?php echo $order['order_id']; ?>">★</label>
                                <input type="radio" name="rating" value="4" id="star4_<?php echo $order['order_id']; ?>">
                                <label for="star4_<?php echo $order['order_id']; ?>">★</label>
                                <input type="radio" name="rating" value="3" id="star3_<?php echo $order['order_id']; ?>">
                                <label for="star3_<?php echo $order['order_id']; ?>">★</label>
                                <input type="radio" name="rating" value="2" id="star2_<?php echo $order['order_id']; ?>">
                                <label for="star2_<?php echo $order['order_id']; ?>">★</label>
                                <input type="radio" name="rating" value="1" id="star1_<?php echo $order['order_id']; ?>">
                                <label for="star1_<?php echo $order['order_id']; ?>">★</label>
                            </div>
                            
                            <div class="form-group mb-3">
                                <textarea name="review_text" class="form-control" rows="3" 
                                          placeholder="Write your review here..." required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html> 