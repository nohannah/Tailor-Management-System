<?php
// Sample order data (you can later replace this with database results)
$orders = [
    ["id" => "ORD001", "customer" => "John Smith", "date" => "2024-01-15", "status" => "Completed", "amount" => 250.00],
    ["id" => "ORD002", "customer" => "Sarah Johnson", "date" => "2024-01-16", "status" => "In Progress", "amount" => 180.00],
    ["id" => "ORD003", "customer" => "Michael Brown", "date" => "2024-01-17", "status" => "Pending", "amount" => 320.00],
    ["id" => "ORD004", "customer" => "Emily Davis", "date" => "2024-01-18", "status" => "In Progress", "amount" => 150.00],
    ["id" => "ORD005", "customer" => "David Wilson", "date" => "2024-01-19", "status" => "Completed", "amount" => 275.00],
    ["id" => "ORD005", "customer" => "David Wilson", "date" => "2024-01-19", "status" => "Completed", "amount" => 275.00],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorPro - Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <button class="back-btn" onclick="history.back()">← Back</button>
    <h3 class="logo">✂️ TailorPro</h3>
</header>

<main class="container">
    <div class="header-section">
        <div>
            <h2>Order Management</h2>
            <p>Track and manage customer orders</p>
        </div>
        <button class="new-order-btn">+ New Order</button>
    </div>

    <div class="search-bar">
        <input type="text" placeholder="Search orders by ID or customer name...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong><?= htmlspecialchars($order['id']); ?></strong></td>
                <td><?= htmlspecialchars($order['customer']); ?></td>
                <td><?= htmlspecialchars($order['date']); ?></td>
                <td>
                    <?php $statusClass = strtolower(str_replace(' ', '-', $order['status'])); ?>
                    <span class="status <?= $statusClass; ?>">
                        <?= htmlspecialchars($order['status']); ?>
                    </span>
                </td>
                <td>$<?= number_format($order['amount'], 2); ?></td>
                <td><a href="#">View Details</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
