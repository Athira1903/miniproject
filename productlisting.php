<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }

        #product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .product img {
            max-width: 100%;
            border-radius: 8px;
            height: 200px; /* Fixed height for uniformity */
            object-fit: cover; /* Ensures the image covers the area */
        }

        .product h2 {
            font-size: 1.5em;
            color: #343a40;
            margin: 10px 0;
        }

        .product p {
            color: #6c757d;
            margin: 5px 0;
        }

        .product .price {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
        }

        .product .category {
            font-style: italic;
            color: #007bff;
        }

        .product .subcategory {
            color: #6c757d;
        }

        @media (max-width: 768px) {
            #product-list {
                grid-template-columns: 1fr; /* Stack items on smaller screens */
            }
        }

        .btn {
            background-color:rgb(2, 28, 54); /* Bootstrap primary color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
    </style>
</head>
<body>
    <h1>Available Products</h1>
    <div id="product-list">
        <?php
        include("db.php");

        // Fetch products from the seller_products table
        $query = "SELECT * FROM seller_product";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="product">';
                echo '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                echo '<p class="price">Price: $' . htmlspecialchars($row['price']) . '</p>';
                echo '<p class="category">category: ' . htmlspecialchars($row['category']) . '</p>';
                echo '<p class="subcategory">Subcategory: ' . htmlspecialchars($row['subcategory']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No products available.</p>';
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </div>
    <button class="btn" onclick="window.location.href='sellerdashboard.php'" style="margin: 20px auto; display: block;">Go to Seller Dashboard</button>
</body>
</html>