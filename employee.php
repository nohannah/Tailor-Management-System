<?php 
include('db_con.php'); 

// ---------- SEARCH ----------
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $query = "
        SELECT *
        FROM customer
        WHERE 
            CustomerID LIKE '%$search%' OR
            Name LIKE '%$search%' OR
            Email LIKE '%$search%' OR
            ContactNo LIKE '%$search%' OR
            Address LIKE '%$search%'
        ORDER BY CustomerID ASC
    ";
} else {
    $query = "SELECT * FROM customer ORDER BY CustomerID ASC";
}

$result = mysqli_query($connection, $query);
$customers = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ---------- EDIT MODE ----------
$editData = null;

if (isset($_GET['edit'])) {
    $editID = $_GET['edit'];

    $editQuery = mysqli_query($connection, "SELECT * FROM customer WHERE CustomerID='$editID'");
    $editData = mysqli_fetch_assoc($editQuery);
}

// ---------- UPDATE CUSTOMER ----------
if (isset($_POST['update'])) {

    $CustomerID = $_POST['CustomerID'];
    $Name = $_POST['Name'];
    $Age = $_POST['Age'];
    $Gender = $_POST['Gender'];
    $Email = $_POST['email'];
    $Contact = $_POST['Contact'];
    $Address = $_POST['Address'];

    $update = "UPDATE customer SET 
    Name='$Name',
    Age='$Age',
    Gender='$Gender',
    Email='$Email',
    ContactNo='$Contact',
    Address='$Address'
    WHERE CustomerID='$CustomerID'";

    if (mysqli_query($connection, $update)) {
        echo "<script>alert('Customer updated successfully!'); window.location='employee.php';</script>";
        exit;
    } else {
        echo "<script>alert('Update failed: " . mysqli_error($connection) . "');</script>";
    }
}

// ---------- ADD CUSTOMER ----------
if (isset($_POST['save'])) {

    // Auto-generate CustomerID
    $idQuery = "SELECT CustomerID FROM customer ORDER BY CustomerID DESC LIMIT 1";
    $idResult = mysqli_query($connection, $idQuery);

    if (mysqli_num_rows($idResult) > 0) {
        $row = mysqli_fetch_assoc($idResult);
        $lastId = intval(substr($row['CustomerID'], 4));
        $CustomerID = "CUST" . str_pad($lastId + 1, 3, "0", STR_PAD_LEFT);
    }
     else {
        $CustomerID = "CUST001";
    }

    // Get values
    $Name = $_POST['Name'];
    $Age = $_POST['Age'];
    $Gender = $_POST['Gender'];
    $Email = $_POST['email'];
    $Contact = $_POST['Contact'];
    $Address = $_POST['Address'];

    // Insert
    $insert = "
        INSERT INTO customer (CustomerID, Name, Age, Gender, Email, ContactNo, Address)
        VALUES ('$CustomerID', '$Name', '$Age', '$Gender', '$Email', '$Contact', '$Address')
    ";

    if (mysqli_query($connection, $insert)) {
        echo "<script>alert('Customer added successfully!');</script>";
    } else {
        echo "<script>alert('Insert failed: " . mysqli_error($connection) . "');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorPro - Customer Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<header class="navbar bg-dark p-3 text-white">
    <button class="btn btn-outline-light" onclick="window.location.href='customerorder.php'">← Back</button>
    <h3 class="ms-3">✂️ TailorPro - Customer Management</h3>
</header>

<main class="container my-4">

    <!-- Heading -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><?= $editData ? "Edit Customer" : "Customer Management"; ?></h2>
            <p class="text-muted">Track and manage your customers</p>
        </div>
        <?php if (!$editData): ?>
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#newCustomer">+ New Customer</button>
        <?php endif; ?>
    </div>

    <!-- ADD or EDIT FORM -->
    <div id="newCustomer" class="collapse show mb-4">
        <div class="card card-body">

            <form method="POST">

                <div class="row g-3">

                    <!-- Customer ID (Read-only in edit mode) -->
                    <div class="col-md-2">
                        <label class="form-label">Customer ID</label>
                        <input type="text" name="CustomerID" class="form-control" 
                            value="<?= $editData['CustomerID'] ?? 'Auto' ?>" 
                            <?= $editData ? "readonly" : "disabled" ?>>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="Name" class="form-control" required
                            value="<?= $editData['Name'] ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Age</label>
                        <input type="number" name="Age" class="form-control" required
                            value="<?= $editData['Age'] ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="Gender" class="form-select" required>
                            <option <?= !isset($editData) ? "selected" : "" ?>>Gender</option>
                            <option <?= ($editData['Gender'] ?? '') == "Male" ? "selected" : "" ?>>Male</option>
                            <option <?= ($editData['Gender'] ?? '') == "Female" ? "selected" : "" ?>>Female</option>
                            <option <?= ($editData['Gender'] ?? '') == "Other" ? "selected" : "" ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?= $editData['Email'] ?? '' ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Contact</label>
                        <input type="text" name="Contact" class="form-control" required
                            value="<?= $editData['ContactNo'] ?? '' ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Address</label>
                        <input type="text" name="Address" class="form-control" required
                            value="<?= $editData['Address'] ?? '' ?>">
                    </div>

                </div>

                <div class="mt-3">

                    <?php if ($editData): ?>
                        <button class="btn btn-warning" name="update">Update Customer</button>
                        <a href="employee.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button class="btn btn-success" name="save">Save Customer</button>
                    <?php endif; ?>

                </div>

            </form>
        </div>
    </div>

    <!-- SEARCH BAR -->
    <form method="GET" class="mb-3">
        <div class="input-group" style="max-width: 350px;">
            <input type="text" name="search" class="form-control" placeholder="Search customer..."
                value="<?= $search ?>">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- CUSTOMER TABLE -->
    <table class="table table-bordered table-striped bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= $c['CustomerID']; ?></td>
                    <td><?= $c['Name']; ?></td>
                    <td><?= $c['Age']; ?></td>
                    <td><?= $c['Gender']; ?></td>
                    <td><?= $c['Email']; ?></td>
                    <td><?= $c['ContactNo']; ?></td>
                    <td><?= $c['Address']; ?></td>
                    <td>
                        <a href="employee.php?edit=<?= $c['CustomerID']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
