<?php
include("db.php");
session_start();

// Fetch all categories
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch all subcategories with their parent category names
$subcategories_query = "SELECT s.*, c.category_name 
                       FROM subcategories s 
                       JOIN categories c ON s.category_id = c.category_id 
                       ORDER BY c.category_name, s.subcategory_name";
$subcategories_result = mysqli_query($conn, $subcategories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Categories & Subcategories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Categories & Subcategories</h2>
        
        <!-- Add Back Button -->
        <a href="admincat.php" class="btn btn-primary mb-3">Back to Admin Categories</a>
        
        <div class="card">
            <div class="card-body">
                <?php
                mysqli_data_seek($categories_result, 0);
                while($category = mysqli_fetch_assoc($categories_result)):
                ?>
                    <div class="category-card">
                        <h6 class="mb-2">
                            <a href="/FURNI/<?php echo strtolower(str_replace(' ', '_', htmlspecialchars($category['category_name']))); ?>.php" class="fw-bold">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </a>
                        </h6>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($category['category_description']); ?></p>
                        
                        <!-- Subcategories -->
                        <div>
                            <?php
                            mysqli_data_seek($subcategories_result, 0);
                            while($subcategory = mysqli_fetch_assoc($subcategories_result)):
                                if($subcategory['category_id'] == $category['category_id']):
                            ?>
                                <div class="subcategory-item">
                                    <span class="fw-bold">
                                        <a href="/FURNI/<?php echo strtolower(str_replace(' ', '_', htmlspecialchars($subcategory['subcategory_name']))); ?>.php" class="fw-bold">
                                            <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                                        </a>
                                    </span>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($subcategory['subcategory_description']); ?></p>
                                </div>
                            <?php
                                endif;
                            endwhile;
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>