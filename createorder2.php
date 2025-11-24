<?php
session_start();
include('db_con.php');

// BLOCK back button cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Ensure employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: index2.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// -----------------------------
// Fetch Customers and Garments
// -----------------------------
$customer_query = "SELECT CustomerID, Name FROM Customer";
$customer_result = mysqli_query($connection, $customer_query);

$garment_query = "SELECT GarmentTypeID, Name FROM GarmentType";
$garment_result = mysqli_query($connection, $garment_query);

// -----------------------------
// Handle Create Order
// -----------------------------
if(isset($_POST['CreateOrderForExisting'])){
    $customerID = $_POST['ExistingCustomerID'];
    $garmentID = $_POST['GarmentTypeID'];
    $status = $_POST['NewOrderStatus'];
    $amount = $_POST['NewOrderAmount'];
    $orderDate = $_POST['NewOrderDate'];
    $description = $_POST['DesignDescription'];

    // Insert into Order table
    mysqli_query($connection, "INSERT INTO `Order` (CustomerID, OrderDate, OrderStatus, TotalAmount)
        VALUES ('$customerID', '$orderDate', '$status', '$amount')");

    $orderID = mysqli_insert_id($connection);

    // Insert into OrderItem table (assuming quantity=1, UnitPrice=TotalAmount)
    mysqli_query($connection, "INSERT INTO OrderItem (OrderID, GarmentTypeID, Quantity, UnitPrice, ItemTotal)
        VALUES ('$orderID', '$garmentID', 1, '$amount', '$amount')");

    header("Location: createorder2.php");
    exit();
}

// -----------------------------
// Handle Update Order
// -----------------------------
if(isset($_POST['updateOrder'])){
    $orderID = $_POST['OrderID'];
    $amount = $_POST['Amount'];
    $status = $_POST['Status'];
    $garmentID = $_POST['GarmentTypeID'];

    // Update Order table
    mysqli_query($connection, "UPDATE `Order` SET TotalAmount='$amount', OrderStatus='$status' WHERE OrderID='$orderID'");

    // Update OrderItem table (assuming 1 item per order)
    mysqli_query($connection, "UPDATE OrderItem SET GarmentTypeID='$garmentID', UnitPrice='$amount', ItemTotal='$amount' WHERE OrderID='$orderID'");

    header("Location: extra2.php");
    exit();
}

// -----------------------------
// Fetch Orders
// -----------------------------
$order_query = "SELECT o.OrderID, o.CustomerID, c.Name as CustomerName, o.OrderDate, o.OrderStatus, o.TotalAmount,
                oi.GarmentTypeID, gt.Name as GarmentName
                FROM `Order` o
                LEFT JOIN Customer c ON o.CustomerID = c.CustomerID
                LEFT JOIN OrderItem oi ON o.OrderID = oi.OrderID
                LEFT JOIN GarmentType gt ON oi.GarmentTypeID = gt.GarmentTypeID
                ORDER BY o.OrderDate DESC";

$order_result = mysqli_query($connection, $order_query);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TailorPro - Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="CSS/home1.css">
</head>
<body class="bg-light">

<header class="navbar  p-3 mb-4 shadow-sm">
  <nav>
    <label class="logo">TailorPro</label>
    <ul>
        <li><a href="index2.php">Home</a></li>
        <li><a href="employee.php">CustomerManagement</a></li>
        <li><a href="logout.php" class="btn btn-danger btn-sm">Logout</a></li>
    </ul>
</nav>
</header>

<div class="container">

<!-- ADD NEW ORDER -->
<div class="mt-4 p-4 bg-warning-subtle border rounded">
    <h3>Add New Order</h3>
    <form action="createorder2.php" method="POST">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select name="ExistingCustomerID" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php while($cust = mysqli_fetch_assoc($customer_result)){ ?>
                        <option value="<?php echo $cust['CustomerID']; ?>">
                            <?php echo $cust['CustomerID'] . " — " . $cust['Name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Garment Type</label>
                <select name="GarmentTypeID" class="form-select" required>
                    <option value="">-- Select Garment --</option>
                    <?php
                    mysqli_data_seek($garment_result, 0);
                    while($garment = mysqli_fetch_assoc($garment_result)){
                        echo "<option value='".$garment['GarmentTypeID']."'>".$garment['Name']."</option>";
                    }
                    ?>
                </select>
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
                <input type="date" name="NewOrderDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="col-12 mt-3">
                <label class="form-label">Design Description</label>
                <textarea name="DesignDescription" class="form-control" placeholder="Describe the design..."></textarea>
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

<!-- ORDERS TABLE -->
<table class="table table-bordered table-striped bg-white mt-4">
    <thead class="table-light">
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Garment</th>
            <th>Date</th>
            <th>Status</th>
            <th>Amount</th>
            <th width="140">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while($order = mysqli_fetch_assoc($order_result)){ ?>
        <tr>
            <td><?php echo $order['OrderID']; ?></td>
            <td><?php echo $order['CustomerID'] . " — " . $order['CustomerName']; ?></td>
            <td><?php echo $order['GarmentName']; ?></td>
            <td><?php echo $order['OrderDate']; ?></td>
            <td><?php echo $order['OrderStatus']; ?></td>
            <td>$<?php echo number_format($order['TotalAmount'],2); ?></td>
            <td>
                <button class="btn btn-sm btn-warning"
                    onclick="editOrder('<?php echo $order['OrderID']; ?>','<?php echo $order['TotalAmount']; ?>','<?php echo $order['OrderStatus']; ?>','<?php echo $order['GarmentTypeID']; ?>')">Edit</button>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Edit Order</h5></div>
            <div class="modal-body">
                <input type="hidden" name="OrderID" id="editOrderID">
                <label class="form-label">Garment Type</label>
                <select name="GarmentTypeID" id="editGarment" class="form-select mb-3" required>
                    <option value="">-- Select Garment --</option>
                    <?php
                    mysqli_data_seek($garment_result, 0);
                    while($garment = mysqli_fetch_assoc($garment_result)){
                        echo "<option value='".$garment['GarmentTypeID']."'>".$garment['Name']."</option>";
                    }
                    ?>
                </select>

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
function editOrder(id, amount, status, garmentID){
    document.getElementById('editOrderID').value = id;
    document.getElementById('editAmount').value = amount;
    document.getElementById('editStatus').value = status;
    document.getElementById('editGarment').value = garmentID;
    let modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<footer class="footer mt-4">
    <div class="container text-center">
        <p>&copy; 2025 TailorPro Management. All rights reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Use</a></p>
    </div>
</footer>

</body>
</html>s