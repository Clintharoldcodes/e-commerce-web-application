<?php
// Initialize the session
session_start();

// Check if the user is logged in; if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file for database connection
require_once "config.php";

// Fetch products from the database
$products = [];
$sql = "SELECT id, name, description, quantity, price, image FROM products LIMIT 6";
if ($result = $pdo->query($sql)) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $products[] = $row;
    }
}

// Handle Buy button functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $product_id = $_POST["product_id"];
    $user_id = $_SESSION["id"];
    $quantity = 1; // Default purchase quantity for simplicity

    // First, check if the product is available in stock
    $sql = "SELECT quantity FROM products WHERE id = :product_id";
    if ($stmt = $pdo->prepare($sql)) {
        // Bind product ID
        $stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);

        // Execute and fetch the product's current stock quantity
        if ($stmt->execute()) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product && $product["quantity"] > 0) {
                // Proceed to insert the transaction

                // Insert transaction into the transactions table
                $sql = "INSERT INTO transactions (user_id, product_id, quantity, transaction_date) 
                        VALUES (:user_id, :product_id, :quantity, NOW())";
                if ($stmt = $pdo->prepare($sql)) {
                    // Bind parameters for transaction
                    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
                    $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);

                    // Execute statement to insert the transaction
                    if ($stmt->execute()) {
                        // Update the stock quantity after purchase
                        $new_quantity = $product["quantity"] - $quantity;
                        $update_sql = "UPDATE products SET quantity = :quantity WHERE id = :product_id";
                        if ($update_stmt = $pdo->prepare($update_sql)) {
                            // Bind parameters for stock update
                            $update_stmt->bindParam(":quantity", $new_quantity, PDO::PARAM_INT);
                            $update_stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);

                            // Execute stock update
                            $update_stmt->execute();
                        }

                        // Set success message
                        $message = "Product purchased successfully!";
                        $messageType = "success";
                    } else {
                        // Set error message
                        $message = "Something went wrong. Please try again later.";
                        $messageType = "danger";
                    }
                }
            } else {
                // Set out of stock message
                $message = "Sorry, this product is out of stock.";
                $messageType = "danger";
            }
        }
    }
}

unset($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AutoParts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #000000; /* Set black background */
            color: #fff; /* Set text color to white to contrast against black background */
        }
        .container { margin-top: 50px; }
        .product-card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; background-color: #333; }
        .product-image {
    width: 100%;       /* Set the width to the full width of the container */
    height: 200px;     /* Fixed height to make all images the same size */
    object-fit: cover; /* Maintain aspect ratio, crop if necessary */
    border-radius: 8px; /* Optional: add rounded corners */
}



        .btn-buy { background-color: #3399ff; color: #fff; border: none; }
        .btn-buy:hover { background-color: #66ccff; }
        .alert { position: fixed; top: 20px; right: 20px; width: 300px; z-index: 9999; }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">AutoParts</a>
    <div class="navbar-nav ml-auto">
        <a class="nav-item nav-link" href="history.php">History</a>
        <a class="nav-item nav-link" href="logout.php">Logout</a>
    </div>
</nav>

<!-- Product Display -->
<div class="container">
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4">
                <div class="product-card">
                    <?php if ($product["image"]): ?>
                        <img src="<?php echo $product["image"]; ?>" alt="<?php echo htmlspecialchars($product["name"]); ?>" class="product-image">
                    <?php else: ?>
                        <img src="default.jpg" alt="Default Image" class="product-image">
                    <?php endif; ?>
                    <h5><?php echo htmlspecialchars($product["name"]); ?></h5>
                    <p><?php echo htmlspecialchars($product["description"]); ?></p>
                    <p>Available Quantity: <?php echo $product["quantity"]; ?></p>
                    <p>Price: $<?php echo number_format($product["price"], 2); ?></p>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product["id"]; ?>">
                        <button type="submit" class="btn btn-buy btn-block">Buy</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Show the message after purchase -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
            <?php echo $message; ?>
        </div>

        <script>
            // Hide the alert after 2 seconds
            setTimeout(function() {
                document.querySelector('.alert').style.display = 'none';
            }, 2000);
        </script>
    <?php endif; ?>

</div>

</body>
</html>
