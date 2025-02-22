<?php
include("db.php");
session_start();

// Fetch all products with category and subcategory information
$products_query = "SELECT p.*, s.subcategory_name, c.category_name 
                  FROM products p 
                  JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
                  JOIN categories c ON s.category_id = c.category_id 
                  ORDER BY c.category_name, s.subcategory_name, p.product_name";
$products_result = mysqli_query($conn, $products_query);

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']); // Ensure the product ID is an integer
    $delete_query = "DELETE FROM products WHERE product_id = $product_id";
    mysqli_query($conn, $delete_query);
    // Optionally, you can add a success message or redirect after deletion
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        .fixed-size {
            width: 100%; /* Adjust as needed */
            height: 150px; /* Fixed height */
            object-fit: cover; /* Maintain aspect ratio */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Current Products</h2>
        <a href="productmang.php" class="btn btn-primary mb-3">Back to Product Management</a>
        <div class="card">
            <div class="card-body">
                <?php
                if (mysqli_num_rows($products_result) > 0) {
                    while($product = mysqli_fetch_assoc($products_result)) {
                ?>
                    <div class="product-card">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if(!empty($product['image_url'])) { ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="product-image fixed-size" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <?php } else { ?>
                                    <div class="text-center text-muted">No Image</div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4">
                                <h5 class="mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($product['category_name']); ?> > 
                                    <?php echo htmlspecialchars($product['subcategory_name']); ?>
                                </small>
                                <p class="mb-0 mt-2"><?php echo htmlspecialchars($product['product_description']); ?></p>
                            </div>
                            <div class="col-md-2">
                                <span class="price-tag">$<?php echo number_format($product['price'], 2); ?></span>
                            </div>
                            <div class="col-md-2">
                                <span class="stock-badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                    Stock: <?php echo $product['stock_quantity']; ?>
                                </span>
                            </div>
                            <div class="col-md-2 text-end">
                                <form action="" method="POST" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo '<div class="text-center text-muted">No products found</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html> 