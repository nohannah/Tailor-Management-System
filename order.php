<?php
session_start();
include('db_con.php');

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['username'];

/* ------------------------------------
   Fetch Garment Types for Filter
------------------------------------ */
$garment_query = "SELECT GarmentTypeID, Name FROM GarmentType";
$garment_result = mysqli_query($connection, $garment_query);

/* ------------------------------------
   Read Filters
------------------------------------ */
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_garment = isset($_GET['garment']) ? $_GET['garment'] : '';

/* ------------------------------------
   Build Order Query with Filters
------------------------------------ */
$query = "SELECT o.OrderID, o.OrderDate, o.OrderStatus, o.TotalAmount,
          GROUP_CONCAT(gt.Name SEPARATOR ', ') as Garments
          FROM `Order` o
          LEFT JOIN OrderItem oi ON o.OrderID = oi.OrderID
          LEFT JOIN GarmentType gt ON oi.GarmentTypeID = gt.GarmentTypeID
          WHERE o.CustomerID = ?";

// Filter by Status
if (!empty($filter_status)) {
    $query .= " AND o.OrderStatus = '" . mysqli_real_escape_string($connection, $filter_status) . "'";
}

// Filter by Garment Type
if (!empty($filter_garment)) {
    $query .= " AND oi.GarmentTypeID = '" . mysqli_real_escape_string($connection, $filter_garment) . "'";
}

$query .= " GROUP BY o.OrderID ORDER BY o.OrderDate DESC";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Dashboard</title>
<link rel="stylesheet" href="CSS/customer_dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" type="text/css" href="CSS/home.css">

</head>
<body>
<!-- Navigation -->
<nav>
    <label class="logo">TailorPro</label>
    <ul>
        
            <a href="customer.php" class="btn btn-outline-light me-2">Home</a>
            <a href="services.php" class="btn btn-outline-light me-2">Make New Order</a>
            <a href="login.php" class="btn btn-danger">Logout</a>

    </ul>
</nav>
</nav>
<div class="container mt-4">
    <h2>Your Orders</h2>

    <!-- FILTER FORM -->
    <form method="GET" class="row mt-3 mb-4">
        <!-- Garment Filter -->
        <div class="col-md-4">
            <label>Filter by Garment</label>
            <select name="garment" class="form-control">
                <option value="">All Garments</option>
                <?php while ($g = mysqli_fetch_assoc($garment_result)) { ?>
                    <option value="<?php echo $g['GarmentTypeID']; ?>" <?php if ($filter_garment == $g['GarmentTypeID']) echo "selected"; ?>>
                        <?php echo $g['Name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <!-- Status Filter -->
        <div class="col-md-4">
            <label>Filter by Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Pending" <?php if ($filter_status == "Pending") echo "selected"; ?>>Pending</option>
                <option value="In Progress" <?php if ($filter_status == "In Progress") echo "selected"; ?>>In Progress</option>
                <option value="Completed" <?php if ($filter_status == "Completed") echo "selected"; ?>>Completed</option>
            </select>
        </div>

        <!-- Button -->
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- ORDER TABLE -->
    <?php if (mysqli_num_rows($result) > 0) { ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Garments</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['OrderID']; ?></td>
                        <td><?php echo $row['Garments']; ?></td>
                        <td><?php echo $row['OrderDate']; ?></td>
                        <td><?php echo $row['OrderStatus']; ?></td>
                        <td><?php echo number_format($row['TotalAmount'], 2); ?></td>
                        <td>
                            <?php if ($row['OrderStatus'] === 'Pending') { ?>
                                <a href="payment.php?order_id=<?php echo $row['OrderID']; ?>" class="btn btn-success btn-sm">Pay Now</a>
                            <?php } else { ?>
                                <span class="text-muted">N/A</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p class="text-muted">No orders found for selected filters.</p>
    <?php } ?>
</div>
<!-- Footer -->
<footer class="footer">
    <div class="container text-center">
        <p>&copy; 2025 TailorPro Management. All rights reserved.</p>
        <p>
            <a href="#">Privacy Policy</a> |
            <a href="#">Terms of Use</a>
        </p>
    </div>
</footer>

</body>
</html>
