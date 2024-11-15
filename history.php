<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Variable to store the result for each join query
$leftJoinResult = $rightJoinResult = $innerJoinResult = null;

// Handle the button clicks to perform the respective joins
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["left_join"])) {
        // LEFT JOIN: Fetch user data along with transactions (if any)
        $sql = "SELECT users.id AS user_id, users.username, transactions.product_id, transactions.quantity, transactions.transaction_date
                FROM users
                LEFT JOIN transactions ON users.id = transactions.user_id";
        $leftJoinResult = $pdo->query($sql);
    } elseif (isset($_POST["right_join"])) {
        // RIGHT JOIN: Fetch products data along with user transactions (if any)
        $sql = "SELECT products.id AS product_id, products.name AS product_name, transactions.user_id, transactions.quantity, transactions.transaction_date
                FROM products
                RIGHT JOIN transactions ON products.id = transactions.product_id";
        $rightJoinResult = $pdo->query($sql);
    } elseif (isset($_POST["inner_join"])) {
        // INNER JOIN: Fetch user transactions with both user and product info
        $sql = "SELECT users.username, products.name AS product_name, transactions.quantity, transactions.transaction_date
                FROM transactions
                INNER JOIN users ON transactions.user_id = users.id
                INNER JOIN products ON transactions.product_id = products.id";
        $innerJoinResult = $pdo->query($sql);
    }
}

unset($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History - AutoParts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container { margin-top: 50px; }
        .btn-join { margin-right: 10px; }
        table { margin-top: 20px; }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">AutoParts</a>
    <div class="navbar-nav ml-auto">
        <a class="nav-item nav-link" href="dashboard.php">Dashboard</a>
        <a class="nav-item nav-link" href="logout.php">Logout</a>
    </div>
</nav>

<!-- History Section -->
<div class="container">
    <h2>User History</h2>
    <p>Select a join type to view data:</p>

    <!-- Buttons for JOIN Types -->
    <form method="post">
        <button type="submit" name="left_join" class="btn btn-primary btn-join">Left Join</button>
        <button type="submit" name="right_join" class="btn btn-primary btn-join">Right Join</button>
        <button type="submit" name="inner_join" class="btn btn-primary btn-join">Inner Join</button>
    </form>

    <!-- LEFT JOIN Table: User Information with Transactions -->
    <?php if ($leftJoinResult): ?>
        <h3>Left Join: User Information with Transactions</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $leftJoinResult->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['product_id'] ? $row['product_id'] : 'No Product'; ?></td>
                        <td><?php echo $row['quantity'] ? $row['quantity'] : 'N/A'; ?></td>
                        <td><?php echo $row['transaction_date'] ? $row['transaction_date'] : 'No Transaction'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- RIGHT JOIN Table: Product Information with Transactions -->
    <?php if ($rightJoinResult): ?>
        <h3>Right Join: Product Information with Transactions</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>User ID</th>
                    <th>Quantity</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $rightJoinResult->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['user_id'] ? $row['user_id'] : 'No User'; ?></td>
                        <td><?php echo $row['quantity'] ? $row['quantity'] : 'N/A'; ?></td>
                        <td><?php echo $row['transaction_date'] ? $row['transaction_date'] : 'No Transaction'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- INNER JOIN Table: User Transactions -->
    <?php if ($innerJoinResult): ?>
        <h3>Inner Join: User Transactions</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $innerJoinResult->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['transaction_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
