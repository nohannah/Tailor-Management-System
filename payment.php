<?php
session_start();
include('db_con.php');

if (!isset($_GET['order_id'])) {
    header("Location: services.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$stmt = mysqli_prepare($connection, "SELECT * FROM `Order` WHERE OrderID = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("Order not found.");
}

// Handle form submission
if (isset($_POST['pay_now'])) {
    // Update order status to 'In Progress' since customer paid
    $stmt = mysqli_prepare($connection, "UPDATE `Order` SET OrderStatus='In Progress' WHERE OrderID=?");
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>alert('Payment successful! Order is now In Progress.'); window.location='order.php';</script>";
    exit();
}

if (isset($_POST['pay_later'])) {
    // Keep order status as 'Pending'
    echo "<script>alert('Payment deferred. Order remains Pending.'); window.location='order.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Payment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container { max-width: 500px; margin-top: 80px; }
.card { padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h3 class="mb-4">Payment for Order #<?= htmlspecialchars($order['OrderID']); ?></h3>
        <p><strong>Customer ID:</strong> <?= htmlspecialchars($order['CustomerID']); ?></p>
        <p><strong>Total Amount:</strong> $<?= number_format($order['TotalAmount'], 2); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['OrderStatus']); ?></p>

        <form method="POST">
            <div class="d-flex gap-2">
                <button type="submit" name="pay_now" class="btn btn-success flex-fill">Pay Now</button>
                <button type="submit" name="pay_later" class="btn btn-secondary flex-fill">Pay Later</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
