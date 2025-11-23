<?php
include('db_con.php');

/* -----------------------------------------------------------
   CREATE NEW ORDER
----------------------------------------------------------- */
if (isset($_POST['CreateOrderForExisting'])) {

    $customerID = mysqli_real_escape_string($connection, $_POST['ExistingCustomerID']);
    $status     = mysqli_real_escape_string($connection, $_POST['NewOrderStatus']);
    $amount     = mysqli_real_escape_string($connection, $_POST['NewOrderAmount']);

    // Use provided date or today's date
    $orderDate  = !empty($_POST['NewOrderDate']) ? $_POST['NewOrderDate'] : date('Y-m-d');

    $insertQuery = "
        INSERT INTO `order` (CustomerID, OrderDate, OrderStatus, TotalAmount)
        VALUES ('$customerID', '$orderDate', '$status', '$amount')
    ";

    if (mysqli_query($connection, $insertQuery)) {
        echo "<script>alert('New order added successfully!'); window.location='createorder2.php';</script>";
        exit;
    } else {
        // Debug: show error
        die("Error adding order: " . mysqli_error($connection));
    }
}


/* -----------------------------------------------------------
   UPDATE ORDER
----------------------------------------------------------- */
if (isset($_POST['updateOrder'])) {
    $OrderID = $_POST['OrderID'];
    $Status  = $_POST['Status'];
    $Amount  = $_POST['Amount'];

    $updateQuery = "
        UPDATE `order` SET
            OrderStatus = '$Status',
            TotalAmount = '$Amount'
        WHERE OrderID = '$OrderID'
    ";

    mysqli_query($connection, $updateQuery);

    echo "<script>
        alert('Order updated successfully!');
        window.location='createorder2.php';
    </script>";
    exit;
}

/* -----------------------------------------------------------
   DELETE ORDER
----------------------------------------------------------- */
/*if (isset($_GET['delete'])) {
    $delID = $_GET['delete'];
    mysqli_query($connection, "DELETE FROM `order` WHERE OrderID='$delID'");
    echo "<script>window.location='order3.php';</script>";
    exit;
} */

