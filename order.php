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
   Cancel Order Logic
------------------------------------ */
if (isset($_GET['cancel_order_id'])) {
    $cancel_order_id = intval($_GET['cancel_order_id']);

    // Get order date
    $stmt = mysqli_prepare($connection, "SELECT OrderDate FROM `Order` WHERE OrderID = ? AND CustomerID = ?");
    mysqli_stmt_bind_param($stmt, "ii", $cancel_order_id, $customer_id);
    mysqli_stmt_execute($stmt);
    $result_cancel = mysqli_stmt_get_result($stmt);

    if ($row_cancel = mysqli_fetch_assoc($result_cancel)) {
        $order_date = new DateTime($row_cancel['OrderDate']);
        $today = new DateTime();
        $diff_days = $today->diff($order_date)->days;

        if ($diff_days <= 7) {
            $stmt_update = mysqli_prepare($connection, "UPDATE `Order` SET OrderStatus = 'Cancelled' WHERE OrderID = ? AND CustomerID = ?");
            mysqli_stmt_bind_param($stmt_update, "ii", $cancel_order_id, $customer_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            $message = "Order #$cancel_order_id has been cancelled successfully.";
        } else {
            $error = "Cannot cancel orders older than 7 days.";
        }
    }
    mysqli_stmt_close($stmt);
}

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
mysqli_stmt_bind_param($stmt, "i", $customer_id);
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
</head>
<body>
<!-- Navigation -->
<nav>
    <label class="logo">TailorPro</label>
    <ul>
        <a href="customer.php" class="btn btn-outline-light me-2">Home</a>
        <a href="services.php" class="btn btn-outline-light me-2">Make New Order</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </ul>
</nav>

<div class="container mt-4">
    <h2>Your Orders</h2>

    <!-- Display Messages -->
    <?php if(isset($message)) { ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php } ?>
    <?php if(isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>

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
                <option value="Cancelled" <?php if ($filter_status == "Cancelled") echo "selected"; ?>>Cancelled</option>
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
                <?php while ($row = mysqli_fetch_assoc($result)) { 
                    $order_date = new DateTime($row['OrderDate']);
                    $today = new DateTime();
                    $diff_days = $today->diff($order_date)->days;
                ?>
                    <tr>
                        <td><?php echo $row['OrderID']; ?></td>
                        <td><?php echo $row['Garments']; ?></td>
                        <td><?php echo $row['OrderDate']; ?></td>
                        <td><?php echo $row['OrderStatus']; ?></td>
                        <td><?php echo number_format($row['TotalAmount'], 2); ?></td>
                        <td>
                            <?php 
                            if ($row['OrderStatus'] === 'Pending') { 
                                echo '<a href="payment.php?order_id=' . $row['OrderID'] . '" class="btn btn-success btn-sm">Pay Now</a> ';
                            }
                            if ($row['OrderStatus'] !== 'Cancelled' && $diff_days <= 7) {
                                echo '<a href="?cancel_order_id=' . $row['OrderID'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to cancel this order?\');">Cancel</a>';
                            } elseif ($diff_days > 7 && $row['OrderStatus'] !== 'Cancelled') {
                                echo '<span class="text-muted">Cancel unavailable</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p class="text-muted">No orders found for selected filters.</p>
    <?php } ?>
</div>

</body>
</html>