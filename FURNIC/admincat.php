<?php
include("db.php");
session_start();

// Process Category Addition
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_description = mysqli_real_escape_string($conn, $_POST['category_description']);
    
    $query = "INSERT INTO categories (category_name, category_description) 
              VALUES ('$category_name', '$category_description')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Category added successfully";
    } else {
        $_SESSION['error'] = "Error adding category: " . mysqli_error($conn);
    }
    header("Location: admincat.php");
    exit();
}

// Process Subcategory Addition
if (isset($_POST['add_subcategory'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $subcategory_name = mysqli_real_escape_string($conn, $_POST['subcategory_name']);
    $subcategory_description = mysqli_real_escape_string($conn, $_POST['subcategory_description']);
    
    $query = "INSERT INTO subcategories (category_id, subcategory_name, subcategory_description) 
              VALUES ('$category_id', '$subcategory_name', '$subcategory_description')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Subcategory added successfully";
    } else {
        $_SESSION['error'] = "Error adding subcategory: " . mysqli_error($conn);
    }
    header("Location: admincat.php");
    exit();
}

// Process Category Deletion
if (isset($_POST['delete_category'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    // First, delete all subcategories associated with this category
    $delete_subcategories_query = "DELETE FROM subcategories WHERE category_id = '$category_id'";
    mysqli_query($conn, $delete_subcategories_query); // Execute the delete query for subcategories

    // Now delete the category
    $query = "DELETE FROM categories WHERE category_id = '$category_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Category deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting category: " . mysqli_error($conn);
    }
    header("Location: admincat.php");
    exit();
}

// Process Subcategory Deletion
if (isset($_POST['delete_subcategory'])) {
    $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
    
    // First, delete all products associated with this subcategory
    $delete_products_query = "DELETE FROM products WHERE subcategory_id = '$subcategory_id'";
    
    // Execute the delete query for products
    if (mysqli_query($conn, $delete_products_query)) {
        // Now delete the subcategory
        $query = "DELETE FROM subcategories WHERE subcategory_id = '$subcategory_id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Subcategory deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting subcategory: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Error deleting products: " . mysqli_error($conn);
    }
    
    header("Location: admincat.php");
    exit();
}

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
    <title>Admin Dashboard - Category Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background for contrast */
            font-family: 'Arial', sans-serif; /* Clean font */
        }

        .sidebar {
            background: #343a40; /* Dark sidebar */
            color: white;
            padding: 20px;
            height: 100vh; /* Full height */
            position: fixed; /* Fixed sidebar */
            width: 250px; /* Fixed width */
            transition: background 0.3s; /* Smooth transition for background */
        }

        .sidebar a {
            color: #ffffff; /* White text */
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s; /* Smooth background change */
        }

        .sidebar a:hover {
            background: #495057; /* Lighter background on hover */
        }

        .main-content {
            margin-left: 250px; /* Space for sidebar */
            padding: 20px; /* Padding for content */
            transition: margin-left 0.3s; /* Smooth transition for margin */
        }

        .card {
            border: none; /* No border */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            margin-bottom: 20px; /* Space between cards */
            transition: box-shadow 0.3s; /* Smooth shadow transition */
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
        }

        .card-header {
            background:#063358; /* Primary color header */
            color: white; /* White text */
            border-radius: 10px 10px 0 0; /* Rounded top corners */
        }

        .btn-primary {
            background: #063358; /* Primary button color */
            border: none; /* No border */
            border-radius: 20px; /* Rounded button */
            transition: background 0.3s, transform 0.3s; /* Smooth background and transform change */
        }

        .btn-primary:hover {
            background: #063358; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        .form-label {
            font-weight: bold; /* Bold labels */
        }

        .form-control {
            border-radius: 5px; /* Rounded input fields */
            transition: border-color 0.3s; /* Smooth border color change */
        }

        .form-control:focus {
            border-color: #007bff; /* Change border color on focus */
        }

        .alert {
            border-radius: 5px; /* Rounded alert boxes */
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%; /* Full width on small screens */
                position: relative; /* Relative positioning */
                height: auto; /* Auto height */
            }

            .main-content {
                margin-left: 0; /* No margin on small screens */
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <nav>
        <a href="#" class="nav-link" id="dashboardLink">Dashboard</a>
                <a href="#" class="nav-link" id="manageUsersLink">Manage Users</a>
                <a href="productmang.php" class="nav-link" id="manageUsersLink">product</a>
                <a href="admincat.php" class="nav-link" id="manageUsersLink">category</a>
                <a href="#" class="nav-link">Orders</a>
                <a href="#" class="nav-link">Reports</a>
                <a href="#" class="nav-link">Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Category Management</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Add Category Form -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Category</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="categoryName" name="category_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="categoryDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="categoryDescription" name="category_description" rows="2"></textarea>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Subcategory Form -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Subcategory</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="parentCategory" class="form-label">Parent Category</label>
                                <select class="form-select" id="parentCategory" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    mysqli_data_seek($categories_result, 0);
                                    while($category = mysqli_fetch_assoc($categories_result)): 
                                    ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subcategoryName" class="form-label">Subcategory Name</label>
                                <input type="text" class="form-control" id="subcategoryName" name="subcategory_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="subcategoryDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="subcategoryDescription" name="subcategory_description" rows="2"></textarea>
                            </div>
                            <button type="submit" name="add_subcategory" class="btn btn-primary">Add Subcategory</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories and Subcategories List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Current Categories & Subcategories</h5>
            </div>
            <div class="card-body">
                <p>To view all categories and subcategories, click the link below:</p>
                <a href="view_categories.php" class="btn btn-primary">View Categories & Subcategories</a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSubcategories(categoryId) {
        var subcategoryList = document.getElementById('subcategories-' + categoryId);
        if (subcategoryList.style.display === "none") {
            subcategoryList.style.display = "block";
        } else {
            subcategoryList.style.display = "none";
        }
    }
    </script>
</body>
</html>