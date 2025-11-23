<?php
session_start();
include('db_con.php'); // Connect to lab_project2

// Make sure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_name = $_SESSION['username'];

// Fetch all garment types
$garments = [];
$result = mysqli_query($connection, "SELECT * FROM GarmentType");
while ($row = mysqli_fetch_assoc($result)) {
    $garments[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Services</title>
<link rel="stylesheet" href="CSS/service.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Navbar -->
<nav>
    <label class="logo">TailorPro</label>
    <ul>
        <li><a href="customer.php">Home</a></li>
    <li><a href="contact.php">Contact</a></li>
    <li><a href="logout.php" class="btn btn-success btn-sm">Logout</a></li>
    </ul>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Hello, <?php echo htmlspecialchars($customer_name); ?>! Choose a service:</h2>

    <div class="row">
        <?php foreach ($garments as $g): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($g['Name']); ?></h5>
                        <p class="card-text">Place an order for <?php echo htmlspecialchars($g['Name']); ?>.</p>
                        <a href="create_order.php?garment_id=<?php echo $g['GarmentTypeID']; ?>" class="btn btn-primary">Order Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>
