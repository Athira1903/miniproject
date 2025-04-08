<?php
include("db.php");
session_start();

// Process Product Addition
if (isset($_POST['add_product'])) {
    $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($conn, $_POST['stock_quantity']);
    $length = mysqli_real_escape_string($conn, $_POST['length']);
    $width = mysqli_real_escape_string($conn, $_POST['width']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $wood_type = mysqli_real_escape_string($conn, $_POST['wood_type']);

    // Handle image upload
    $image_url = '';
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "uploads/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . time() . '_' . basename($_FILES["product_image"]["name"]);
        if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }
    
    $query = "INSERT INTO products (subcategory_id, product_name, product_description, price, stock_quantity, length, width, height, image_url, wood_type) 
              VALUES ('$subcategory_id', '$product_name', '$product_description', '$price', '$stock_quantity', '$length', '$width', '$height', '$image_url', '$wood_type')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Product added successfully";
    } else {
        $_SESSION['error'] = "Error adding product: " . mysqli_error($conn);
    }
    header("Location: productmang.php");
    exit();
}

// Process Product Deletion
if (isset($_POST['delete_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    
    // Get image URL before deleting
    $image_query = "SELECT image_url FROM products WHERE product_id = '$product_id'";
    $image_result = mysqli_query($conn, $image_query);
    $product = mysqli_fetch_assoc($image_result);
    
    $query = "DELETE FROM products WHERE product_id = '$product_id'";
    
    if (mysqli_query($conn, $query)) {
        // Delete image file if exists
        if(!empty($product['image_url']) && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        $_SESSION['success'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting product: " . mysqli_error($conn);
    }
    header("Location: productmang.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #0a2246;
            --primary-light: #1a3256;
            --primary-dark: #091d3b;
            --primary-hover: #0c2a55;
            --secondary-color: #f8f9fa;
            --accent-color: #4a90e2;
            --text-color: #333;
            --light-grey: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(10, 34, 70, 0.15);
            --sidebar-width: 250px;
        }
        
        body {
            background-color: #f0f2f5;
            color: var(--text-color);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            padding-top: 2rem;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 2rem;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            padding: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            flex-grow: 1;
            padding: 2rem;
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s ease;
        }
        
        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background-color: white;
        }
        
        .card-header {
            background-color: var(--primary-color);
            border-bottom: none;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
            padding: 1.8rem 1.5rem 1.8rem;
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(10, 34, 70, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: var(--border-radius);
            padding: 0.85rem 1.5rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(10, 34, 70, 0.2);
        }
        
        .section-title {
            color: white;
            margin-bottom: 0;
            font-weight: 600;
            font-size: 1.75rem;
        }
        
        .form-section {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.75rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .form-section-title {
            margin-bottom: 1.25rem;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .form-section-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }
        
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .custom-file-upload {
            display: block;
            padding: 2.5rem;
            background-color: #f8f9fa;
            border: 2px dashed #cbd5e0;
            border-radius: var(--border-radius);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .custom-file-upload:hover {
            border-color: var(--primary-color);
            background-color: rgba(10, 34, 70, 0.03);
        }
        
        .custom-file-upload i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }
        
        #productImage {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }
        
        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .optgroup {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .form-select option, .form-select optgroup {
            padding: 0.5rem;
        }
        
        .form-select optgroup {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .navbar-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            width: 40px;
            height: 40px;
            z-index: 1100;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding-top: 70px;
            }
            
            .navbar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            body.sidebar-open .main-content::before {
                content: "";
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
        }
    </style>
</head>
<body>
    <button class="navbar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="dashboard-layout">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                Admin<br>Dashboard
            </div>
            <ul class="sidebar-menu">
            <a href="adminDash.php" class="nav-link" data-section="dashboard-section">Dashboard</a>
                <a href="productmang.php" class="nav-link">Product Management</a>
                <a href="admincat.php" class="nav-link">Category Management</a>
                <a href="manageseller.php" class="nav-link">Manage Sellers</a>
                <a href="#" class="nav-link" data-section="orders-section">Order Management</a>
                <a href="reports.php" class="nav-link" data-section="reports-section">Reports</a>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="dashboard-container">
                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title text-center">
                            <i class="fas fa-plus-circle me-2"></i>Add New Product
                        </h2>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['success'])) { ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                        <?php } ?>
                        
                        <?php if (isset($_SESSION['error'])) { ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php } ?>
                        
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-tags"></i> Category Information
                                </div>
                                <div class="mb-3">
                                    <label for="subcategory" class="form-label">Subcategory</label>
                                    <select class="form-select" id="subcategory" name="subcategory_id" required>
                                        <option value="">Select Subcategory</option>
                                        <?php
                                        $categories_query = "SELECT * FROM categories ORDER BY category_name";
                                        $categories_result = mysqli_query($conn, $categories_query);
                                        
                                        while($category = mysqli_fetch_assoc($categories_result)) {
                                            $subcategories_query = "SELECT * FROM subcategories WHERE category_id = '{$category['category_id']}' ORDER BY subcategory_name";
                                            $subcategories_result = mysqli_query($conn, $subcategories_query);
                                            
                                            echo "<optgroup label='" . htmlspecialchars($category['category_name']) . "'>";
                                            while($subcategory = mysqli_fetch_assoc($subcategories_result)) {
                                                echo "<option value='" . $subcategory['subcategory_id'] . "'>" . htmlspecialchars($subcategory['subcategory_name']) . "</option>";
                                            }
                                            echo "</optgroup>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-info-circle"></i> Product Details
                                </div>
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="productName" name="product_name" placeholder="Enter product name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="productDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="productDescription" name="product_description" rows="4" placeholder="Detailed product description"></textarea>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-dollar-sign"></i> Pricing & Inventory
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">Price ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                            <input type="number" class="form-control" id="price" name="price" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stockQuantity" class="form-label">Stock Quantity</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-cube"></i></span>
                                            <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" placeholder="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-ruler-combined"></i> Product Dimensions
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="length" class="form-label">Length (cm)</label>
                                        <input type="number" class="form-control" id="length" name="length" step="0.1" placeholder="0.0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="width" class="form-label">Width (cm)</label>
                                        <input type="number" class="form-control" id="width" name="width" step="0.1" placeholder="0.0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="height" class="form-label">Height (cm)</label>
                                        <input type="number" class="form-control" id="height" name="height" step="0.1" placeholder="0.0" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-tree"></i> Wood Specifications
                                </div>
                                <div class="mb-3">
                                    <label for="woodType" class="form-label">Wood Type</label>
                                    <select class="form-select" id="woodType" name="wood_type" required>
                                        <option value="">Select Wood Type</option>
                                        <option value="Oak">Oak</option>
                                        <option value="Pine">Pine</option>
                                        <option value="Maple">Maple</option>
                                        <option value="Walnut">Walnut</option>
                                        <option value="Cherry">Cherry</option>
                                        <option value="Mahogany">Mahogany</option>
                                        <option value="Birch">Birch</option>
                                        <option value="Cedar">Cedar</option>
                                        <option value="Teak">Teak</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-image"></i> Product Image
                                </div>
                                <div class="file-upload-wrapper">
                                    <label for="productImage" class="custom-file-upload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div>Drag and drop your image here or click to browse</div>
                                        <small class="text-muted">Accepted formats: JPG, PNG, GIF</small>
                                    </label>
                                    <input type="file" class="form-control" id="productImage" name="product_image" accept="image/*">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="add_product" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Add Product
                                </button>
                            </div>

                        </form>
                        <div class="card-body">
                <p>To view all added products </p>
                <a href="view_product.php" class="btn btn-primary">View all added products</a>
            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Display file name when selected
        document.getElementById('productImage').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const label = document.querySelector('.custom-file-upload');
            
            if (fileName) {
                label.innerHTML = `<i class="fas fa-file-image"></i><div>${fileName}</div>`;
            }
        });
        
        // Sidebar toggle functionality for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const body = document.body;
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggleButton = sidebarToggle.contains(event.target);
            
            if (window.innerWidth <= 768 && !isClickInsideSidebar && !isClickOnToggleButton && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        });
        
        // Adjust layout on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        });
    </script>
</body>
</html>