<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_product.php');
    exit();
}

// Update the sanitization methods
$title = htmlspecialchars(trim($_POST['title']));
$description = htmlspecialchars(trim($_POST['description']));
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
$category = htmlspecialchars(trim($_POST['category']));
$subcategory = htmlspecialchars(trim($_POST['subcategory']));
$width = filter_input(INPUT_POST, 'width', FILTER_VALIDATE_FLOAT);
$height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT);
$depth = filter_input(INPUT_POST, 'depth', FILTER_VALIDATE_FLOAT);
$measurement_unit = htmlspecialchars(trim($_POST['measurement_unit']));

// Validate required fields
if (!$title || !$price || !$stock || !$category || !$subcategory || 
    !$width || !$height || !$depth || !$measurement_unit) {
    $_SESSION['error'] = "All fields are required";
    header('Location: add_product.php');
    exit();
}

// Handle image upload
$image_path = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
        header('Location: add_product.php');
        exit();
    }

    $upload_dir = 'uploads/products/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $image_path = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
        $_SESSION['error'] = "Failed to upload image";
        header('Location: add_product.php');
        exit();
    }
}

try {
    // Add connection check
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Insert product into database
    $sql = "INSERT INTO seller_product (title, description, price, image, category, subcategory, 
            width, height, depth, measurement_unit, stock, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssdsssdddsii", 
        $title, $description, $price, $image_path, $category, $subcategory,
        $width, $height, $depth, $measurement_unit, $stock, $_SESSION['user_id']
    );
    
    if(mysqli_stmt_execute($stmt)) {
        // Replace the old alert with SweetAlert2
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <style>
                /* Sweet Alert 2 Custom Styles */
                .swal2-popup {
                    border-radius: 15px !important;
                    padding: 2em !important;
                }

                .swal2-title {
                    color: #333 !important;
                    font-size: 1.8em !important;
                }

                .swal2-text {
                    color: #666 !important;
                }

                .swal2-confirm {
                    padding: 12px 30px !important;
                    font-size: 1.1em !important;
                    border-radius: 8px !important;
                }

                /* Animation */
                @keyframes fadeInDown {
                    from {
                        opacity: 0;
                        transform: translate3d(0, -20%, 0);
                    }
                    to {
                        opacity: 1;
                        transform: translate3d(0, 0, 0);
                    }
                }

                .animated {
                    animation-duration: 0.3s;
                    animation-fill-mode: both;
                }

                .fadeInDown {
                    animation-name: fadeInDown;
                }
            </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Product added successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#5C4033',
                    customClass: {
                        popup: 'animated fadeInDown'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'add_product.php';
                    }
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    // If database insertion fails, delete uploaded image
    if (!empty($image_path) && file_exists($image_path)) {
        unlink($image_path);
    }
    
    // Update error handling to use SweetAlert2 as well
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            /* Sweet Alert 2 Custom Styles */
            .swal2-popup {
                border-radius: 15px !important;
                padding: 2em !important;
            }

            .swal2-title {
                color: #333 !important;
                font-size: 1.8em !important;
            }

            .swal2-text {
                color: #666 !important;
            }

            .swal2-confirm {
                padding: 12px 30px !important;
                font-size: 1.1em !important;
                border-radius: 8px !important;
            }

            /* Animation */
            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translate3d(0, -20%, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }

            .animated {
                animation-duration: 0.3s;
                animation-fill-mode: both;
            }

            .fadeInDown {
                animation-name: fadeInDown;
            }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo addslashes($e->getMessage()); ?>',
                icon: 'error',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#dc3545',
                customClass: {
                    popup: 'animated fadeInDown'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'add_product.php';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>