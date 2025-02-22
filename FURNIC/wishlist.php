<?php
// Start the session and include any necessary PHP logic
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopping Cart - WOODPECKER</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header remains the same -->
    <header>
        <div class="header-container">
            <!-- Search and Currency -->
            <div class="left-header">
                <div class="search-box">
                    <label for="search-input" class="visually-hidden">Search</label>
                    <input id="search-input" type="search" placeholder="Search" aria-label="Search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </div>
                <div class="currency-selector">
                    <label for="currency-select" class="visually-hidden">Select currency</label>
                    <select id="currency-select" aria-label="Currency selector">
                        <option value="INR">🇮🇳 INR ₹</option>
                        <option value="USD">🇺🇸 USD $</option>
                    </select>
                </div>
            </div>
            <!-- Logo -->
            <div class="logo">
                <a href="/">
                    <img src="logo.png" alt="Woodpecker Logo">
                </a>
            </div>

            <!-- User Actions -->
            <div class="right-header">
                <div class="user-actions">
                    <a href="login.html" aria-label="Account">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="wishlist.php" class="wishlist-icon" aria-label="Wishlist">
                        <i class="fas fa-heart"></i>
                        <span class="counter">0</span>
                    </a>
                    <a href="cart.php" class="cart-icon" aria-label="Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="counter">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Cart Page Content Only -->
    <div class="page-container">
        <div class="cart-section">
            <h2 class="section-title">Shopping Cart (3 items)</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Here you would fetch cart items from your database
                    // Example structure:
                    /*
                    $cart_items = fetch_cart_items();
                    foreach($cart_items as $item) {
                        echo "<tr>";
                        // Output item details
                        echo "</tr>";
                    }
                    */
                    ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="/api/placeholder/100/100" alt="Product" class="product-image">
                                <div class="product-details">
                                    <h4>Wooden Chair</h4>
                                    <p>Classic Design</p>
                                </div>
                            </div>
                        </td>
                        <td class="product-price">₹1,999</td>
                        <td>
                            <div class="quantity-selector">
                                <button class="quantity-btn">-</button>
                                <input type="number" value="1" min="1" class="quantity-input">
                                <button class="quantity-btn">+</button>
                            </div>
                        </td>
                        <td class="product-price">₹1,999</td>
                        <td>
                            <button class="remove-btn" onclick="removeFromCart(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹5,997</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>₹299</span>
                </div>
                <div class="summary-row">
                    <strong>Total</strong>
                    <strong>₹6,296</strong>
                </div>
                <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
            </div>
        </div>
    </div>

    <!-- Footer remains the same -->
    <footer>
        <!-- Footer content -->
    </footer>

    <script>
        function removeFromCart(button) {
            const row = button.closest('tr');
            const productId = row.getAttribute('data-product-id');
            
            // Make an AJAX call to remove item from cart
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    row.remove();
                    updateCartTotal();
                }
            });
        }

        function updateCartTotal() {
            // Add your cart total calculation logic here
        }

        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>