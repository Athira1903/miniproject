<?php
session_start();
include("db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get wishlist items with product details
$user_id = $_SESSION['user_id'];
$query = "SELECT p.*, w.id as wishlist_id 
          FROM wishlist w 
          JOIN products p ON w.product_id = p.product_id 
          WHERE w.user_id = ?";

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
    <title>My Wishlist - Woodpecker</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add these custom styles */
        .wishlist-header {
            background: linear-gradient(135deg, #0a2246 0%, #1a3a6c 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            border-radius: 0 0 50px 50px;
            position: relative;
        }

        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .card-img-top {
            height: 250px !important;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .price {
            font-size: 1.2rem;
            color: #0a2246;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .btn-remove {
            background-color: #ff4757;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background-color: #ff6b81;
            transform: scale(1.05);
        }

        .btn-view {
            background-color: #0a2246;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            background-color: #1a3a6c;
            transform: scale(1.05);
        }

        .empty-wishlist {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-wishlist i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        /* Add these new styles */
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
    </style>
</head>
<body>
    <!-- Header -->
  

    <div class="wishlist-header">
        <div class="container position-relative">
            <a href="armchair.php" class="back-button">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
            <h2 class="text-center mb-2"><i class="fas fa-heart me-2"></i>My Wishlist</h2>
            <p class="text-center text-light mb-0">Save your favorite items here</p>
        </div>
    </div>

    <div class="container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row g-4">
                <?php while ($item = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                <div class="price">₹<?php echo number_format($item['price'], 2); ?></div>
                                
                                <div class="button-group">
                                    <button class="btn btn-remove" 
                                            onclick="toggleWishlist(<?php echo $item['product_id']; ?>, event)">
                                        <i class="fas fa-heart-broken"></i> Remove
                                    </button>
                                    
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>" 
                                       class="btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart-broken"></i>
                <h3>Your wishlist is empty</h3>
                <p class="text-muted">Explore our collection and add items you love!</p>
                <a href="armchair.php" class="btn btn-view">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

   

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleWishlist(productId, event) {
        event.preventDefault();
        
        fetch('toggle_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the product card from the page
                const card = event.target.closest('.col-md-3');
                card.remove();
                
                // Update the wishlist count in the header
                const wishlistCount = document.getElementById('wishlistCount');
                if (wishlistCount) {
                    wishlistCount.textContent = data.count;
                }
                
                // If no more items, show empty message
                if (data.count === 0) {
                    location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating wishlist. Please try again.');
        });
    }
    </script>
</body>
</html>
