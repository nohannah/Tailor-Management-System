<?php
// Sample order data (you can later replace this with database results)
$orders = [
    ["id" => "CUST001", "customer" => "John Smith", "email" => "smith@gmail.com", "status" => "Completed", "amount" => 250.00],
    ["id" => "CUST002", "customer" => "Sarah Johnson", "email" => "john@gmail.com", "status" => "In Progress", "amount" => 180.00],
    ["id" => "CUST003", "customer" => "Michael Brown", "email" => "brown@gmail.com", "status" => "Pending", "amount" => 320.00],
    ["id" => "CUST004", "customer" => "Emily Davis", "email" => "davis@gmail.com", "status" => "In Progress", "amount" => 150.00],
    ["id" => "CUST005", "customer" => "David Wilson", "email" => "wilson@gmail.com", "status" => "Completed", "amount" => 275.00],
];

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newOrder = [
        "id" => $_POST['id'] ?? '',
        "customer" => $_POST['customer'] ?? '',
        "email" => $_POST['email'] ?? '',
        "status" => $_POST['status'] ?? '',
        "amount" => (float) ($_POST['amount'] ?? 0),
    ];

    // Add new order to array
    $orders[] = $newOrder;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorPro - Order Management</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<header class="navbar bg-dark p-3 shadow-sm mb-4">
    <button class="btn btn-outline-secondary me-3" onclick="history.back()">← Back</button>
    <h3 class="m-0">✂️ TailorPro</h3>
</header>

<main class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2>Order Management</h2>
            <p class="text-muted">Track and manage customer orders</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#newOrderForm">+ New Order</button>
    </div>

    <!-- 🧾 New Order Form -->
    <div id="newOrderForm" class="collapse mb-4">
        <div class="card card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Customer ID</label>
                        <input type="text" name="id" class="form-control" placeholder="CUST006" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="customer" class="form-control" placeholder="Customer Name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" name="amount" step="0.01" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success" type="submit">Save Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🧍 Order Table -->
    <table class="table table-bordered table-striped bg-white">
        <thead class="table-light">
            <tr>
                <th>Cust ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php 
                    // convert status to css class like "status-completed"
                    $statusClass = strtolower(str_replace(' ', '-', $order['status']));
                ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']); ?></td>
                    <td><?= htmlspecialchars($order['customer']); ?></td>
                    <td><?= htmlspecialchars($order['email']); ?></td>
                    <td><span class="status <?= $statusClass; ?>"><?= htmlspecialchars($order['status']); ?></span></td>
                    <td>$<?= number_format($order['amount'], 2); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning">Edit</button>
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
