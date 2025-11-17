<?php
include('db_con.php');

// ---------------- SEARCH ----------------
$search = $_GET['search'] ?? '';

if ($search !== "") {
    $query = "
        SELECT 
            o.OrderID, 
            o.CustomerID, 
            o.OrderDate, 
            o.OrderStatus, 
            o.TotalAmount,
            c.Name AS CustomerName
        FROM `order` o
        JOIN customer c ON o.CustomerID = c.CustomerID
        WHERE 
            o.CustomerID LIKE '%$search%' OR
            c.Name LIKE '%$search%' OR
            c.Email LIKE '%$search%' OR
            o.OrderStatus LIKE '%$search%'
        ORDER BY o.OrderID DESC
    ";
} else {
    $query = "
        SELECT 
            o.OrderID, 
            o.CustomerID, 
            o.OrderDate, 
            o.OrderStatus, 
            o.TotalAmount,
            c.Name AS CustomerName
        FROM `order` o
        JOIN customer c ON o.CustomerID = c.CustomerID
        ORDER BY o.OrderID DESC
    ";
}

$result = mysqli_query($connection, $query);
if (!$result) die("Query failed: " . mysqli_error($connection));
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ---------------- EDIT MODE ----------------
$editData = null;
if (isset($_GET['edit'])) {
    $editID = $_GET['edit'];
    $editQuery = mysqli_query($connection, "
        SELECT o.OrderID, o.CustomerID, o.OrderStatus, o.TotalAmount, c.Name AS CustomerName
        FROM `order` o
        JOIN customer c ON o.CustomerID = c.CustomerID
        WHERE o.OrderID=$editID
    ");
    $editData = mysqli_fetch_assoc($editQuery);
}

// ---------------- ADD NEW ORDER ----------------
if (isset($_POST['save'])) {
    $CustomerID = $_POST['CustomerID'];
    $Status = $_POST['Status'];
    $Amount = $_POST['Amount'];

    // Check if customer exists
    $check = mysqli_query($connection, "SELECT * FROM customer WHERE CustomerID='$CustomerID'");
    if (mysqli_num_rows($check) > 0) {
        // Insert new order
        $insert = "
            INSERT INTO `order` (CustomerID, OrderStatus, TotalAmount)
            VALUES ('$CustomerID', '$Status', '$Amount')
        ";
        if (mysqli_query($connection, $insert)) {
            echo "<script>alert('Order added successfully for Customer ID $CustomerID!'); window.location='order.php';</script>";
            exit;
        } else {
            echo "<script>alert('Insert failed: " . mysqli_error($connection) . "');</script>";
        }
    } else {
        echo "<script>alert('Customer ID does not exist! Please enter a valid existing CustomerID.');</script>";
    }
}

// ---------------- UPDATE ORDER ----------------
if (isset($_POST['update'])) {
    $OrderID = $_POST['OrderID'];
    $CustomerID = $_POST['CustomerID'];
    $Status = $_POST['Status'];
    $Amount = $_POST['Amount'];

    // Check if customer exists
    $check = mysqli_query($connection, "SELECT * FROM customer WHERE CustomerID='$CustomerID'");
    if (mysqli_num_rows($check) > 0) {
        $update = "
            UPDATE `order` SET
                CustomerID='$CustomerID',
                OrderStatus='$Status',
                TotalAmount='$Amount'
            WHERE OrderID='$OrderID'
        ";
        if (mysqli_query($connection, $update)) {
            echo "<script>alert('Order updated successfully!'); window.location='order.php';</script>";
            exit;
        } else {
            echo "<script>alert('Update failed: " . mysqli_error($connection) . "');</script>";
        }
    } else {
        echo "<script>alert('Customer ID does not exist!');</script>";
    }
}

// ---------------- DELETE ORDER ----------------
if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    mysqli_query($connection, "DELETE FROM `order` WHERE OrderID=$deleteID");
    header("Location: order.php");
    exit;
}

// ---------------- GET ALL CUSTOMERS ----------------
$customers = mysqli_query($connection, "SELECT CustomerID, Name FROM customer ORDER BY Name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorPro - Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body class="bg-light">

<header class="navbar bg-dark p-3 mb-4 shadow-sm">
    <button class="btn btn-outline-light" onclick="window.location.href='customerorder.php'">← Back</button>
    <h3 class="text-white ms-3">✂️ TailorPro - Orders</h3>
</header>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><?= $editData ? "Edit Order" : "Order Management"; ?></h2>
            <p class="text-muted">Track and manage all tailor orders</p>
        </div>

        <?php if (!$editData): ?>
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#newOrder">+ New Order</button>
        <?php endif; ?>
    </div>

    <!-- ADD/EDIT ORDER FORM -->
    <div id="newOrder" class="collapse show mb-4">
        <div class="card card-body">
            <form method="POST">
                <?php if ($editData): ?>
                    <input type="hidden" name="OrderID" value="<?= $editData['OrderID'] ?>">
                <?php endif; ?>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Customer ID</label>
                        <input type="text" name="CustomerID" class="form-control"
                               value="<?= $editData['CustomerID'] ?? '' ?>" placeholder="Enter existing Customer ID" required>
                        <small class="text-muted">Enter an existing Customer ID to add a new order.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option <?= ($editData['OrderStatus'] ?? '') == "Pending" ? "selected" : ""; ?>>Pending</option>
                            <option <?= ($editData['OrderStatus'] ?? '') == "In Progress" ? "selected" : ""; ?>>In Progress</option>
                            <option <?= ($editData['OrderStatus'] ?? '') == "Completed" ? "selected" : ""; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="Amount" step="0.01" class="form-control"
                               value="<?= $editData['TotalAmount'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="mt-3">
                    <?php if ($editData): ?>
                        <button class="btn btn-warning" name="update">Update Order</button>
                        <a href="order.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button class="btn btn-success" name="save">Save Order</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- SEARCH BAR -->
    <form method="GET" class="mb-3">
        <div class="input-group" style="max-width: 350px;">
            <input type="text" name="search" class="form-control" placeholder="Search order..."
                   value="<?= $search ?>">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- ORDER TABLE -->
    <table class="table table-bordered table-striped bg-white">
        <thead class="table-light">
            <tr>
                <th>Order ID</th>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Amount</th>
                <th width="120">Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= $o['OrderID'] ?></td>
                    <td><?= $o['CustomerID'] ?></td>
                    <td><?= $o['CustomerName'] ?></td>
                    <td><?= $o['OrderDate'] ?></td>
                    <td><?= $o['OrderStatus'] ?></td>
                    <td>$<?= number_format($o['TotalAmount'], 2) ?></td>
                    <td>
                        <a href="order.php?edit=<?= $o['OrderID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="order.php?delete=<?= $o['OrderID'] ?>" 
                           onclick="return confirm('Delete this order?')" 
                           class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
