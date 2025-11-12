<?php
// Sample customer data (you can later replace this with database results)
$customers = [
    ["id" => "CUST001", "name" => "John Smith", "age" => 30, "gender" => "Male", "email" => "smith@gmail.com", "contact" => "01712345678", "address" => "New York", "status" => "Completed"],
    ["id" => "CUST002", "name" => "Sarah Johnson", "age" => 25, "gender" => "Female", "email" => "sarah@gmail.com", "contact" => "01898765432", "address" => "Los Angeles", "status" => "In Progress"],
    ["id" => "CUST003", "name" => "Michael Brown", "age" => 35, "gender" => "Male", "email" => "brown@gmail.com", "contact" => "01655555555", "address" => "Chicago", "status" => "Pending"],
];

// Handle new customer form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newCustomer = [
        "id" => $_POST['id'] ?? '',
        "name" => $_POST['name'] ?? '',
        "age" => $_POST['age'] ?? '',
        "gender" => $_POST['gender'] ?? '',
        "email" => $_POST['email'] ?? '',
        "contact" => $_POST['contact'] ?? '',
        "address" => $_POST['address'] ?? '',
        "status" => $_POST['status'] ?? 'Pending',
    ];

    // Add new customer to array
    $customers[] = $newCustomer;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorPro - Customer Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar bg-dark p-3">
    <button class="btn btn-outline-secondary" onclick="history.back()">← Back</button>
    <h3 class="ms-3">✂️ TailorPro - Customer Management</h3>
</header>

<main class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2>Customer Management</h2>
            <p class="text-muted">Track and manage your customers</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#newCustomer">+ New Customer</button>
    </div>

    <!-- 🧾 New Customer Form -->
    <div id="newCustomer" class="collapse mb-4">
        <div class="card card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Customer ID</label>
                        <input type="text" name="id" class="form-control" placeholder="CUST006" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Customer Name" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" class="form-control" placeholder="Age" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contact</label>
                        <input type="text" name="contact" class="form-control" placeholder="Phone number" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Address" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success" type="submit">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🧍 Customer Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
                 <?php 
                    // convert status to css class like "status-completed"
                    $statusClass = strtolower(str_replace(' ', '-', $c['status']));
                ?>
                <tr>
                    <td><?= htmlspecialchars($c['id']); ?></td>
                    <td><?= htmlspecialchars($c['name']); ?></td>
                    <td><?= htmlspecialchars($c['age']); ?></td>
                    <td><?= htmlspecialchars($c['gender']); ?></td>
                    <td><?= htmlspecialchars($c['email']); ?></td>
                    <td><?= htmlspecialchars($c['contact']); ?></td>
                    <td><?= htmlspecialchars($c['address']); ?></td>
                    <td><span class="status <?= $statusClass; ?>"><?= htmlspecialchars($c['status']); ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
