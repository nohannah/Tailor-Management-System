<?php
session_start();
include('db_con.php');

/* -------------------------------------------
   BLOCK BACK BUTTON AFTER LOGOUT
-------------------------------------------- */
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache");
header("Expires: 0");

/* -------------------------------------------
   CORRECT SESSION CHECK
-------------------------------------------- */
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

/* -------------------------------------------
   VALIDATE GARMENT ID
-------------------------------------------- */
if (!isset($_GET['garment_id'])) {
    header("Location: services.php");
    exit();
}

$garment_id = intval($_GET['garment_id']);

function float_or_null($val) {
    if ($val === null) return null;
    $val = trim($val);
    if ($val === '') return null;
    return floatval($val);
}

/* -------------------------------------------
   GET GARMENT NAME
-------------------------------------------- */
$stmt = mysqli_prepare($connection, "SELECT Name FROM GarmentType WHERE GarmentTypeID = ?");
mysqli_stmt_bind_param($stmt, "i", $garment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$garment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

/* -------------------------------------------
   GET PREVIOUS MEASUREMENTS
-------------------------------------------- */
$stmt = mysqli_prepare($connection, "SELECT * FROM Measurement WHERE CustomerID = ? ORDER BY MeasurementDate DESC");
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$measurements = [];
while ($row = mysqli_fetch_assoc($res)) {
    $measurements[] = $row;
}

$latest_measurement = $measurements[0] ?? null;

/* -------------------------------------------
   HANDLE FORM SUBMISSION
-------------------------------------------- */
if (isset($_POST['submit'])) {

    $measurement_choice = $_POST['measurement_choice'] ?? ($latest_measurement ? 'm_'.$latest_measurement['MeasurementID'] : 'new');

    if (strpos($measurement_choice, "m_") === 0) {

        $measurement_id = intval(substr($measurement_choice, 2));

    } else {

        $Height = float_or_null($_POST['Height'] ?? null);
        $Chest = float_or_null($_POST['Chest'] ?? null);
        $Waist = float_or_null($_POST['Waist'] ?? null);
        $Hips = float_or_null($_POST['Hips'] ?? null);
        $ShoulderWidth = float_or_null($_POST['ShoulderWidth'] ?? null);
        $SleeveLength = float_or_null($_POST['SleeveLength'] ?? null);
        $Neck = float_or_null($_POST['Neck'] ?? null);
        $Inseam = float_or_null($_POST['Inseam'] ?? null);
        $Thigh = float_or_null($_POST['Thigh'] ?? null);

        if ($latest_measurement) {
            $Height = $Height ?? $latest_measurement['Height'];
            $Chest = $Chest ?? $latest_measurement['Chest'];
            $Waist = $Waist ?? $latest_measurement['Waist'];
            $Hips = $Hips ?? $latest_measurement['Hips'];
            $ShoulderWidth = $ShoulderWidth ?? $latest_measurement['ShoulderWidth'];
            $SleeveLength = $SleeveLength ?? $latest_measurement['SleeveLength'];
            $Neck = $Neck ?? $latest_measurement['Neck'];
            $Inseam = $Inseam ?? $latest_measurement['Inseam'];
            $Thigh = $Thigh ?? $latest_measurement['Thigh'];

            $measurement_id = $latest_measurement['MeasurementID'];

            $stmt = mysqli_prepare($connection, "UPDATE Measurement SET 
                Height=?, Chest=?, Waist=?, Hips=?, ShoulderWidth=?, SleeveLength=?, Neck=?, Inseam=?, Thigh=?, 
                MeasurementDate=NOW()
                WHERE MeasurementID=?");
            
            mysqli_stmt_bind_param($stmt, "dddddddddi",
                $Height, $Chest, $Waist, $Hips, $ShoulderWidth, $SleeveLength, $Neck, $Inseam, $Thigh, $measurement_id
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

        } else {

            $stmt = mysqli_prepare($connection, "INSERT INTO Measurement
                (CustomerID, MeasurementDate, Height, Chest, Waist, Hips, ShoulderWidth, SleeveLength, Neck, Inseam, Thigh)
                VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            mysqli_stmt_bind_param($stmt, "sddddddddd",
                $customer_id,
                $Height, $Chest, $Waist, $Hips, $ShoulderWidth, $SleeveLength, $Neck, $Inseam, $Thigh
            );

            mysqli_stmt_execute($stmt);
            $measurement_id = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);
        }
    }

    /* -------------------------------------------
       INSERT ORDER
    -------------------------------------------- */
    $quantity = 1;
    $unit_price = 500.00;
    $item_total = $quantity * $unit_price;

    $stmt = mysqli_prepare($connection, "INSERT INTO `Order` (CustomerID, OrderDate, OrderStatus, TotalAmount)
        VALUES (?, NOW(), 'Pending', ?)");
    mysqli_stmt_bind_param($stmt, "sd", $customer_id, $item_total);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($connection);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($connection, "INSERT INTO OrderItem
        (OrderID, GarmentTypeID, MeasurementID, Quantity, UnitPrice, ItemTotal)
        VALUES (?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param($stmt, "iiiidd",
        $order_id, $garment_id, $measurement_id, $quantity, $unit_price, $item_total
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "
    <script>
        if (confirm('Order created successfully! Proceed to payment?')) {
            window.location = 'payment.php?order_id=$order_id';
        } else {
            window.location = 'services.php';
        }
    </script>";

    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Create Order</title>
<link rel="stylesheet" href="CSS/service.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<!-- NAVBAR -->
<nav class="d-flex align-items-center justify-content-between bg-dark p-3">
    <label class="logo text-white fs-3 fw-bold">TailorPro</label>
    <ul class="d-flex align-items-center mb-0" style="list-style:none; gap:15px">
        <li><a href="home.php" class="text-white">Home</a></li>
        <li><a href="services.php" class="text-white">Services</a></li>
        <li><a href="logout.php" class="btn btn-danger btn-sm">Logout</a></li>
    </ul>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Place Order for: <?= htmlspecialchars($garment['Name'] ?? 'Garment'); ?></h2>

    <!-- YOUR FORM REMAINS SAME BELOW -->
    <!-- (kept unchanged to avoid breaking your layout) -->

    <!-- ---------------- FORM START ---------------- -->
    <form action="" method="POST" class="register_form bg-white p-4 rounded shadow-sm">
        <!-- Your measurement fields (unchanged) -->
        <?php /* keeping your form same */ ?>
