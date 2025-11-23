<?php
session_start();
include('db_con.php');

if (!isset($_SESSION['username']) || !isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

if (!isset($_GET['garment_id'])) {
    header("Location: services.php");
    exit();
}

$garment_id = intval($_GET['garment_id']);

// helper: convert posted numeric value to float or null
function float_or_null($val) {
    if ($val === null) return null;
    $val = trim($val);
    if ($val === '') return null;
    // Use floatval to cast; will produce 0.0 for "0" or numeric strings
    return floatval($val);
}

// Get garment name
$stmt = mysqli_prepare($connection, "SELECT Name FROM GarmentType WHERE GarmentTypeID = ?");
mysqli_stmt_bind_param($stmt, "i", $garment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$garment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch all measurements for this customer (latest first)
$stmt = mysqli_prepare($connection, "SELECT * FROM Measurement WHERE CustomerID = ? ORDER BY MeasurementDate DESC");
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$measurements = [];
while ($row = mysqli_fetch_assoc($res)) {
    $measurements[] = $row;
}
mysqli_stmt_close($stmt);

$latest_measurement = $measurements[0] ?? null;

// If form submitted
if (isset($_POST['submit'])) {

    // Measurement choice: either "m_{id}" for existing measurement, or "new" for edit/insert
    $measurement_choice = $_POST['measurement_choice'] ?? ($latest_measurement ? 'm_' . $latest_measurement['MeasurementID'] : 'new');

    if (strpos($measurement_choice, 'm_') === 0) {
        // Use an existing measurement (user selected a previous measurement radio)
        $measurement_id = intval(substr($measurement_choice, 2));
    } else {
        // User wants to provide new/edited measurement. Read posted inputs and handle blanks.
        $Height = float_or_null($_POST['Height'] ?? null);
        $Chest = float_or_null($_POST['Chest'] ?? null);
        $Waist = float_or_null($_POST['Waist'] ?? null);
        $Hips = float_or_null($_POST['Hips'] ?? null);
        $ShoulderWidth = float_or_null($_POST['ShoulderWidth'] ?? null);
        $SleeveLength = float_or_null($_POST['SleeveLength'] ?? null);
        $Neck = float_or_null($_POST['Neck'] ?? null);
        $Inseam = float_or_null($_POST['Inseam'] ?? null);
        $Thigh = float_or_null($_POST['Thigh'] ?? null);

        // If there's a latest measurement, and the user left some fields blank, keep old values for those blanks.
        if ($latest_measurement) {
            $Height = $Height ?? ($latest_measurement['Height'] !== null ? floatval($latest_measurement['Height']) : null);
            $Chest = $Chest ?? ($latest_measurement['Chest'] !== null ? floatval($latest_measurement['Chest']) : null);
            $Waist = $Waist ?? ($latest_measurement['Waist'] !== null ? floatval($latest_measurement['Waist']) : null);
            $Hips = $Hips ?? ($latest_measurement['Hips'] !== null ? floatval($latest_measurement['Hips']) : null);
            $ShoulderWidth = $ShoulderWidth ?? ($latest_measurement['ShoulderWidth'] !== null ? floatval($latest_measurement['ShoulderWidth']) : null);
            $SleeveLength = $SleeveLength ?? ($latest_measurement['SleeveLength'] !== null ? floatval($latest_measurement['SleeveLength']) : null);
            $Neck = $Neck ?? ($latest_measurement['Neck'] !== null ? floatval($latest_measurement['Neck']) : null);
            $Inseam = $Inseam ?? ($latest_measurement['Inseam'] !== null ? floatval($latest_measurement['Inseam']) : null);
            $Thigh = $Thigh ?? ($latest_measurement['Thigh'] !== null ? floatval($latest_measurement['Thigh']) : null);

            // Update the existing latest measurement with provided/kept values
            $measurement_id_to_update = $latest_measurement['MeasurementID'];
            $stmt = mysqli_prepare($connection, "UPDATE Measurement SET 
                Height=?, Chest=?, Waist=?, Hips=?, ShoulderWidth=?, SleeveLength=?, Neck=?, Inseam=?, Thigh=?, MeasurementDate=NOW() 
                WHERE MeasurementID=?");
            // types: 9 doubles followed by integer (measurement id)
            mysqli_stmt_bind_param($stmt, "dddddddddi",
                $Height, $Chest, $Waist, $Hips, $ShoulderWidth, $SleeveLength, $Neck, $Inseam, $Thigh, $measurement_id_to_update
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $measurement_id = $measurement_id_to_update;
        } else {
            // No previous measurement -> insert new one
            $stmt = mysqli_prepare($connection, "INSERT INTO Measurement
                (CustomerID, MeasurementDate, Height, Chest, Waist, Hips, ShoulderWidth, SleeveLength, Neck, Inseam, Thigh)
                VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // types: s + 9 doubles => total 10
            mysqli_stmt_bind_param($stmt, "sddddddddd",
                $customer_id,
                $Height, $Chest, $Waist, $Hips, $ShoulderWidth, $SleeveLength, $Neck, $Inseam, $Thigh
            );
            mysqli_stmt_execute($stmt);
            $measurement_id = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);
        }
    }

    // At this point, $measurement_id should be set (either chosen existing or newly created/updated)
    if (empty($measurement_id)) {
        // safety fallback
        die("Measurement processing failed. Please try again.");
    }

    // Insert order
    $quantity = 1;
    $unit_price = 500.00;
    $item_total = $quantity * $unit_price;

    $stmt = mysqli_prepare($connection, "INSERT INTO `Order` (CustomerID, OrderDate, OrderStatus, TotalAmount) VALUES (?, NOW(), 'Pending', ?)");
    mysqli_stmt_bind_param($stmt, "sd", $customer_id, $item_total);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($connection);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($connection, "INSERT INTO OrderItem (OrderID, GarmentTypeID, MeasurementID, Quantity, UnitPrice, ItemTotal) VALUES (?, ?, ?, ?, ?, ?)");
    // types: i i i i d d  -> use "iiiidd" but OrderID, GarmentTypeID, MeasurementID, Quantity are ints, unit price and item total are doubles
    mysqli_stmt_bind_param($stmt, "iiiidd", $order_id, $garment_id, $measurement_id, $quantity, $unit_price, $item_total);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>
        if(confirm('Order created successfully! Do you want to proceed to payment?')) {
            window.location='payment.php?order_id=$order_id';
        } else {
            window.location='services.php';
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<style>
body { background-color: #f8f9fa; }

nav label.logo { color: #fff; font-weight: bold; font-size: 24px; }
nav ul { list-style: none; display: flex; gap: 15px; }
nav ul li a { color: #fff; text-decoration: none; }
.register_form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
h2, h4 { color: #343a40; }
input.form-control { background: #f1f3f5; }
.measurement-ref { background:#fafafa; border:1px solid #ececec; padding:10px; border-radius:6px; margin-bottom:8px; }
.small-note { font-size:0.9rem; color:#6c757d; }
</style>
</head>
<body>

<!-- Navbar -->
<nav>
    <label class="logo">TailorPro</label>
    <ul>
        <a href="customer.php" class="btn btn-outline-light me-2">Home</a>
        <a href="contact.php" class="btn btn-outline-light me-2">Contact</a>
        <a href="login.php" class="btn btn-danger">Logout</a>
    </ul>
</nav>


<div class="container mt-5">
    <h2 class="mb-4">Place Order for: <?php echo htmlspecialchars($garment['Name'] ?? 'Garment'); ?></h2>

    <form action="" method="POST" class="register_form">
        <h4>Choose Measurement</h4>
        <p class="small-note">Select an old measurement or choose "Use / Edit latest (or create new)". Latest is preselected.</p>

        <?php if (!empty($measurements)): ?>
            <?php foreach ($measurements as $m): ?>
                <?php
                    $mid = $m['MeasurementID'];
                    $label = date('Y-m-d H:i', strtotime($m['MeasurementDate'])) . " — H:".($m['Height'] ?? '—')." Ch:".($m['Chest'] ?? '—')." W:".($m['Waist'] ?? '—');
                    $checked = ($latest_measurement && $mid == $latest_measurement['MeasurementID']) ? 'checked' : '';
                ?>
                <div class="form-check measurement-ref">
                    <input class="form-check-input" type="radio" name="measurement_choice" id="m_<?php echo $mid; ?>" value="m_<?php echo $mid; ?>" <?php echo $checked; ?>>
                    <label class="form-check-label" for="m_<?php echo $mid; ?>">
                        <strong><?php echo htmlspecialchars($label); ?></strong>
                        <div class="small-note">
                            Height: <?php echo htmlspecialchars($m['Height'] ?? '—'); ?> cm
                            &nbsp; Chest: <?php echo htmlspecialchars($m['Chest'] ?? '—'); ?> cm
                            &nbsp; Waist: <?php echo htmlspecialchars($m['Waist'] ?? '—'); ?> cm
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No previous measurements found. Please enter your measurements below.</div>
        <?php endif; ?>

        <div class="form-check mt-3 mb-3">
            <input class="form-check-input" type="radio" name="measurement_choice" id="choice_new" value="new" <?php echo empty($measurements) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="choice_new">
                Use / Edit latest or create new measurement
            </label>
        </div>

        <h4 class="mt-4">Enter Your Measurement (leave blank to keep latest values)</h4>

        <div class="row g-3">
            <div class="col-md-4">
                <label>Height (cm)</label>
                <input type="number" step="0.01" name="Height" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Height'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label>Chest (cm)</label>
                <input type="number" step="0.01" name="Chest" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Chest'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label>Waist (cm)</label>
                <input type="number" step="0.01" name="Waist" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Waist'] ?? ''); ?>">
            </div>

            <div class="col-md-4">
                <label>Hips (cm)</label>
                <input type="number" step="0.01" name="Hips" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Hips'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label>Shoulder Width (cm)</label>
                <input type="number" step="0.01" name="ShoulderWidth" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['ShoulderWidth'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label>Sleeve Length (cm)</label>
                <input type="number" step="0.01" name="SleeveLength" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['SleeveLength'] ?? ''); ?>">
            </div>

            <div class="col-md-4">
                <label>Neck (cm)</label>
                <input type="number" step="0.01" name="Neck" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Neck'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label>Inseam (cm)</label>
                <input type="number" step="0.01" name="Inseam" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Inseam'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label>Thigh (cm)</label>
                <input type="number" step="0.01" name="Thigh" class="form-control" value="<?php echo htmlspecialchars($latest_measurement['Thigh'] ?? ''); ?>">
            </div>
        </div>
  <!-- Design Description & Image Preview (Preview Only) -->
<h4 class="mt-4">Design Reference</h4>

<div class="mb-3">
    <label class="form-label">Design Description</label>
    <textarea name="DesignDescription" class="form-control" placeholder="Describe the design the customer wants..."></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Upload Design Image</label>
    <input type="file" class="form-control" id="designImage" accept="image/*">
    <img id="designPreview" src="#" alt="Design Preview" style="display:none; max-width:200px; margin-top:10px; border:1px solid #ccc; border-radius:5px;">
</div>
<!-- JS for image preview -->
<script>
document.getElementById('designImage').addEventListener('change', function(event) {
    const [file] = this.files;
    if (file) {
        const preview = document.getElementById('designPreview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
});
</script>

        <div class="mb-3 mt-4">
            <input type="submit" name="submit" value="Place Order" class="btn btn-primary w-100">
        </div>
    </form>
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
