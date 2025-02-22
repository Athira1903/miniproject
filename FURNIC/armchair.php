<?php
include("db.php");
session_start();

// Pagination settings
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build the base query
$query = "SELECT p.*, s.subcategory_name, c.category_name 
          FROM products p
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id
          JOIN categories c ON s.category_id = c.category_id";

// Handle filters
$where_conditions = [];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "c.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $where_conditions[] = "s.subcategory_id = ?";
    $params[] = $_GET['subcategory'];
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $_GET['min_price'];
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $_GET['max_price'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.product_description LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY p.created_at ASC";
        break;
    default: // newest
        $query .= " ORDER BY p.created_at DESC";
}

// Add pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total number of products for pagination
$count_query = "SELECT COUNT(*) as total FROM products p";
if (!empty($where_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $where_conditions);
}
$count_stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    // Remove the last two parameters (LIMIT and OFFSET)
    array_pop($params);
    array_pop($params);
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    }
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $items_per_page);

// Fetch categories for filter
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Woodpecker</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .products-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filters-sidebar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .filter-section {
            margin-bottom: 20px;
        }

        .filter-section h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .price-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .price-inputs input {
            width: 100px;
        }

        .product-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 16px;
            margin-bottom: 10px;
            height: 48px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-price {
            font-size: 18px;
            font-weight: 500;
            color: #000;
        }

        .stock-status {
            font-size: 14px;
            margin-top: 5px;
        }

        .in-stock {
            color: #28a745;
        }

        .out-of-stock {
            color: #dc3545;
        }

        .sorting-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .pagination {
            margin-top: 40px;
        }

        .page-link {
            color: #000;
            border-color: #000;
        }

        .page-link:hover {
            background-color: #000;
            color: #fff;
            border-color: #000;
        }

        .page-item.active .page-link {
            background-color: #000;
            border-color: #000;
        }

        @media (max-width: 768px) {
            .filters-sidebar {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header and Navigation code from the previous file -->

    <main class="products-container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-md-3">
                <div class="filters-sidebar">
                    <form action="" method="GET" id="filter-form">
                        <div class="filter-section">
                            <h3>Categories</h3>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-section">
                            <h3>Price Range</h3>
                            <div class="price-inputs">
                                <input type="number" name="min_price" class="form-control" placeholder="Min" 
                                       value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                                <span>-</span>
                                <input type="number" name="max_price" class="form-control" placeholder="Max"
                                       value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Apply Filters</button>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <div class="sorting-container">
                    <div class="results-count">
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $items_per_page, $total_products); ?> 
                        of <?php echo $total_products; ?> products
                    </div>
                    <select name="sort" class="form-select" style="width: auto;" onchange="window.location.href=this.value">
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" 
                                <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>
                            Newest First
                        </option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_low'])); ?>"
                                <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : ''; ?>>
                            Price: Low to High
                        </option>
                        <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_high'])); ?>"
                                <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>
                            Price: High to Low
                        </option>
                    </select>
                </div>

                <div class="row">
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="product-card">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                     class="product-image">
                                <div class="product-info">
                                    <h2 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                                    <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                                    <div class="stock-status <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                        <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </div>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <button class="btn btn-dark w-100 mt-2">Add to Cart</button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100 mt-2" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Product pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer would go here -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>