/* -----------------------------------------------------------
   FETCH UNIQUE CUSTOMERS
----------------------------------------------------------- */
$customerList = mysqli_query($connection, "
    SELECT DISTINCT c.CustomerID, c.Name
    FROM customer c
    JOIN `order` o ON c.CustomerID = o.CustomerID
    ORDER BY c.Name ASC
");

/* -----------------------------------------------------------
   GET FILTER VALUES
----------------------------------------------------------- */
$filterCustomer = $_GET['filterCustomer'] ?? '';
$filterOrderID  = $_GET['filterOrderID'] ?? '';
$filterStatus   = $_GET['filterStatus'] ?? '';
$search         = $_GET['search'] ?? '';

/* -----------------------------------------------------------
   BUILD QUERY WITH ALL FILTERS
----------------------------------------------------------- */
$where = " WHERE 1=1 ";

if ($filterCustomer !== "") {
    $where .= " AND o.CustomerID = '$filterCustomer' ";
}

if ($filterOrderID !== "") {
    $where .= " AND o.OrderID = '$filterOrderID' ";
}

if ($filterStatus !== "") {
    $where .= " AND o.OrderStatus = '$filterStatus' ";
}

if ($search !== "") {
    $where .= "
        AND (
            o.CustomerID LIKE '%$search%' OR
            c.Name LIKE '%$search%' OR
            o.OrderStatus LIKE '%$search%' OR
            o.OrderID LIKE '%$search%'
        )
    ";
}

/* -----------------------------------------------------------
   FETCH ORDERS BASED ON FILTERS
----------------------------------------------------------- */
$query = "
    SELECT 
        o.OrderID,
        o.CustomerID,
        o.OrderDate,
        o.OrderStatus,
        o.TotalAmount,
        c.Name AS CustomerName
    FROM `order` o
    JOIN customer c ON c.CustomerID = o.CustomerID
    $where
    ORDER BY o.OrderID DESC
";

$result = mysqli_query($connection, $query);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

/* -----------------------------------------------------------
   FETCH ORDER IDS FOR SELECTED CUSTOMER
----------------------------------------------------------- */
$orderIDList = [];
if ($filterCustomer !== "") {
    $orderIDQuery = mysqli_query($connection, "
        SELECT OrderID FROM `order`
        WHERE CustomerID = '$filterCustomer'
        ORDER BY OrderID DESC
    ");
    $orderIDList = mysqli_fetch_all($orderIDQuery, MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>TailorPro - Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="CSS/home.css">

</head>
<body class="bg-light">

<header class="navbar bg-dark p-3 mb-4 shadow-sm">
  <nav>
    <label class="logo">TailorPro</label>
    <ul>
        <li><a href="index2.php">Home</a></li>
        <li><a href="employee.php">CustomerManagement</a></li>
        <li><a href="createorder2.php">CustomerOrders</a></li>
        <li><a href="logout.php" class="btn btn-danger btn-sm">Logout</a></li>
    </ul>
</nav>
</header>

<div class="container">

<!-- ADD NEW ORDER -->
<div class="mt-4 p-4 bg-warning-subtle border rounded">
    <h3>Add New Order</h3>
    <form action="createorder2.php" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select name="ExistingCustomerID" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php
                    $allCustomers = mysqli_query($connection, "SELECT CustomerID, Name FROM customer ORDER BY Name ASC");
                    while ($row = mysqli_fetch_assoc($allCustomers)) {
                        echo "<option value='{$row['CustomerID']}'>{$row['CustomerID']} — {$row['Name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Garment Type(s)</label>
                <div class="d-flex flex-wrap gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Garments[]" value="Shirt" id="g1">
                        <label class="form-check-label" for="g1">Shirt</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Garments[]" value="Pant" id="g2">
                        <label class="form-check-label" for="g2">Pant</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="Garments[]" value="Dress" id="g3">
                        <label class="form-check-label" for="g3">Dress</label>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="NewOrderStatus" class="form-select">
                    <option>Pending</option>
                    <option>In Progress</option>
                    <option>Completed</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Amount ($)</label>
                <input type="number" name="NewOrderAmount" step="0.01" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Order Date</label>
                <input type="date" name="NewOrderDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- NEW DESIGN DESCRIPTION & IMAGE PREVIEW -->
            <div class="col-12 mt-3">
                <label class="form-label">Design Description</label>
                <textarea name="DesignDescription" class="form-control" placeholder="Describe the design the customer wants..."></textarea>
            </div>

            <div class="col-12 mt-3">
                <label class="form-label">Upload Design Image</label>
                <input type="file" class="form-control" id="designImage" accept="image/*">
                <img id="designPreview" src="#" alt="Design Preview" style="display:none; max-width:200px; margin-top:10px; border:1px solid #ccc; border-radius:5px;">
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" name="CreateOrderForExisting" class="btn btn-success">Create Order</button>
        </div>
    </form>
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



    <!-- FILTERS -->
    <form method="GET" class="row g-12 mb-3 mt-4">
        <div class="col-md-4">
            <label class="form-label">Filter by Customer</label>
            <select name="filterCustomer" class="form-select" onchange="this.form.submit()">
                <option value="">All Customers</option>
                <?php while ($row = mysqli_fetch_assoc($customerList)): ?>
                    <option value="<?= $row['CustomerID'] ?>" <?= ($row['CustomerID'] == $filterCustomer)?'selected':'' ?>>
                        <?= $row['CustomerID'] ?> — <?= $row['Name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Filter by Order ID</label>
            <select name="filterOrderID" class="form-select" onchange="this.form.submit()">
                <option value="">All Orders</option>
                <?php foreach ($orderIDList as $oid): ?>
                    <option value="<?= $oid['OrderID'] ?>" <?= ($oid['OrderID']==$filterOrderID)?'selected':'' ?>>
                        Order #<?= $oid['OrderID'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Filter by Status</label>
            <select name="filterStatus" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="Pending" <?= $filterStatus=="Pending"?"selected":"" ?>>Pending</option>
                <option value="In Progress" <?= $filterStatus=="In Progress"?"selected":"" ?>>In Progress</option>
                <option value="Completed" <?= $filterStatus=="Completed"?"selected":"" ?>>Completed</option>
            </select>
        </div>
    </form>

    <!-- ORDERS TABLE -->
    <table class="table table-bordered table-striped bg-white">
        <thead class="table-light">
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Status</th>
            <th>Amount</th>
            <th width="140">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['OrderID'] ?></td>
                <td><?= $o['CustomerID'] ?> — <?= $o['CustomerName'] ?></td>
                <td><?= $o['OrderDate'] ?></td>
                <td><?= $o['OrderStatus'] ?></td>
                <td>$<?= number_format($o['TotalAmount'],2) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning " onclick="editOrder('<?= $o['OrderID'] ?>','<?= $o['TotalAmount'] ?>','<?= $o['OrderStatus'] ?>')">Edit</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Edit Order</h5></div>
            <div class="modal-body">
                <input type="hidden" name="OrderID" id="editOrderID">
                <label class="form-label">Status</label>
                <select name="Status" id="editStatus" class="form-select mb-3">
                    <option>Pending</option>
                    <option>In Progress</option>
                    <option>Completed</option>
                </select>
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" name="Amount" id="editAmount" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" name="updateOrder">Save Changes</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editOrder(id, amount, status){
    document.getElementById('editOrderID').value = id;
    document.getElementById('editAmount').value = amount;
    document.getElementById('editStatus').value = status;
    let modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>
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