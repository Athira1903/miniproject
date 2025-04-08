<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            margin: 0;
            padding: 40px 20px;
            color: #fff;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            color: #ffffff;
            letter-spacing: 1px;
        }

        .add-product-form {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .add-product-form h2, h3 {
            text-align: center;
            color: #2a5298;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .add-product-form label {
            display: block;
            margin-bottom: 8px;
            color: #1e3c72;
            font-weight: 500;
            font-size: 0.95em;
        }

        .add-product-form input,
        .add-product-form select,
        .add-product-form textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1em;
            color: #2c3e50;
            background: #f8f9fa;
        }

        .add-product-form input:focus,
        .add-product-form select:focus,
        .add-product-form textarea:focus {
            border-color: #2a5298;
            outline: none;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.2);
            background: #ffffff;
        }

        .add-product-form input[type="submit"] {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
            padding: 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-top: 20px;
        }

        .add-product-form input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .add-product-form input[type="file"] {
            background: #ffffff;
            padding: 10px;
            border: 2px dashed #2a5298;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group .subcategory-label {
            display: block;
            margin-top: 15px;
            color: #1e3c72;
        }

        /* Error message styling */
        .error-message {
            background-color: #fee;
            color: #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .add-product-form {
                padding: 25px;
            }
            
            h1 {
                font-size: 2em;
            }
        }

        /* Add smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Add animation for form appearance */
        .add-product-form {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        function updateSubcategories() {
            const category = document.getElementById("category").value;
            const subcategorySelect = document.getElementById("subcategory");
            subcategorySelect.innerHTML = "";

            let subcategories = [];

            switch (category) {
                case "Furniture":
                    subcategories = ["Sofa", "Chair", "Table", "Cabinet", "Bed", "Desk", "Bookshelf", "Dresser", "Nightstand", "Recliner", "Dining Set", "Office Chair", "Coffee Table", "Wardrobe"];
                    break;
                case "Outdoor Furniture":
                    subcategories = ["Patio Set", "Lounge Chair", "Umbrella", "Outdoor Dining Table", "Garden Bench", "Hammock", "Fire Pit", "Outdoor Storage"];
                    break;
                case "Office Furniture":
                    subcategories = ["Office Desk", "Ergonomic Chair", "Filing Cabinet", "Bookcase", "Conference Table", "Cubicle Partition", "Reception Desk", "Office Storage"];
                    break;
                case "Kids Furniture":
                    subcategories = ["Bunk Bed", "Toy Chest", "Kids Desk", "Rocking Chair", "Play Table", "Bookshelf", "Kids Chair", "Crib"];
                    break;
            }

            subcategories.forEach(function(subcategory) {
                const option = document.createElement("option");
                option.value = subcategory;
                option.textContent = subcategory;
                subcategorySelect.appendChild(option);
            });
        }
    </script>
    <!-- Add Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Add a New Product</h1>
    <div class="add-product-form">
        <?php
        // Add this at the top of the form to display errors

        if(isset($_SESSION['error'])) {
            echo '<div style="color: red;">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="submit_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="stock">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" required>
            </div>

            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" onchange="updateSubcategories()" required>
                    <option value="">Select a category</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Outdoor Furniture">Outdoor Furniture</option>
                    <option value="Office Furniture">Office Furniture</option>
                    <option value="Kids Furniture">Kids Furniture</option>
                </select>
            </div>

            <div class="form-group">
                <label class="subcategory-label" for="subcategory">Subcategory:</label>
                <select id="subcategory" name="subcategory" required>
                    <option value="">Select a subcategory</option>
                </select>
            </div>

            <h3>Dimensions</h3>
            <div class="form-group">
                <label for="width">Width:</label>
                <input type="number" id="width" name="width" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="height">Height:</label>
                <input type="number" id="height" name="height" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="depth">Depth:</label>
                <input type="number" id="depth" name="depth" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="measurement_unit">Measurement Unit:</label>
                <select id="measurement_unit" name="measurement_unit" required>
                    <option value="cm">Centimeters (cm)</option>
                    <option value="in">Inches (in)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="wood_type">Wood Type:</label>
                <select id="wood_type" name="wood_type" required>
                    <option value="">Select wood type</option>
                    <option value="Oak">Oak</option>
                    <option value="Pine">Pine</option>
                    <option value="Maple">Maple</option>
                    <option value="Walnut">Walnut</option>
                    <option value="Mahogany">Mahogany</option>
                    <option value="Cherry">Cherry</option>
                    <option value="Birch">Birch</option>
                    <option value="Teak">Teak</option>
                </select>
            </div>

            <input type="submit" value="Add Product">
        </form>
    </div>
</body>
</html>
